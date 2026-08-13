# Test Cases: Feature #27 — Отчёт «Движение товара»

*Создано: 2026-08-12*
*Stories: #28 (Расширение returnp.data до DATETIME), #29 (Таблица sverka_log — лог истории сверок), #30 (Модель ProductMovementReport — UNION по всем источникам)*
*NFR-2: Доступ только для аутентифицированных пользователей*

---

## Story #28 — Расширение returnp.data до DATETIME

**AC:** Given оператор регистрирует возврат / When `SellController::actionReceivedReturn()` сохраняет запись / Then `returnp.data` содержит DATETIME (не только дату, но и время).

### Тест-кейсы

| TC-ID | Story | AC | Название | Тип | Шаги | Ожидаемый результат | Priority |
|-------|-------|----|----------|-----|------|---------------------|----------|
| TC-27-001 | #28 | returnp.data содержит DATETIME | Happy path — returnp.data после возврата содержит дату И время | Happy Path | 1. Применить миграцию `alter_returnp_data_datetime`. 2. Оператор регистрирует возврат от клиента через UI. 3. Выполнить `SELECT data FROM returnp ORDER BY id DESC LIMIT 1`. 4. Проверить формат полученного значения. | Значение поля `returnp.data` соответствует формату `YYYY-MM-DD HH:MM:SS` и содержит ненулевую временну́ю часть (не `00:00:00`); длина строки — 19 символов. | High |
| TC-27-002 | #28 | returnp.data до миграции | Error case — старые записи returnp (до миграции) имеют время 00:00:00 | Error/Edge Case | 1. Проверить записи `returnp`, созданные до применения миграции (значение типа DATE). 2. Выполнить `SELECT data FROM returnp WHERE id < <id_первой_записи_после_миграции>`. 3. Сравнить временну́ю часть. | Все старые записи отображают время `00:00:00` (MySQL автоматически дополнил DATE до DATETIME). Это задокументированное поведение — находится вне scope согласно ADR-3. Приложение не падает, GridView отображает `YYYY-MM-DD 00:00:00`. | Medium |
| TC-27-003 | #28 | Два возврата подряд | Edge case — два возврата подряд фиксируют разное время | Edge Case | 1. Оператор регистрирует первый возврат, фиксирует `returnp.data` (T1). 2. Немедленно регистрирует второй возврат, фиксирует `returnp.data` (T2). 3. Сравнить T1 и T2. | T1 и T2 различаются (хотя бы на 1 секунду). Оба значения содержат ненулевую временну́ю часть. Подтверждает: `date("Y-m-d H:i:s")` вызывается при каждом сохранении, а не кешируется. | Medium |
| TC-27-RBAC-1 | #28 | NFR-2 — RBAC | RBAC — неаутентифицированный пользователь не может вызвать actionReceivedReturn | RBAC | 1. Выйти из системы (или открыть браузер в инкогнито). 2. Отправить POST-запрос на эндпоинт `/sell/received-return` с валидными параметрами возврата. 3. Проверить HTTP-ответ. | HTTP 302 редирект на страницу входа (или HTTP 403). Запись в `returnp` НЕ создаётся. `SellController` защищён `AccessControl` с `roles => ['@']`. | High |

---

## Story #29 — Таблица sverka_log — лог истории сверок

**AC:** Given кладовщик применяет сверку / When `SverkaController::actionReceived()` выполняется / Then в таблице `sverka_log` создаётся запись с полями `(id_product, id_store, qty_before, qty_after, datetime, id_user)`.

### Тест-кейсы

