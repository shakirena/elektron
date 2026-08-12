# Handoff: Story #30 — Модель ProductMovementReport — UNION по всем источникам

**Дата:** 2026-08-13
**Branch:** feature/27-product-movement-report
**Commit:** fb60e1d0244085219f6843639314726d6661715d
**Parent Feature:** #27 — Отчёт «Движение товара»
**Status:** in-development → ready for security review / testing

---

## Изменённые файлы

| Файл | Тип | Описание |
|------|-----|----------|
| `D:\OSPanel\domains\elektron\models\ProductMovementSearch.php` | CREATE | Модель поиска: rules(), attributeLabels(), search(), buildUnionSql(), buildBindings(), operationLabels() |
| `D:\OSPanel\domains\elektron\tests\codeception\unit\models\ProductMovementSearchTest.php` | CREATE | 7 unit-тестов: guard-логика, labels, SQL-структура, bindings-маппинг |

---

## Команда build verification

```bash
# Проверка синтаксиса
php -l D:\OSPanel\domains\elektron\models\ProductMovementSearch.php
php -l D:\OSPanel\domains\elektron\tests\codeception\unit\models\ProductMovementSearchTest.php

# Autoload check
php -r "define('YII_DEBUG', true); define('YII_ENV', 'dev'); require 'D:/OSPanel/domains/elektron/vendor/autoload.php'; echo 'Autoload OK';"

# Unit-тесты (из директории feature branch worktree)
php D:\OSPanel\domains\elektron\vendor\bin\codecept run unit tests/codeception/unit/models/ProductMovementSearchTest.php --no-colors
```

**Результат syntax check:** PASS — оба файла без ошибок синтаксиса.
**Autoload:** OK.

---

## Точки для security review (delta-scope)

### 1. `models/ProductMovementSearch.php`

**SQL Injection (главный риск):**
- `buildUnionSql()` возвращает SQL-строку с 5 плейсхолдерами (`:id_product`, `:id_store`, `:date_from`, `:date_to`, `:operation_type`).
- Никакой конкатенации пользовательских данных в SQL-строку нет.
- Все значения передаются через `Yii::$app->db->createCommand($sql)->bindValues($bindings)`.
- Таблицы UNION жёстко закодированы — динамического формирования имён нет.
- **SQL Injection: невозможен.**

**Guard на id_product:**
- `rules()` объявляет `id_product` как `required`.
- `search()` вызывает `validate()` ПЕРЕД любым обращением к БД.
- Если `id_product` отсутствует → `validate()` вернёт false → пустой `ArrayDataProvider` без DB-запроса.
- **Обход guard: невозможен.**

**Type coercion:**
- `:id_product` → `(int) $this->id_product` — принудительное приведение к целому.
- `:id_store` → `$this->id_store ? (int) $this->id_store : null` — null или целое.
- Строковые фильтры (date_from, date_to, operation_type) передаются как есть — без дополнительной очистки. **Замечание для reviewer:** убедиться, что валидатор `'safe'` достаточен для дат; при необходимости добавить `'date'` validator в follow-up story.

**RBAC / Access control:**
- Модель не реализует контроль доступа — это ответственность контроллера (story #31, вне scope #30).

### 2. `tests/codeception/unit/models/ProductMovementSearchTest.php`

- Только чтение атрибутов модели и вызов методов через `ReflectionMethod`.
- Нет обращений к файловой системе, сети или БД.
- **Поверхность атаки отсутствует.**

---

## Notes для QA / тестировщика

- **7 unit-тестов** в `tests/codeception/unit/models/ProductMovementSearchTest.php`:
  1. `testEmptyDataProviderWhenIdProductMissing` — guard-логика без DB
  2. `testRulesRequireIdProduct` — validate() = false без id_product
  3. `testOperationLabelsHasSixTypes` — ровно 6 типов
  4. `testOperationLabelsRussianValues` — все русские метки корректны
  5. `testBuildUnionSqlContainsAllSources` — 6 таблиц + 5 UNION ALL + плейсхолдеры
  6. `testBuildBindingsMapping` — маппинг с реальными значениями
  7. `testBuildBindingsNullableFiltersAreNull` — необязательные фильтры = NULL

- Тесты 1-2 используют `yii\codeception\TestCase` — требуют Yii-окружения через Codeception.
- Тесты 5-7 используют `ReflectionMethod::setAccessible(true)` для доступа к `protected` методам — это корректный паттерн для unit-тестирования.

## Acceptance Criteria (self-check)

- [x] id_product — обязательный фильтр через rules()
- [x] Без id_product → пустой ArrayDataProvider, нет DB-запроса
- [x] UNION ALL из 6 источников: arrival, sell, sell2, returnp, return_arrival, sverka_log
- [x] Все параметры через bindValues() — SQL Injection невозможен
- [x] ArrayDataProvider (не ActiveDataProvider) — ADR-4
- [x] operationLabels() с 6 русскими метками
- [x] 7 unit-тестов, syntax check PASS

Готово к передаче в security review и testing.
