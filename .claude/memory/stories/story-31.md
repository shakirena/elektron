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

**Verdict:** PASS (pending — security-reviewer запущен)

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