| TC-ID | Story | AC | Название | Тип | Шаги | Ожидаемый результат | Priority |
|-------|-------|----|----------|-----|------|---------------------|----------|
| TC-27-004 | #29 | sverka_log создаётся при apply | Happy path — запись в sverka_log при применении сверки | Happy Path | 1. Применить миграцию `create_sverka_log`. 2. Зафиксировать текущее кол-во записей в `sverka_log`: `SELECT COUNT(*) FROM sverka_log`. 3. Кладовщик применяет сверку через UI (выбирает товар, вводит количество, нажимает «Применить»). 4. Выполнить `SELECT * FROM sverka_log ORDER BY id DESC LIMIT 1`. 5. Проверить все поля новой записи. | В `sverka_log` появилась ровно одна новая запись. Поля: `id_product` — совпадает с товаром в сверке; `id_store` — совпадает со складом; `qty_before` — остаток ДО применения; `qty_after` — значение из `sverka.quantity`; `delta = qty_after - qty_before`; `datetime` — текущая дата и время в формате `YYYY-MM-DD HH:MM:SS`; `id_user` — ID аутентифицированного пользователя. | High |
| TC-27-005 | #29 | qty_before == qty_after | Error case — если qty_before равен qty_after, delta = 0; запись всё равно создаётся | Error/Edge Case | 1. Установить остаток товара X на складе S в значение 10. 2. Создать сверку для товара X, склад S, quantity = 10 (без фактического изменения). 3. Применить сверку. 4. Выполнить `SELECT delta FROM sverka_log ORDER BY id DESC LIMIT 1`. | Запись в `sverka_log` создана. `delta = 0`. `qty_before = qty_after = 10`. Система НЕ пропускает запись с нулевой дельтой — логируются все применённые сверки. Строка в `sverka` удалена (обычная логика). | Medium |
| TC-27-006 | #29 | Несколько позиций в одной сверке | Edge case — несколько позиций в одной сверке создают несколько записей в sverka_log | Edge Case | 1. Создать сверку из N позиций (разные товары или одинаковый товар). 2. Зафиксировать `COUNT(*) FROM sverka_log` = C. 3. Кладовщик применяет всю сверку (все позиции). 4. Выполнить `SELECT COUNT(*) FROM sverka_log` повторно. | Новое количество записей `= C + N`. Каждая позиция сверки порождает отдельную строку в `sverka_log` с корректными `id_product`, `id_store`, `qty_before`, `qty_after`, `delta`. Все записи имеют одинаковый `id_user` и близкие значения `datetime`. | Medium |
| TC-27-RBAC-2 | #29 | NFR-2 — RBAC | RBAC — неаутентифицированный пользователь не может применить сверку | RBAC | 1. Выйти из системы (или открыть браузер в инкогнито). 2. Отправить POST-запрос на эндпоинт `/sverka/received` с валидными параметрами сверки. 3. Проверить HTTP-ответ и состояние таблиц. | HTTP 302 редирект на страницу входа (или HTTP 403). Запись в `sverka_log` НЕ создаётся. Строки `sverka` НЕ удаляются. `SverkaController` защищён `AccessControl` с `roles => ['@']`. | High |

---

## Story #30 — Модель ProductMovementReport — UNION по всем источникам

**AC:** Given в БД есть записи по товару в таблицах arrival, sell, sell2, returnp, return_arrival, sverka_log / When вызывается `ProductMovementSearch::search(['id_product' => N, 'date_from' => X, 'date_to' => Y])` / Then возвращается `ArrayDataProvider` с объединёнными строками, отсортированными по datetime DESC; каждая строка содержит: operation_type, datetime, quantity, counterpart.

### Тест-кейсы

