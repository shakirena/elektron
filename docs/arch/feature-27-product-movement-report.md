# Feature #27 Architecture: Отчёт «Движение товара» по всем операциям

**GitHub Issue:** #27
**Stack:** PHP 7.4+, Yii2 Basic, MySQL, kartik\grid GridView
**Arch Doc Created:** 2026-08-12
**Author:** architect

---

## 1. Overview

Единый хронологический отчёт по товару, объединяющий 5 типов операций:

1. **Приход** (`arrival` — фильтр `received=1`, `id_contr>=1`)
2. **Продажа** (`sell` и `sell2` — фильтр `sold=1`, `returnp=0`)
3. **Изменение при сверке** (новая таблица `sverka_log` — вводится в этой feature)
4. **Возврат от клиента** (`returnp`)
5. **Возврат поставщику** (`return_arrival` — фильтр `received=1`)

Данные приводятся к общей форме (`id_product`, `id_store`, `quantity`, `price`, `datetime`, `operation_type`, `counterparty`, `document_number`) и объединяются через `UNION ALL`. Результат подаётся в GridView с фильтрами по товару, диапазону дат и складу.

---

## 2. ADR (Architecture Decision Records)

### ADR-1: db2 (второй склад) — OUT of scope v1

**Дата:** 2026-08-12
**Статус:** Принято

**Контекст:**
Модели `Arrival2`, `ReturnArrival2`, `Sell2` подключаются к второму соединению `Yii::$app->db2`. Вопрос — включать ли их в единый отчёт первой версии.

**Решение:** **OUT of scope v1.** В охват v1 попадают только таблицы основной БД (`Yii::$app->db`): `arrival`, `sell`, `sell2` (в основной БД тоже есть, см. ниже), `returnp`, `return_arrival`, `sverka_log`.

**Обоснование:**

1. **Технический барьер.** Yii2 `Query::createCommand()` привязан к одному `Connection`. `UNION ALL` между `db` и `db2` невозможен на уровне SQL (два разных сервера — обычно разные хосты). Альтернатива — fetch двумя запросами в PHP и merge/sort в памяти — сильно усложняет search-модель и ломает пагинацию GridView (нельзя переложить `LIMIT/OFFSET` на СУБД).

2. **Конфиг db2 фактически пуст.** `config/db2.php` содержит `dsn: mysql:host=;dbname=` — то есть второй склад в текущей установке не сконфигурирован, приоритет включения его в отчёт минимальный.

3. **MVP-принцип.** Ценность отчёта — разбор расхождений остатков и претензий по товару. 80% случаев покрываются основным складом. Расширение на db2 разумно вынести в отдельную feature (v2) после сбора обратной связи от пользователя.

4. **Sell2 в основной БД.** Отдельно проверено: `Sell2::tableName()` возвращает `'sell2'` и модель наследуется от `ActiveRecord` без переопределения `getDb()` → работает с основным `Yii::$app->db`. То есть таблица `sell2` в **основной** БД уже входит в scope v1 (это черновики/отложенные продажи, ставшие sold=1).

**Последствия:**
- Пользователь при использовании отчёта на втором складе (если он появится) увидит частичную картину — в UI отчёта добавить disclaimer «Данные первого склада» в v1.
- В v2 (когда/если понадобится) — реализовать fetch-в-PHP-и-merge либо MySQL FEDERATED-таблицы, либо перенести все операции в одну БД.

**Альтернативы отклонены:**
- *Fetch-и-merge в PHP:* сломает пагинацию, ORDER BY будет неэффективным для больших объёмов, риск OOM.
- *Триггеры репликации в основную БД:* инфраструктурная сложность несопоставима с ценностью MVP.

---

### ADR-2: Отдельная таблица `sverka_log` вместо мягкого удаления `sverka`

**Дата:** 2026-08-12
**Статус:** Принято

**Контекст:**
`SverkaController::actionReceived()` (controllers/SverkaController.php:234-290) удаляет строки `sverka` после применения. Истории нет.

