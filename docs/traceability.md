# Traceability Matrix

*Инициализировано: 2026-05-07*
*Обновлено: 2026-05-08*

| Feature | Story | Spec | Arch | Code | Unit Tests | TC Doc | E2E |
|---------|-------|------|------|------|------------|--------|-----|
| #1 Bug: массовое удаление dclient | #2 Защита actionCancel от null number | docs/specs/spec-bug-001-payment-deletion.md | docs/arch/arch-bug-001-payment-deletion.md | controllers/SellController.php:1018-1022 (patched) | tests/codeception/unit/models/ActionCancelValidationTest.php | — | — |
| #1 Bug: массовое удаление dclient | #3 Сохранение оплаты с корректным number | docs/specs/spec-bug-001-payment-deletion.md | docs/arch/arch-bug-001-payment-deletion.md | controllers/SellController.php:1843-1850 (patched) | tests/codeception/unit/models/ActionReceivedDebtNumberTest.php | — | — |
| #1 Bug: массовое удаление dclient | #4 Null-safe удаление dclient в CostsController | docs/specs/spec-bug-001-payment-deletion.md | docs/arch/arch-bug-001-payment-deletion.md | controllers/CostsController.php:249-264 (patched) | tests/codeception/unit/models/CostsActionDeleteNullSafeTest.php | — | — |
| #5 Feature: Artikulnyj nomer | #6 Migraciya BD article_number | docs/specs/feature-5-article-number.md | docs/arch/feature-5-article-number.md | migrations/m260508_000001_add_article_number_to_product.php | tests/codeception/unit/models/ProductArticleNumberTest.php (TC-5-004) | docs/tests/tc-feature-5-article-number.md | — |
| #5 Feature: Artikulnyj nomer | #7 Model Product atribut+validaciya | docs/specs/feature-5-article-number.md | docs/arch/feature-5-article-number.md | models/Product.php (rules, attributeLabels) | tests/codeception/unit/models/ProductArticleNumberTest.php (8 tests) | docs/tests/tc-feature-5-article-number.md | — |
| #5 Feature: Artikulnyj nomer | #8 Forma tovara create+update | docs/specs/feature-5-article-number.md | docs/arch/feature-5-article-number.md | views/product/_form.php | tests/codeception/unit/models/ProductArticleNumberTest.php (TC-5-001) | docs/tests/tc-feature-5-article-number.md | — |
| #5 Feature: Artikulnyj nomer | #9 Otchet Prodazhi kolonka | docs/specs/feature-5-article-number.md | docs/arch/feature-5-article-number.md | views/sell/report.php | Static: Artikul nomresi column verified (TC-5-005) | docs/tests/tc-feature-5-article-number.md | — |
| #5 Feature: Artikulnyj nomer | #10 Otchet Ostatok kolonka | docs/specs/feature-5-article-number.md | docs/arch/feature-5-article-number.md | views/sell/rest.php | Static: closure+null-safe verified (TC-5-006) | docs/tests/tc-feature-5-article-number.md | — |
| #5 Feature: Artikulnyj nomer | #11 Otchet Prihod kolonka | docs/specs/feature-5-article-number.md | docs/arch/feature-5-article-number.md | views/arrival/report.php | Static: Artikul nomresi column verified (TC-5-007) | docs/tests/tc-feature-5-article-number.md | — |
| #5 Feature: Artikulnyj nomer | #12 Modalnye okna poiska | docs/specs/feature-5-article-number.md | docs/arch/feature-5-article-number.md | views/sell/find.php, views/arrival/find.php | Static: both find views verified (TC-5-008, TC-5-009) | docs/tests/tc-feature-5-article-number.md | — |