| TC-ID | Story | AC | Название | Тип | Шаги | Ожидаемый результат | Priority |
|-------|-------|----|----------|-----|------|---------------------|----------|
| TC-27-007 | #30 | UNION из 6 источников, сортировка DESC | Happy path — search() с корректным id_product возвращает объединённые строки из всех источников, отсортированные по datetime DESC | Happy Path | 1. Убедиться, что в БД есть записи по товару id=N в таблицах arrival, sell, sell2, returnp, return_arrival, sverka_log. 2. Вызвать `ProductMovementSearch::search(['ProductMovementSearch' => ['id_product' => N]])`. 3. Получить `$dataProvider->getModels()`. 4. Проверить структуру строк и порядок сортировки. | Возвращается `ArrayDataProvider` (не `ActiveDataProvider` — ADR-4). Строки содержат поля: `operation_type`, `source_id`, `id_product`, `id_store`, `quantity`, `price`, `event_datetime`, `document_number`. Все строки имеют `id_product = N`. Строки отсортированы по `event_datetime DESC` (строка с наибольшей датой — первая). Совокупное число строк равно сумме подходящих записей из всех 6 таблиц. | High |
| TC-27-008 | #30 | guard-логика: id_product required | Error case — вызов search() без id_product возвращает пустой ArrayDataProvider без обращения к БД | Error Case | 1. Вызвать `ProductMovementSearch::search([])` без передачи id_product. 2. Получить объект dataProvider. 3. Проверить `$dataProvider->getModels()`. | Возвращается `ArrayDataProvider`. `$dataProvider->getModels()` возвращает пустой массив `[]`. Ни одного SQL-запроса к БД не выполняется — guard-логика: `search()` вызывает `validate()` перед `buildUnionSql()`, валидация падает из-за отсутствия required-поля `id_product`. Соответствует тестам `testEmptyDataProviderWhenIdProductMissing` и `testRulesRequireIdProduct`. | High |
| TC-27-009 | #30 | фильтры date_from/date_to, id_store, operation_type | Filter test — каждый необязательный фильтр корректно сужает результат через bindValues() | Functional | 1. Вызвать `search(['id_product' => N, 'date_from' => '2026-01-01', 'date_to' => '2026-03-31'])` — проверить, что все строки имеют `event_datetime` в диапазоне [2026-01-01, 2026-03-31]. 2. Вызвать `search(['id_product' => N, 'id_store' => S])` — проверить, что все строки имеют `id_store = S`. 3. Вызвать `search(['id_product' => N, 'operation_type' => 'arrival'])` — проверить, что все строки имеют `operation_type = 'arrival'`. | Шаг 1: строки вне диапазона дат отсутствуют. Шаг 2: строки с `id_store ≠ S` отсутствуют. Шаг 3: строки с `operation_type ≠ 'arrival'` отсутствуют. Фильтры транслируются в плейсхолдеры `:date_from`, `:date_to`, `:id_store`, `:operation_type` через `buildBindings()` без конкатенации в SQL-строку. Нулевые фильтры передаются как `null` — условие `(:filter IS NULL OR ...)` пропускает ограничение. Покрывается тестами `testBuildBindingsMapping`, `testBuildBindingsNullableFiltersAreNull`, `testBuildUnionSqlContainsAllSources`. | Medium |
| TC-27-RBAC-3 | #30 | NFR-2 — RBAC на уровне контроллера, не модели | RBAC — ProductMovementSearch не содержит access control логику (static analysis) | RBAC | 1. Открыть `models/ProductMovementSearch.php`. 2. Проверить отсутствие `use yii\filters\AccessControl` и `AccessRule`. 3. Проверить отсутствие метода `behaviors()` с AccessControl-конфигурацией. 4. Убедиться, что RBAC (NFR-2) обеспечивается контроллером story #31. | `ProductMovementSearch` не содержит никакой логики контроля доступа — подтверждается статическим анализом файла. Согласно ADR-5: RBAC реализуется в `ProductMovementController` (story #31) через `AccessControl` с `roles => ['@']`. Отсутствие RBAC в модели является намеренным архитектурным решением, а не дефектом. | High |

---

## Unit Test Mapping