**Решение:** Создать новую таблицу `sverka_log(id, id_product, id_store, qty_before, qty_after, delta, id_user, datetime)` и писать в неё запись непосредственно перед `$model->delete()` (строка ~282).

**Обоснование:**
- **Не трогаем существующую логику применения сверки** — минимальный риск регрессии для критичной операции инвентаризации.
- **Sverka работает как «черновик»** (rebuild-able), а log — как immutable event stream. Разделение ответственностей.
- **Backup через mysqldump** (уже есть в `backupDatabase()`) не помогает для точечных запросов истории — там дамп, а не структурированные данные.

**Последствия:**
- В отчёте будут видны только сверки, применённые **после** миграции create_sverka_log. Ретроспективно данные не восстановить (уже удалены).
- `delta = qty_after - qty_before` хранится избыточно для UI-простоты (можно было бы вычислять).

---

### ADR-3: `returnp.data` расширяется до DATETIME (ALTER, а не новая колонка)

**Дата:** 2026-08-12
**Статус:** Принято

**Контекст:**
`returnp.data` имеет тип `DATE`. Для единого хронологического отчёта нужно время.

**Решение:** `ALTER TABLE returnp MODIFY COLUMN data DATETIME NULL`.

**Обоснование:**
- MySQL безопасно расширяет `DATE → DATETIME`: существующие значения `2026-01-15` станут `2026-01-15 00:00:00` без потери данных.
- Альтернатива — новая колонка `data_datetime` — усложняет код (двойная запись, backfill, старая колонка живёт вечно).
- Все существующие места чтения `->data` продолжат работать (MySQL приводит DATETIME к строке того же формата в selects).

**Последствия:**
- Старые записи возвратов будут показаны с временем `00:00:00` (документировано в issue как «out of scope»).
- В `SellController::actionReceivedReturn()` (SellController.php:687) нужно изменить `date("Y-m-d")` → `date("Y-m-d H:i:s")`.

---

### ADR-4: UNION-запрос выполняется на уровне БД, не в PHP

**Дата:** 2026-08-12
**Статус:** Принято

**Контекст:**
Данные разбросаны по 5 таблицам, нужен ORDER BY datetime DESC с пагинацией.

**Решение:** Один SQL-запрос вида `SELECT ... FROM arrival WHERE ... UNION ALL SELECT ... FROM sell WHERE ... UNION ALL ...`, обёрнутый в подзапрос для сортировки/пагинации.

**Обоснование:**
- MySQL умеет ORDER BY + LIMIT над UNION-подзапросом эффективно (если есть индексы на `id_product` и `datetime`).
- PHP-merge не масштабируется и ломает `ActiveDataProvider` pagination.
- `UNION ALL` (без DISTINCT) — быстрее, дубликатов между таблицами быть не может по определению.

**Последствия:**
- `ProductMovementSearch` возвращает `ArrayDataProvider` (не `ActiveDataProvider`), т.к. `UNION` не даёт ActiveRecord-объектов одного класса. Строки — обычные массивы.
- Нужны индексы: `arrival(id_product, datetime)`, `sell(id_product, datetime)`, `sell2(id_product, datetime)`, `returnp(id_product, data)`, `return_arrival(id_product, date)`. **Проверить и, если нет, — добавить в отдельной миграции.** В v1 полагаемся на существующие (полное сканирование при малом объёме приемлемо; для 100k+ строк — обязательно).

---

### ADR-5: Новый контроллер `ProductMovementController` (а не action в существующем)

**Дата:** 2026-08-12
**Статус:** Принято

**Решение:** Создать `controllers/ProductMovementController.php` с одним action `actionReport`.

**Обоснование:**
- Отчёт кросс-табличный (arrival + sell + returnp + return_arrival + sverka_log) — не принадлежит ни одному существующему домену.
- Соответствует существующему паттерну: `ArrivalController::actionReport`, `SellController::actionReport`, `ReturnArrivalController::actionReport`.
- Меньше диффа в критичных контроллерах (SellController — 1000+ строк).

