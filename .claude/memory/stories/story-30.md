# Story #30: Модель ProductMovementReport — UNION по всем источникам

**Parent Feature:** #27 — Отчёт «Движение товара»
**Status:** in-development
**Size:** M

## Задача

**Как** менеджер склада, я хочу получать единый хронологический список всех операций по конкретному товару, чтобы не открывать вручную 4 разных отчёта для анализа движения позиции.

**Acceptance Criteria:**
- Given: в БД есть записи по товару в таблицах arrival, sell, sell2, returnp, return_arrival, sverka_log
- When: вызывается ProductMovementReport::search(['id_product' => N, 'date_start' => X, 'date_end' => Y])
- Then: возвращается ActiveDataProvider с объединёнными строками, отсортированными по datetime DESC, каждая строка содержит: operation_type, datetime, quantity, counterpart

**Вне Scope:**
- UI контроллер и view (story #31)
- Охват второй БД (db2) — ADR-1
- Изменение существующих таблиц (выполнено в #28, #29)

## Архитектура

**Файлы для создания/изменения:**

1. `models/ProductMovementSearch.php` — stub уже создан архитектором
   - Расширить Model с attributes: id_product (required), date_from, date_to, id_store, operation_type
   - Метод search($params): load + validate → buildUnionSql() + buildBindings() → queryAll() → ArrayDataProvider
   - Метод buildUnionSql(): UNION ALL из 6 источников (arrival, sell, sell2, returnp, return_arrival, sverka_log)
   - Метод buildBindings(): массив параметров для bindValues()
   - Метод operationLabels(): статические лейблы для GridView
   - ADR-4: результат — ArrayDataProvider (не ActiveDataProvider), т.к. UNION не даёт AR-объектов одного класса

**Unit-тесты:**
- `tests/unit/models/ProductMovementSearchTest.php`
   - testEmptyDataProvider (пустой id_product → пустой ArrayDataProvider)
   - testRulesIdProductRequired
   - testOperationLabels (6 типов + правильные лейблы)
   - testBuildBindings (все параметры корректно маппируются)
   - testBuildUnionSqlStructure (SQL содержит все 6 UNION-блоков)
   - testSearchWithMockedDb (мок Yii::$app->db, queryAll возвращает rows)

**UNION SQL (из arch doc раздел 4):**
```sql
SELECT * FROM (
    SELECT 'arrival' AS operation_type,
        a.id AS source_id, a.id_product, a.id_store,
        a.quantity, a.price, a.datetime AS event_datetime,
        a.number AS document_number, a.id_contr AS counterparty_id, NULL AS client_id
    FROM arrival a WHERE a.received = 1 AND (a.id_contr IS NULL OR a.id_contr >= 1)

    UNION ALL

    SELECT 'sell', s.id, s.id_product, s.id_store,
        s.quantity, s.price, s.datetime,
        s.number, NULL, s.id_client
    FROM sell s WHERE s.sold = 1 AND s.returnp = 0

    UNION ALL

    SELECT 'sell2', s2.id, s2.id_product, s2.id_store,
        s2.quantity, s2.price, s2.datetime,
        s2.number, NULL, s2.id_client
    FROM sell2 s2 WHERE s2.sold = 1 AND s2.returnp = 0

    UNION ALL

    SELECT 'return_client', r.id, r.id_product, r.id_store,
        r.quantity, r.price, r.data,
        r.number, NULL, r.id_client
    FROM returnp r

    UNION ALL

    SELECT 'return_supplier', ra.id, ra.id_product, ra.id_store,
        ra.quantity, ra.price, ra.date,
        NULL, ra.id_contr, NULL
    FROM return_arrival ra WHERE ra.received = 1

    UNION ALL

    SELECT 'sverka', sl.id, sl.id_product, sl.id_store,
        sl.delta AS quantity, NULL AS price, sl.datetime,
        NULL, NULL, NULL
    FROM sverka_log sl
) t
WHERE t.id_product = :id_product
  AND (:id_store IS NULL OR t.id_store = :id_store)
  AND (:date_from IS NULL OR t.event_datetime >= :date_from)
  AND (:date_to IS NULL OR t.event_datetime <= :date_to)
  AND (:operation_type IS NULL OR t.operation_type = :operation_type)
ORDER BY t.event_datetime DESC
```

**Ветка:** feature/27-product-movement-report (существует, worktree: agent-a50d852737dea42f5 @ ecc374f)

**Security points:**
- SQL Injection: все параметры через bindValues(), нет конкатенации
- id_product обязательный — валидируется rules() перед исполнением запроса
- RBAC: access control — на уровне контроллера (story #31), модель — без HTTP

## Реализация

**Дата:** 2026-08-13
**Commit:** fb60e1d0244085219f6843639314726d6661715d
**Branch:** feature/27-product-movement-report

### Изменённые файлы

| Файл | Тип |
|------|-----|
| `models/ProductMovementSearch.php` | CREATE |
| `tests/codeception/unit/models/ProductMovementSearchTest.php` | CREATE |

### Build verification

```
php -l models/ProductMovementSearch.php
-> No syntax errors detected

php -l tests/codeception/unit/models/ProductMovementSearchTest.php
-> No syntax errors detected

php -r "require 'D:/OSPanel/domains/elektron/vendor/autoload.php'; echo 'Autoload OK';"
-> Autoload OK
```

### Покрытие

7 unit-тестов в `tests/codeception/unit/models/ProductMovementSearchTest.php`:
- testEmptyDataProviderWhenIdProductMissing
- testRulesRequireIdProduct
- testOperationLabelsHasSixTypes
- testOperationLabelsRussianValues
- testBuildUnionSqlContainsAllSources
- testBuildBindingsMapping
- testBuildBindingsNullableFiltersAreNull

## Security Review

<!-- Заполняется security-reviewer -->