| TC | Unit Test File | Test Method |
|----|---------------|-------------|
| TC-27-001 | ReturnpDatetimeTest.php | testSellControllerUsesDatetimeFormat, testDatetimeFormatContainsTime |
| TC-27-002 | ReturnpDatetimeTest.php | testDateOnlyFormatLacksTime |
| TC-27-003 | ReturnpDatetimeTest.php | testDatetimeStringLongerThanDateOnly, testSellControllerDoesNotUseDateOnlyForReturnp |
| TC-27-004 | SverkaLogTest.php | testTableName, testRulesContainRequiredFields, testAttributeLabelsCovertAllPersistedFields |
| TC-27-005 | SverkaLogTest.php | testDeltaCalculationZero |
| TC-27-006 | SverkaLogTest.php | testDeltaCalculationPositive, testDeltaCalculationNegative |
| TC-27-RBAC-1 | Static: AccessControl в SellController + NFR-2 | — |
| TC-27-RBAC-2 | Static: AccessControl в SverkaController + NFR-2 | — |
| TC-27-007 | ProductMovementSearchTest.php | (integration test — happy path требует реальной БД; unit-покрытие обратного случая: testEmptyDataProviderWhenIdProductMissing) |
| TC-27-008 | ProductMovementSearchTest.php | testEmptyDataProviderWhenIdProductMissing, testRulesRequireIdProduct |
| TC-27-009 | ProductMovementSearchTest.php | testBuildBindingsMapping, testBuildBindingsNullableFiltersAreNull, testBuildUnionSqlContainsAllSources |
| TC-27-RBAC-3 | Static: ProductMovementSearch не содержит AccessControl | — |

---

## Дополнительные замечания