**Последствия:**
- URL: `/product-movement/report` (Yii2 auto-routing по CamelCase → kebab-case).
- Отдельная папка view: `views/product-movement/`.

---

## 3. ERD (Entity-Relationship Diagram)

### Существующие таблицы (без изменений в схеме)

```
┌──────────────┐         ┌──────────────┐
│   product    │◄────────│   arrival    │  приход (received=1)
│  id (PK)     │         │  id_product  │
│  name        │         │  id_store    │
│  article_no  │         │  quantity    │
└──────┬───────┘         │  price       │
       │                 │  datetime    │  ← DATETIME
       │                 │  id_contr    │  (contractor)
       │                 │  number      │  (document #)
       │                 └──────────────┘
       │
       │                 ┌──────────────┐
       ├─────────────────│     sell     │  продажа (sold=1, returnp=0)
       │                 │  id_product  │
       │                 │  id_store    │
       │                 │  quantity    │
       │                 │  price       │
       │                 │  datetime    │  ← DATETIME
       │                 │  id_client   │
       │                 │  number      │
       │                 └──────────────┘
       │
       │                 ┌──────────────┐
       ├─────────────────│    sell2     │  продажа (аналог sell — другой канал)
       │                 │  ...          │  та же структура
       │                 └──────────────┘
       │
       │                 ┌──────────────┐
       ├─────────────────│   returnp    │  возврат от клиента
       │                 │  id_product  │
       │                 │  id_store    │
       │                 │  quantity    │
       │                 │  price       │
       │                 │  data        │  ← ALTER: DATE → DATETIME
       │                 │  id_client   │
       │                 │  number      │
       │                 └──────────────┘
       │
       │                 ┌──────────────┐
       └─────────────────│return_arrival│  возврат поставщику
                         │  id_product  │
                         │  id_store    │
                         │  quantity    │
                         │  price       │
                         │  date        │  ← DATETIME (уже)
                         │  id_contr    │
                         └──────────────┘
```

### Новая таблица `sverka_log`

```
┌───────────────────────┐
│      sverka_log       │
├───────────────────────┤
│ id           INT PK   │
│ id_product   INT NN   │  → product.id
│ id_store     INT NN   │  → store.id
│ qty_before   DECIMAL  │  qty до применения сверки
│ qty_after    DECIMAL  │  qty после (значение из sverka.quantity)
│ delta        DECIMAL  │  qty_after - qty_before (для UI)
│ id_user      INT NN   │  → users.id_user
│ datetime     DATETIME │  время применения
├───────────────────────┤
│ INDEX (id_product, datetime)  │  для UNION-отчёта
│ INDEX (id_store)              │
└───────────────────────┘
```

### Изменение таблицы `returnp`

```sql
ALTER TABLE `returnp` MODIFY COLUMN `data` DATETIME NULL;
```

Существующие значения `2026-01-15` автоматически становятся `2026-01-15 00:00:00`. Кода, полагающегося на строгое равенство «10-символьной строки», в проекте не обнаружено (`Returnp.php` использует `->data` как safe attribute).

---

## 4. UNION-запрос — структура

Единый SQL, генерируемый `ProductMovementSearch`:

