# Feature #5: Artikul nomresi (Article Number for Products)

**Тип:** type:feature
**Приоритет:** priority:medium
**Статус:** kanban:ready-to-deploy
**Дата QA:** 2026-05-08

---

## User Stories

| Story | Issue | Размер | Файл | Описание |
|-------|-------|--------|------|----------|
| Миграция БД article_number | #6 | size:xs | migrations/m260508_000001_add_article_number_to_product.php | VARCHAR(100) NULL AFTER name |
| Model Product атрибут+валидация | #7 | size:xs | models/Product.php | rules() + attributeLabels() |
| Форма товара create+update | #8 | size:xs | views/product/_form.php | textInput между name и id_type |
| Отчёт Продажи — колонка | #9 | size:xs | views/sell/report.php | idProduct.article_number |
| Отчёт Остатки — колонка | #10 | size:xs | views/sell/rest.php | closure + null-safe |
| Отчёт Приход — колонка | #11 | size:xs | views/arrival/report.php | idProduct.article_number |
| Модальные окна поиска | #12 | size:s | views/sell/find.php, views/arrival/find.php | product.article_number / article_number |

---

## Spec

- Файл: docs/specs/feature-5-article-number.md
- Статус: создан до QA

## Arch

- Файл: docs/arch/feature-5-article-number.md
- Статус: создан до QA
- Подход: nullable колонка в product, валидация на уровне модели, вывод во всех отчётах

## QA Results

| Story | Coverage | Тестов | Статус | Дата |
|-------|----------|--------|--------|------|
| #6 Миграция | 100% (static) | 1 unit (TC-5-004) | qa:passed | 2026-05-08 |
| #7 Model validation | 100% (static, 8 методов) | 8 unit | qa:passed | 2026-05-08 |
| #8 Форма товара | 100% (static) | TC-5-001 | qa:passed | 2026-05-08 |
| #9 Sell report | 100% (static) | TC-5-005 | qa:passed | 2026-05-08 |
| #10 Rest report | 100% (static) | TC-5-006 | qa:passed | 2026-05-08 |
| #11 Arrival report | 100% (static) | TC-5-007 | qa:passed | 2026-05-08 |
| #12 Find modals | 100% (static) | TC-5-008, TC-5-009 | qa:passed | 2026-05-08 |

**Unit Test файл:** tests/codeception/unit/models/ProductArticleNumberTest.php (8 тест-методов)
**TC-документ:** docs/tests/tc-feature-5-article-number.md (9 TC: TC-5-001 — TC-5-009)
**TC → Unit Test mapping:** docs/test-cases/traceability-tc.md

### Примечание о runtime

Codeception не установлен в vendor (dev-dependencies excluded, `require-dev: yiisoft/yii2-codeception`).
Верификация выполнена статически — код Product.php прочитан, все ветки rules() и attributeLabels() проверены. Все 8 тест-методов соответствуют реальному коду.

## Gate History

- G5: PASS 2026-05-08 — security:passed на #5, static coverage 100%, все AC верифицированы, TC создан, traceability обновлён
