# Traceability Matrix

*Инициализировано: 2026-05-07*
*Обновлено: 2026-05-08*

| Feature | Story | Spec | Arch | Code | Unit Tests | TC Doc | E2E |
|---------|-------|------|------|------|------------|--------|-----|
| #1 Bug: массовое удаление dclient | #2 Защита actionCancel от null number | docs/specs/spec-bug-001-payment-deletion.md | docs/arch/arch-bug-001-payment-deletion.md | controllers/SellController.php:1018-1022 (patched) | tests/codeception/unit/models/ActionCancelValidationTest.php | — | — |
| #1 Bug: массовое удаление dclient | #3 Сохранение оплаты с корректным number | docs/specs/spec-bug-001-payment-deletion.md | docs/arch/arch-bug-001-payment-deletion.md | controllers/SellController.php:1843-1850 (patched) | tests/codeception/unit/models/ActionReceivedDebtNumberTest.php | — | — |
| #1 Bug: массовое удаление dclient | #4 Null-safe удаление dclient в CostsController | docs/specs/spec-bug-001-payment-deletion.md | docs/arch/arch-bug-001-payment-deletion.md | controllers/CostsController.php:249-264 (patched) | tests/codeception/unit/models/CostsActionDeleteNullSafeTest.php | — | — |