```sql
SELECT * FROM (
    /* 1. Приход */
    SELECT
        'arrival'                           AS operation_type,
        a.id                                AS source_id,
        a.id_product                        AS id_product,
        a.id_store                          AS id_store,
        a.quantity                          AS quantity,
        a.price                             AS price,
        a.datetime                          AS event_datetime,
        a.number                            AS document_number,
        a.id_contr                          AS counterparty_id,
        NULL                                AS client_id
    FROM arrival a
    WHERE a.received = 1
      AND (a.id_contr IS NULL OR a.id_contr >= 1)

    UNION ALL

    /* 2. Продажа (sell) */
    SELECT
        'sell',
        s.id, s.id_product, s.id_store,
        s.quantity, s.price, s.datetime,
        s.number, NULL, s.id_client
    FROM sell s
    WHERE s.sold = 1 AND s.returnp = 0

    UNION ALL

    /* 3. Продажа (sell2) */
    SELECT
        'sell2',
        s2.id, s2.id_product, s2.id_store,
        s2.quantity, s2.price, s2.datetime,
        s2.number, NULL, s2.id_client
    FROM sell2 s2
    WHERE s2.sold = 1 AND s2.returnp = 0

    UNION ALL

    /* 4. Возврат от клиента */
    SELECT
        'return_client',
        r.id, r.id_product, r.id_store,
        r.quantity, r.price, r.data,
        r.number, NULL, r.id_client
    FROM returnp r

    UNION ALL

    /* 5. Возврат поставщику */
    SELECT
        'return_supplier',
        ra.id, ra.id_product, ra.id_store,
        ra.quantity, ra.price, ra.date,
        NULL, ra.id_contr, NULL
    FROM return_arrival ra
    WHERE ra.received = 1

    UNION ALL

    /* 6. Изменение при сверке */
    SELECT
        'sverka',
        sl.id, sl.id_product, sl.id_store,
        sl.delta               AS quantity,   /* знаковое: + прибавка, − уменьшение */
        NULL                   AS price,
        sl.datetime,
        NULL, NULL, NULL
    FROM sverka_log sl
) t
WHERE t.id_product = :id_product           /* обязательный фильтр */
  AND (:id_store IS NULL OR t.id_store = :id_store)
  AND (:date_from IS NULL OR t.event_datetime >= :date_from)
  AND (:date_to   IS NULL OR t.event_datetime <= :date_to)
ORDER BY t.event_datetime DESC
LIMIT :offset, :limit;
```

**Замечания по реализации:**
- Параметры биндятся через `Yii::$app->db->createCommand($sql)->bindValues([...])->queryAll()`.
- `id_product` — обязательный фильтр (иначе отчёт бессмыслен и тяжеловесен). Валидация на уровне `ProductMovementSearch::validate()`.
- Для пагинации нужны два запроса: `COUNT(*) FROM (...) t WHERE ...` и основной с LIMIT.

---

## 5. API Contracts (маршрут UI)

### GET /product-movement/report

**Auth:** authenticated user (`AccessControl` в контроллере, роль `@`).

**Query parameters:**

| Параметр | Тип | Обязательный | Описание |
|---|---|---|---|
| `ProductMovementSearch[id_product]` | int | **да** | ID товара из `product.id` |
| `ProductMovementSearch[date_from]` | string (Y-m-d) | нет | Начало диапазона |
| `ProductMovementSearch[date_to]` | string (Y-m-d) | нет | Конец диапазона |
| `ProductMovementSearch[id_store]` | int | нет | Фильтр по складу |
| `ProductMovementSearch[operation_type]` | string | нет | Фильтр по типу операции (arrival/sell/…) |

**Response 200:** HTML-страница с GridView.

**Формат строки в GridView:**

| Колонка | Источник | Формат |
|---|---|---|
| Дата/время | `event_datetime` | `Y-m-d H:i:s` |
| Тип операции | `operation_type` | Локализованный лейбл: «Приход», «Продажа», «Продажа 2», «Возврат от клиента», «Возврат поставщику», «Сверка» |
| Товар | `id_product` → `Product::name` | текст |
| Артикул | `Product::article_number` | текст |
| Кол-во | `quantity` | число (2 знака); для sverka может быть отрицательным |
| Цена | `price` | число (2 знака); NULL для sverka |
| Склад | `id_store` → `Store::name` | текст |
| Контрагент/Клиент | `counterparty_id` или `client_id` | по типу операции |
| Документ № | `document_number` | текст |

**Response 400:** Товар не выбран (в UI — форма выбора товара до GridView).

---

## 6. Компонентная диаграмма

