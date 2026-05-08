# Feature #1: Bug — Массовое удаление оплат долгов клиентов

**Тип:** type:bug
**Приоритет:** priority:critical
**Статус:** kanban:ready-for-dev
**Дата анализа:** 2026-05-08

---

## User Stories

| Story | Issue | Размер | Файл | Метод |
|-------|-------|--------|------|-------|
| Защита actionCancel от null number | #2 | size:s | controllers/SellController.php | actionCancel (1018) |
| Сохранение оплаты с корректным number | #3 | size:xs | controllers/SellController.php | actionReceivedDebt (1837) |
| Null-safe удаление dclient в CostsController | #4 | size:xs | controllers/CostsController.php | actionDelete (242) |

---

## Spec

- Файл: docs/specs/spec-bug-001-payment-deletion.md
- Статус: создан 2026-05-08

## Arch

- Файл: docs/arch/arch-bug-001-payment-deletion.md
- Статус: создан 2026-05-08
- Подход: минимальные патчи, без рефакторинга, без изменений схемы DB

## Code Stubs

Не требуются (точечные правки существующих методов).

## QA Results

| Story | Coverage | Тестов | Статус | Дата |
|-------|----------|--------|--------|------|
| #2 actionCancel guard | 100% (4/4 строк) | 11 | qa:passed | 2026-05-08 |
| #3 actionReceivedDebt number | 100% (3/3 строк) | 6 | qa:passed | 2026-05-08 |
| #4 CostsController null-safe | 100% (4/4 строк) | 7 | qa:passed | 2026-05-08 |

**TC-документ:** docs/test-cases/tc-bug-001-payment-deletion.md
**TC → Unit Test mapping:** docs/test-cases/traceability-tc.md

## Gate History

- G1: PASS 2026-05-08 — business value OK, 5 AC, type:bug, priority:critical, нет дубликатов
- G2: PASS 2026-05-08 — spec+arch созданы, 3 stories SD-1 compliant, size:s/xs, INVEST OK
- G5: PASS 2026-05-08 — coverage 100%, security:passed подтверждён, все AC верифицированы, TC создан
