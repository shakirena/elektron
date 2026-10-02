# Feature #27: Отчёт «Движение товара» по всем операциям

## Business Value

Сейчас, чтобы понять полную историю движения конкретного товара, пользователь открывает
4+ разных отчёта вручную (приходы, продажи, возвраты, сверка). Единый отчёт с датой
и временем ускоряет разбор расхождений остатков, инвентаризацию и ответы на претензии
клиентов.

## Scope

### Входит в scope v1

- Единый хронологический отчёт по товару: приход, продажа, возврат от клиента,
  возврат поставщику, изменение при сверке
- Расширение returnp.data с DATE до DATETIME (миграция + SellController)
- Новая таблица sverka_log для хранения истории инвентаризаций
- Запись в sverka_log при каждом применении сверки (SverkaController)
- UI в стиле существующих отчётов: kartik GridView, фильтры по товару / датам / складу

### Вне Scope

- Охват второй БД (Yii::$app->db2, модели Arrival2 / ReturnArrival2) — **явное решение:
  вне scope v1**. Второй склад работает через отдельное соединение, его интеграция
  потребует отдельной миграции и тестирования в изоляции. Включить в feature v2 при
  наличии потребности.
- Восстановление истории сверок, применённых до внедрения sverka_log (данные удалены безвозвратно)
- Историческое время для возвратов, созданных до миграции returnp (останется 00:00:00)
- Экспорт в Excel / PDF
- Печать отчёта

## Functional Requirements

| # | Требование |
|---|------------|
| FR-1 | По выбранному товару отчёт показывает единый хронологический список операций всех типов |
| FR-2 | Каждая строка содержит: тип операции, дата+время, количество, контрагент/клиент/склад |
| FR-3 | Доступны фильтры: товар (обязательный), диапазон дат (опциональный), склад (опциональный) |
| FR-4 | Результат отсортирован по datetime DESC (последние — сверху) |
| FR-5 | SverkaController::actionReceived() записывает в sverka_log перед удалением строки sverka |
| FR-6 | SellController::actionReceivedReturn() записывает полный datetime в returnp.data |

## Non-Functional Requirements

| # | Требование |
|---|------------|
| NFR-1 | Performance: отчёт за 90 дней по товару отображается за < 3 сек при < 10 000 строк |
| NFR-2 | Безопасность: доступ только для аутентифицированных пользователей (стандартный RBAC проекта) |
| NFR-3 | Совместимость: миграции обратимо применяются через yii migrate без потери данных |
| NFR-4 | Тестируемость: модель ProductMovementReport покрыта unit-тестами с мок-соединением |

## Acceptance Criteria (из issue #27)

- [ ] AC-1: Отчёт показывает по выбранному товару единый хронологический список операций: приход, продажа, изменение при сверке, возврат от клиента, возврат поставщику
- [ ] AC-2: Каждая строка содержит дату и время операции, тип операции, количество и релевантные детали (контрагент/клиент/склад/документ)
- [ ] AC-3: Добавлена таблица sverka_log, и SverkaController::actionReceived() записывает изменение перед удалением строки sverka — сверки, применённые после внедрения, попадают в отчёт
- [ ] AC-4: Поле returnp.data расширено до DATETIME, SellController::actionReceivedReturn() пишет полную дату-время для новых возвратов
- [ ] AC-5: Отчёт доступен через UI в стиле существующих отчётов (фильтр по товару/датам, GridView)

## User Stories

| Story | Issue | Приоритет | Размер | Статус |
|-------|-------|-----------|--------|--------|
| Расширение returnp.data до DATETIME | #28 | medium | s | ready-for-dev |
| Таблица sverka_log — лог истории сверок | #29 | medium | s | ready-for-dev |
| Модель ProductMovementReport — UNION по всем источникам | #30 | medium | m | backlog (blocked by #28, #29) |
| UI отчёта «Движение товара» — контроллер и GridView | #31 | medium | s | backlog (blocked by #30) |

## Data Sources

| Таблица | Тип операции | Поле datetime | Примечание |
|---------|-------------|---------------|------------|
| arrival | приход | datetime | Уже DATETIME, правок не требует |
| sell | продажа | datetime | Уже DATETIME, правок не требует |
| sell2 | продажа | datetime | Уже DATETIME, правок не требует |
| returnp | возврат от клиента | data | Требует миграции DATE → DATETIME (#28) |
| return_arrival | возврат поставщику | date | Уже DATETIME, правок не требует |
| sverka_log | сверка | datetime | Новая таблица (#29) |

### Общая схема строки UNION

```sql
SELECT
    id_product,
    quantity,
    datetime,
    'приход'            AS operation_type,
    id_contractor       AS counterpart_id,
    NULL                AS client_id,
    id_store
FROM arrival
WHERE id_product = :id_product

UNION ALL

SELECT id_product, quantity, datetime, 'продажа', NULL, id_client, id_store
FROM sell  WHERE id_product = :id_product

UNION ALL
-- sell2, returnp, return_arrival, sverka_log — аналогично

ORDER BY datetime DESC
```

## UI Pattern

Существующие отчёты-образцы:

- `views/arrival/report.php` + `ArrivalController::actionReport()` — структура GridView и фильтров
- `views/sell/report.php` + `SellController::actionReport()` — паттерн фильтрации по датам
- kartik\grid\GridView с `showFooter`, `striped`, `hover`, `panel => ['type'=>'primary']`
- Фильтры: kartik\select2\Select2 (товар), kartik\dateRange\DateRangePicker (период), Select2 (склад)

## Open Questions / Decisions

| Вопрос | Решение |
|--------|---------|
| Охват второй БД (db2) в v1? | **Вне scope v1** — реализовать в отдельной feature при наличии потребности. Сложность удваивается, MVP приносит ценность без db2. |
| Восстановление истории до sverka_log? | Технически невозможно — строки sverka удаляются при применении. В отчёте видны только сверки после внедрения. |
| Производительность UNION из 6 таблиц? | Индексы по (id_product, datetime) уже должны быть на основных таблицах. При необходимости добавить через отдельную миграцию в рамках story #30. |

## Dependencies

- Блокирует разработку: story #30 ждёт #28 и #29
- Блокирует UI: story #31 ждёт #30
- Независимые первые шаги: #28 и #29 можно выполнять параллельно