```
┌──────────────────────────────────────────────────────────────┐
│  UI (браузер)                                                 │
│    /product-movement/report?ProductMovementSearch[id_product] │
└──────────────────────┬───────────────────────────────────────┘
                       │ HTTP GET
                       ▼
┌──────────────────────────────────────────────────────────────┐
│  ProductMovementController                                    │
│    actionReport() {                                           │
│      $search = new ProductMovementSearch();                   │
│      $dp = $search->search(Yii::$app->request->queryParams);  │
│      return $this->render('report', [...]);                   │
│    }                                                          │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────────────┐
│  ProductMovementSearch (extends yii\base\Model)               │
│    - rules(): id_product required, остальное safe             │
│    - search($params):                                         │
│         1. load + validate                                    │
│         2. build UNION SQL с параметрами                      │
│         3. Yii::$app->db->createCommand(...)->queryAll()      │
│         4. wrap в ArrayDataProvider (pagination, sort)        │
└──────────────────────┬───────────────────────────────────────┘
                       │ raw SQL (UNION ALL)
                       ▼
┌──────────────────────────────────────────────────────────────┐
│  MySQL (Yii::$app->db)                                        │
│    arrival | sell | sell2 | returnp | return_arrival |        │
│    sverka_log                                                 │
└──────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│  SverkaController::actionReceived()  (изменение!)             │
│    перед $model->delete()                                     │
│    → SverkaLog::insert([                                      │
│         id_product, id_store,                                 │
│         qty_before = arrival1->rest (до update),              │
│         qty_after  = $model->quantity,                        │
│         delta      = qty_after - qty_before,                  │
│         id_user, datetime = now()                             │
│       ])                                                      │
└──────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│  SellController::actionReceivedReturn()  (изменение!)         │
│    $returnp->data = date("Y-m-d H:i:s");                      │
│    (было: date("Y-m-d"))                                      │
└──────────────────────────────────────────────────────────────┘
```

---

## 7. Миграции

Порядок применения (`php yii migrate --interactive=0`):

| # | Файл | Что делает | Down-безопасность |
|---|---|---|---|
| 1 | `m260812_132706_alter_returnp_data_datetime.php` | `ALTER TABLE returnp MODIFY data DATETIME NULL` | `MODIFY data DATE` — обрежет время (data loss для новых записей) |
| 2 | `m260812_132707_create_sverka_log.php` | `CREATE TABLE sverka_log` + индексы | `DROP TABLE sverka_log` (безопасно) |

**Индексы sverka_log:**
- `INDEX idx_sverka_log_product_dt (id_product, datetime)` — под UNION-запрос
- `INDEX idx_sverka_log_store (id_store)` — под фильтр по складу

**Индексы существующих таблиц** — оценка после первых замеров производительности; в v1 не трогаем.

---

## 8. Code Stubs — список файлов для создания

| Файл | Тип | Story (когда декомпозируем) |
|---|---|---|
| `migrations/m260812_132706_alter_returnp_data_datetime.php` | new | Story A (data layer) |
| `migrations/m260812_132707_create_sverka_log.php` | new | Story A (data layer) |
| `models/SverkaLog.php` | new (ActiveRecord) | Story A |
| `models/ProductMovementSearch.php` | new (search-модель) | Story B (backend) |
| `controllers/ProductMovementController.php` | new | Story B |
| `views/product-movement/report.php` | new | Story C (UI) |
| `controllers/SverkaController.php` | **edit** — добавить логирование в actionReceived() | Story A |
| `controllers/SellController.php` | **edit** — actionReceivedReturn() `date("Y-m-d")` → `date("Y-m-d H:i:s")` | Story A |
| `models/Returnp.php` | (без изменений — `data` уже в safe rules) | — |

---

## 9. Затронутые файлы существующего кода

### `controllers/SverkaController.php` (строки ~252-283)

**Что добавить перед `$model->delete()` (строка 282):**

