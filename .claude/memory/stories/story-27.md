# Story #27: Отчёт «Движение товара» по всем операциям

**Feature:** #27 — Отчёт «Движение товара» | **Created:** 2026-08-12

---

## 📋 Задача (analyst)

**Story:** Как пользователь (менеджер/бухгалтер), я хочу видеть единый хронологический отчёт по выбранному товару со всеми операциями (приход, продажа, сверка, возврат клиенту, возврат поставщику) с датой/временем, чтобы быстро разбирать расхождения остатков и претензии, не переключаясь между 4+ отдельными отчётами.

**AC:** См. issue #27 (AC-1 … AC-5).

**Компоненты:** backend (Yii2 models/controllers) + frontend (view + GridView) + БД (миграции)
**Зависимости:** нет (все затрагиваемые таблицы уже существуют, кроме новой sverka_log)
**Размер:** l (2 миграции, новая модель, новый контроллер, новый view, правки 2 существующих контроллеров)
**Приоритет:** medium (см. issue)

**Stories в feature:** (будут декомпозированы analyst — см. arch doc раздел 8)
- Data layer: миграции + SverkaLog + правки actionReceived/actionReceivedReturn
- Backend: ProductMovementSearch + ProductMovementController
- Frontend: views/product-movement/report.php

---

## 🏗️ Архитектура (architect)

**Arch doc:** `docs/arch/feature-27-product-movement-report.md`

**Паттерн:** Search-модель (yii\base\Model) с raw SQL UNION → ArrayDataProvider → GridView.
Отдельный контроллер `ProductMovementController` (см. ADR-5).

**Новые файлы:**
- `migrations/m260812_132706_alter_returnp_data_datetime.php` — ALTER returnp.data DATE→DATETIME
- `migrations/m260812_132707_create_sverka_log.php` — CREATE TABLE sverka_log + индексы
- `models/SverkaLog.php` — ActiveRecord для sverka_log
- `models/ProductMovementSearch.php` — search-модель, UNION-запрос
- `controllers/ProductMovementController.php` — action Report
- `views/product-movement/report.php` — GridView

**Правки существующих файлов** (для developer):
- `controllers/SverkaController.php` (actionReceived, ~строка 282) — добавить `SverkaLog::logChange(...)` перед `$model->delete()`
- `controllers/SellController.php` (actionReceivedReturn, строка 687) — `date("Y-m-d")` → `date("Y-m-d H:i:s")`

**Ключевые решения (ADR):**
- **ADR-1** (db2 scope): **OUT of scope v1.** Обоснования: Yii2 не поддерживает cross-DB UNION, конфиг db2 фактически пуст (dsn=`mysql:host=;dbname=`), MVP-принцип, sell2 в основной БД уже покрывается.
- **ADR-2** (sverka log): отдельная immutable-таблица sverka_log, не мягкое удаление sverka. История видна только с момента внедрения миграции — задокументировано.
- **ADR-3** (returnp.data): ALTER DATE→DATETIME, старые записи автоматически становятся `00:00:00`.
- **ADR-4** (UNION в БД): один SQL с UNION ALL + ORDER BY + LIMIT — не PHP-merge (не ломает пагинацию, не рискует OOM).
- **ADR-5** (новый контроллер): ProductMovementController — кросс-табличный, не принадлежит ни одному домену.

**Маршрут:** `GET /product-movement/report?ProductMovementSearch[id_product]=N` (см. arch doc, раздел 5).

**DB таблицы:**
- Новая: `sverka_log(id PK, id_product, id_store, qty_before, qty_after, delta, id_user, datetime)` + индексы `(id_product, datetime)`, `(id_store)`
- Изменение: `returnp.data DATE → DATETIME`
- Существующие (без изменений): `arrival`, `sell`, `sell2`, `returnp`, `return_arrival`, `product`, `store`

**UNION-структура (см. arch doc раздел 4):**
```sql
SELECT * FROM (
    SELECT 'arrival', ... FROM arrival WHERE received=1 AND (id_contr IS NULL OR id_contr>=1)
    UNION ALL SELECT 'sell', ... FROM sell WHERE sold=1 AND returnp=0
    UNION ALL SELECT 'sell2', ... FROM sell2 WHERE sold=1 AND returnp=0
    UNION ALL SELECT 'return_client', ... FROM returnp
    UNION ALL SELECT 'return_supplier', ... FROM return_arrival WHERE received=1
    UNION ALL SELECT 'sverka', ... FROM sverka_log
) t
WHERE t.id_product = :id_product
  AND (:id_store IS NULL OR t.id_store = :id_store)
  AND (:date_from IS NULL OR t.event_datetime >= :date_from)
  AND (:date_to   IS NULL OR t.event_datetime <= :date_to)
ORDER BY t.event_datetime DESC
LIMIT :offset, :limit;
```

**Открытые вопросы для developer (см. arch doc раздел 12):**
1. Snapshot `qty_before` в actionReceived — рассчитать ДО зануления Arrival.rest (строки 259-268).
2. UI выбора товара — Select2 по каким полям искать (name / article_number / bar_code)?
3. Отображение sverka в GridView — одна колонка `delta` (со знаком) или две (`before`, `after`)?

---

## 💻 Реализация (developer)

**Branch:** feature/27-product-movement-report (TBD)
**Commit:** feat(#27): [описание]

**Реализовано:**
- [ ] Data layer: 2 миграции + SverkaLog модель
- [ ] Правка SverkaController::actionReceived — snapshot + логирование
- [ ] Правка SellController::actionReceivedReturn — datetime вместо date
- [ ] Search-модель: raw SQL UNION + ArrayDataProvider
- [ ] Controller: actionReport
- [ ] View: GridView с фильтрами
- [ ] Unit tests (search-модель, SverkaLog::logChange)

**Ключевые файлы:** см. раздел «Архитектура» выше.

**Build:** `php yii migrate --interactive=0` + `codecept run unit` — [TBD]

---

## 🔒 Security Review (security-reviewer)

**Статус:** TBD
**Дата:** TBD

**Что проверить (см. arch doc раздел 11):**
- SQL Injection в raw UNION SQL — обязательный bindValues, никакой конкатенации.
- RBAC: AccessControl `roles => ['@']`.
- Mass Assignment: ProductMovementSearch — все поля явно в rules; SverkaLog::id_user — из Yii::$app->user, не из формы.
- XSS в GridView — не использовать `format => 'raw'` для user-input колонок.
- Обязательный фильтр id_product — валидация в rules() как required (иначе тяжеловесный запрос без фильтра).

---

## ✅ QA (qa-lead)

**Coverage:** TBD
**Unit Tests:** TBD
**TC Doc:** `docs/test-cases/feature-27-product-movement-report.md` (TBD)
**Traceability:** TBD
**E2E:** TBD