- **TC-27-002**: Потеря времени у старых записей (`00:00:00`) является задокументированным ожидаемым поведением (ADR-3, spec feature #27 «Вне Scope»). Не является дефектом.
- **TC-27-004**: Корректность `qty_before` зависит от точного момента снятия снапшота в `actionReceived()` — до или после зануления `Arrival::rest` при `$zeroUnlisted`. Это открытый вопрос из Arch Doc (п.12 #1). Тест должен верифицировать, что `qty_before` соответствует остатку ДО обновления.
- **TC-27-RBAC-1 / TC-27-RBAC-2**: Верификация выполняется статическим анализом кода (`behaviors()` → `AccessControl` → `rules` → `roles => ['@']`). Интеграционный тест на HTTP-уровне — дополнительный уровень гарантии.

---

## Story #31 — UI отчёта: ProductMovementController + GridView

**AC:** Given менеджер открывает страницу `/product-movement/report` и выбирает товар из списка / When форма с фильтрами (товар, диапазон дат, склад) отправлена / Then отображается GridView с хронологическим списком операций: тип, дата/время, количество, контрагент/клиент/склад — в стиле существующих отчётов arrival/report и sell/report.

### Тест-кейсы

| TC-ID | Story | AC | Название | Тип | Шаги | Ожидаемый результат | Priority |
|-------|-------|----|----------|-----|------|---------------------|----------|
| TC-27-010 | #31 | GridView отображает данные из 6 источников | Happy path — открытие страницы с выбранным товаром отображает GridView со строками из всех 6 источников | Happy Path | 1. Войти в систему под аутентифицированным пользователем. 2. Убедиться, что в БД есть записи по товару id=N в таблицах arrival, sell, sell2, returnp, return_arrival, sverka_log. 3. Открыть `GET /product-movement/report?ProductMovementSearch[id_product]=N`. 4. Проверить ответ страницы: HTTP-статус, наличие GridView, число строк. 5. Проверить структуру строки: колонки тип операции, дата/время, количество, контрагент/клиент/склад. | HTTP 200. Страница содержит GridView с таблицей операций. Строки присутствуют — не менее одной из каждого из 6 источников (arrival, sell, sell2, returnp, return_arrival, sverka_log). Строки отсортированы по дате/времени в порядке убывания (новые — первые). Каждая строка содержит значения в колонках: тип, дата, количество, контрагент. | High |
| TC-27-011 | #31 | GridView не рендерится без выбранного товара | Error case — открытие страницы без выбора товара показывает предупреждение, GridView не рендерится | Error Case | 1. Войти в систему под аутентифицированным пользователем. 2. Открыть `GET /product-movement/report` без передачи `id_product`. 3. Проверить содержимое страницы. | HTTP 200. Страница содержит предупреждение «Hesabatı görmək üçün mal seçin». GridView отсутствует на странице (не рендерится). Ни одного SQL-запроса к таблицам движения не выполняется (guard-логика в `ProductMovementSearch::search()` возвращает пустой `ArrayDataProvider` без запроса к БД). | High |
| TC-27-012 | #31 | Фильтрация по диапазону дат сужает результат | Filter test — фильтрация по диапазону дат передаёт параметры date_from/date_to в URL и сужает результат | Functional | 1. Войти в систему. 2. Открыть `GET /product-movement/report?ProductMovementSearch[id_product]=N` — зафиксировать общее число строк (T). 3. Отправить форму с фильтрами: `date_from=2026-01-01`, `date_to=2026-01-31`. 4. Проверить URL — убедиться в наличии `ProductMovementSearch[date_from]` и `ProductMovementSearch[date_to]`. 5. Зафиксировать число строк после фильтрации (F). 6. Проверить даты всех отображённых строк. | URL содержит параметры `ProductMovementSearch[date_from]=2026-01-01` и `ProductMovementSearch[date_to]=2026-01-31`. Число строк F ≤ T. Все строки GridView имеют `event_datetime` в диапазоне [2026-01-01 00:00:00, 2026-01-31 23:59:59]. Строки с датами вне диапазона отсутствуют. | Medium |
| TC-27-013 | #31 | Типы операций отображаются цветными badge | Badge test — каждый тип операции отображается badge с соответствующим цветом Bootstrap | Functional | 1. Войти в систему. 2. Открыть страницу отчёта с товаром, имеющим записи всех 6 типов операций. 3. Визуально (или DOM-инспекцией) проверить CSS-классы badge для каждого типа. | Badge для типов содержат CSS-классы Bootstrap: `arrival` → `badge-success` (зелёный); `sell` → `badge-danger` (красный); `return` → `badge-warning` (жёлтый); `sverka` → `badge-info` (голубой). Текст badge соответствует типу операции. Все badge видимы и стилизованы (не отображаются как простой текст без фона). | Medium |
| TC-27-014 | #31 | Footer GridView отображает сумму quantity | Footer test — в нижней строке GridView отображается итоговая сумма колонки quantity | Functional | 1. Войти в систему. 2. Открыть страницу отчёта с товаром N, имеющим несколько записей. 3. Вручную сложить значения quantity всех строк GridView → ожидаемая сумма S. 4. Найти footer-строку GridView. 5. Сравнить значение в footer с S. | GridView содержит footer-строку. Значение в ячейке footer колонки quantity равно сумме всех значений quantity отображённых строк (S). Footer не пустой, не равен нулю при наличии записей. | Low |
| TC-27-RBAC-4 | #31 | NFR-2 — RBAC на уровне контроллера | RBAC — неаутентифицированный пользователь перенаправляется на login при GET /product-movement/report | RBAC | 1. Выйти из системы (или открыть браузер в инкогнито). 2. Отправить `GET /product-movement/report`. 3. Проверить HTTP-ответ. | HTTP 302 редирект на страницу входа (`/site/login` или настроенный loginUrl). Страница отчёта не отображается. `ProductMovementController` содержит `behaviors()` с `AccessControl` (`roles => ['@']`) и `VerbFilter` (`GET`). | Critical |

---

## Unit Test Mapping (Story #31)

| TC | Unit Test File | Test Method |
|----|---------------|-------------|
| TC-27-010 | ProductMovementControllerTest.php | testActionReportDelegatesSearchToModel |
| TC-27-011 | ProductMovementControllerTest.php | testActionReportDelegatesSearchToModel (guard-case) |
| TC-27-012 | ProductMovementControllerTest.php | (integration test — требует реальной БД; unit: testBuildBindingsMapping в ProductMovementSearchTest) |
| TC-27-013 | ProductMovementControllerTest.php | (static: view closures) |
| TC-27-014 | ProductMovementControllerTest.php | (static: view footer) |
| TC-27-RBAC-4 | ProductMovementControllerTest.php | testAccessControlAllowsOnlyAuthenticatedRole, testBehaviorsContainsAccessControl |