```php
// Логируем изменение в sverka_log ДО удаления sverka
$qtyBefore = 0;
if ($arrival1) {
    // если arrival существовал — берём его rest ДО присвоения нового значения
    // (важно: если !$zeroUnlisted, мы уже занулили другие строки на строках 262-265,
    //  но $arrival1->rest на этом этапе — ещё старое, до присвоения строки 267)
    $qtyBefore = (float) Arrival::find()
        ->select('SUM(rest) as s')
        ->where(['id_product' => $model->id_product, 'id_store' => $storeId])
        ->scalar();
    // (уточнить в этапе разработки: возможно нужен snapshot ДО занудения)
}
$sverkaLog = new SverkaLog();
$sverkaLog->id_product = $model->id_product;
$sverkaLog->id_store   = $storeId;
$sverkaLog->qty_before = $qtyBefore;
$sverkaLog->qty_after  = (float) $model->quantity;
$sverkaLog->delta      = $sverkaLog->qty_after - $sverkaLog->qty_before;
$sverkaLog->id_user    = Yii::$app->user->identity->id_user;
$sverkaLog->datetime   = date('Y-m-d H:i:s');
$sverkaLog->save(false);

$model->delete();
```

> **Внимание developer:** точный момент расчёта `qty_before` требует уточнения — сейчас у `sverka.actionReceived` сложная логика (`$zeroUnlisted`), и `Arrival::rest` может уже быть занулено к строке 282. **Правильно**: сделать snapshot `Arrival::find()->sum('rest')` в начале итерации `foreach` (до строк 259-268), сохранить в локальную переменную и использовать в SverkaLog. Отражено как TODO в code stub SverkaController.

### `controllers/SellController.php` (строка 687)

**Было:**
```php
$returnp->data = date("Y-m-d");
```

**Стало:**
```php
$returnp->data = date("Y-m-d H:i:s");
```

---

## 10. Порядок деплоя

1. `git pull` (миграции + новые файлы).
2. `php yii migrate --interactive=0` — применит две миграции.
3. Проверить: `SHOW CREATE TABLE returnp` → `data DATETIME`; `SHOW TABLES LIKE 'sverka_log'` → есть.
4. `php yii cache/flush-all` (на всякий случай).
5. Smoke: открыть `/product-movement/report?ProductMovementSearch[id_product]=<любой_id>` — должна отрисоваться таблица (пусть даже пустая).
6. Функциональный smoke: применить одну сверку → проверить, что появилась запись в `sverka_log` и в отчёте.

**Rollback:**
```bash
php yii migrate/down 2 --interactive=0
# Внимание: DOWN у returnp обрежет время для новых записей (не критично для отката).
```

---

## 11. Security Considerations

- [ ] **SQL Injection:** raw SQL с UNION — все значения биндятся через `bindValues([...])`. Никакой конкатенации `$this->id_product` в строку.
- [ ] **RBAC:** action защищён `AccessControl` с `roles => ['@']` (только авторизованные). В v1 доступ равен доступу к любому другому отчёту (arrival/sell).
- [ ] **Mass Assignment:** `ProductMovementSearch` — `yii\base\Model` с `rules()`, где все входные атрибуты явно перечислены как safe. `SverkaLog` — все поля `[[fields], 'safe']` или `'required'`, `id_user` — из `Yii::$app->user`, не из формы.
- [ ] **XSS в GridView:** `format => 'raw'` не используется для user-input колонок; текстовые значения по умолчанию html-encoded в kartik\grid.
- [ ] **Отсутствие фильтра id_product:** приведёт к запросу над всеми таблицами — валидируем как required в search-модели.

---

## 12. Открытые вопросы для аналитика/разработки

1. **Snapshot qty_before в actionReceived**: подтвердить, что рассчитывается ДО зануления `Arrival::rest` (см. п.9).
2. **Отображение sverka_log.quantity в GridView**: показывать `delta` (со знаком) или `qty_before → qty_after` (две колонки)?
3. **Права:** нужны ли ограничения по ролям (например, отчёт видит только менеджер)? В v1 — «как arrival/report».
4. **UI выбора товара:** Select2 по `product.name`/`article_number`/`bar_code` — какие поля искать?
5. **Индексы на существующих таблицах:** проверить существующие через `SHOW INDEX FROM arrival` — при отсутствии `(id_product, datetime)` добавить отдельной миграцией в v1.1.
