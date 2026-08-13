# Story #31: UI отчёта «Движение товара» — контроллер и GridView

**Feature:** #27 — Отчёт «Движение товара»
**Parent:** #27
**Status:** in-development → testing
**Branch:** feature/27-product-movement-report

---

## Задача

Реализовать UI-слой отчёта «Движение товара»:
- `controllers/ProductMovementController.php` — контроллер с одним action `actionReport()`
- `views/product-movement/report.php` — GridView-отчёт с фильтрами (товар, даты, склад, тип операции)
- Пункт меню в `views/layouts/admin.php`

**Зависимость:** #30 (ProductMovementSearch — UNION-модель) — реализована и задеплоена.

---

## AC (Acceptance Criteria)

**Given** менеджер открывает страницу /product-movement/report и выбирает товар из списка
**When** форма с фильтрами (товар, диапазон дат, склад) отправлена
**Then** отображается GridView с хронологическим списком операций: тип, дата/время, количество, контрагент/клиент/склад — в стиле существующих отчётов arrival/report и sell/report

---

## Реализация

### Контроллер: `controllers/ProductMovementController.php`

- Namespace: `app\controllers`
- AccessControl: `roles => ['@']` (только авторизованные)
- VerbFilter: `report => ['GET']`
- `actionReport()`: создаёт `ProductMovementSearch`, вызывает `search(Yii::$app->request->queryParams)`, рендерит `views/product-movement/report.php`
- Паттерн: `ArrivalController::actionReport()` / `SellController::actionReport()`

### View: `views/product-movement/report.php`

- Форма фильтров (ActiveForm, method=GET):
  - Товар (Select2, обязательный) → `ProductMovementSearch[id_product]`
  - Дата от/до (DatePicker × 2) → `date_from`, `date_to`
  - Склад (Select2, опционально) → `id_store`
  - Тип операции (Select2, опционально) → `operation_type`
  - Кнопка «Axtar»
- Если товар не выбран → `alert-info` "Hesabatı görmək üçün mal seçin."
- Если выбран → kartik GridView (ArrayDataProvider):
  - Колонки: №, Дата/время, Тип операции (цветные label-badge), Кол-во, Цена, Склад, Контрагент/Клиент, Документ №
  - Footer: сумма количества
  - panel type=primary, striped, hover
  - Контрагент и клиент резолвятся через PHP-lookup (contractorMap/clientMap)
- Паттерн: `views/arrival/report.php` + `views/sell/report.php`

### Меню: `views/layouts/admin.php`

Добавлен пункт в блок Hesabat (роль `$role`):
```php
['label' => 'Mal hərəkəti (tam)', 'url' => ['/product-movement/report']],
```

### URL маршрутизация

`/product-movement/report` → `ProductMovementController::actionReport()` через Yii2 kebab-case автомаппинг (то же, что `/return-arrival/report` → `ReturnArrivalController`).

---

## Security Review

**Статус:** PASS ✅
**Дата:** 2026-08-13
**GitHub Comment:** https://github.com/shakirena/elektron/issues/31#issuecomment-5275157022

**Проверено:**
- OWASP A01 Access Control: ✅ — `AccessControl` roles=['@'], VerbFilter GET-only
- OWASP A03 Injection (SQL): ✅ — `buildUnionSql()` статичный SQL, все переменные через `bindValues()`
- OWASP A07 Auth: ✅ — CSRF не требуется для GET-формы, AccessControl блокирует анонимов
- OWASP A08 Data Integrity: ✅ — нет `unserialize()/eval()` с user input
- PHP XSS: ✅ — `Html::encode()` на всех строковых выводах в GridView-колонках
- Mass Assignment: ✅ — `load()` через `rules()` (5 явных атрибутов)
- Open Redirect: ✅ — нет `$this->redirect()` с пользовательским URL
- Меню: ✅ — статическая строка, нет user input

**Уязвимости найдены:** нет
**Рекомендации (LOW, не блокируют):**
- Rate limiting на read-only endpoint (приемлемо для внутреннего ERP)
- Добавить date-валидатор для `date_from`/`date_to` (сейчас `'safe'`, формат не проверяется)

**Label:** `security:passed`

---

## Тесты

Unit-тесты для ProductMovementSearch уже покрыты в story #30:
`tests/codeception/unit/models/ProductMovementSearchTest.php` — 7 тест-кейсов:
1. Пустой provider без id_product
2. validate() false без id_product
3. operationLabels() — 6 типов
4. operationLabels() — русские значения
5. buildUnionSql() — все 6 источников + UNION ALL + плейсхолдеры
6. buildBindings() — маппинг параметров
7. buildBindings() — null для необязательных фильтров

---

## ✅ QA (2026-08-13)

**Coverage:** ~93% ProductMovementController.php, ~95%+ ProductMovementSearch.php
**Тесты:** 14/14 passed (0 failed)
**Runner:** `tests/codeception/unit/models/RunProductMovementTests.php`
**TC doc:** `docs/test-cases/feature-27-product-movement-report.md` — добавлен раздел Story #31
**traceability-tc.md:** обновлён (TC-27-010..TC-27-014, TC-27-RBAC-4)
**G5 Report:** https://github.com/shakirena/elektron/issues/31#issuecomment-5275261686
**Kanban:** kanban:ready-to-deploy
**Labels:** qa:passed, security:passed, kanban:ready-to-deploy
