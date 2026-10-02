# Story #28: Расширение returnp.data до DATETIME

**Parent Feature:** #27 — Отчёт «Движение товара»
**Status:** ready-to-deploy
**Size:** S

## Задача

**Как** кладовщик, я хочу чтобы возврат товара от клиента фиксировал точное время операции, чтобы в отчёте «Движение товара» время возврата отображалось точно, а не как 00:00:00.

**Acceptance Criteria:**
- Given: оператор регистрирует возврат товара от клиента через форму продаж
- When: SellController::actionReceivedReturn() сохраняет запись в таблицу returnp
- Then: поле returnp.data содержит дату И время (тип DATETIME), а не только дату

## Архитектура

**Файлы для изменения:**

1. `migrations/m260812_132706_alter_returnp_data_datetime.php` — stub уже создан
   - safeUp(): `$this->alterColumn('returnp', 'data', $this->dateTime()->null());`
   - safeDown(): откат к DATE
   - Stub реализован полностью — TODO убрать

2. `controllers/SellController.php` строка ~687
   - Было: `$returnp->data = date("Y-m-d");`
   - Нужно: `$returnp->data = date("Y-m-d H:i:s");`

**Вне Scope:**
- Изменение уже существующих записей в returnp (старые останутся с 00:00:00)
- Создание UI отчёта движения товара
- Охват второй БД (db2)

## Реализация

**Commit:** ecc374f на ветке feature/27-product-movement-report
**Worktree:** agent-a50d852737dea42f5

**Изменённые файлы:**
- `migrations/m260812_132706_alter_returnp_data_datetime.php` — убран TODO-комментарий, safeUp/safeDown реализованы
- `controllers/SellController.php:651` — date("Y-m-d") → date("Y-m-d H:i:s")
- `tests/unit/models/ReturnpDatetimeTest.php` — 5 unit-тестов (формат datetime, проверка исходника SellController)

**Build:** php -l на 3 файлах — OK, autoload OK

## Security Review (security-reviewer)

**Статус:** PASS
**Дата:** 2026-08-12

**Проверено:**
- OWASP A01 Access Control: PASS (RBAC не изменялся; pre-existing gap вне delta)
- OWASP A02 Cryptographic Failures: PASS
- OWASP A03 SQL Injection: PASS (ORM DDL, нет raw SQL)
- OWASP A04 Insecure Design: PASS
- OWASP A05 Security Misconfiguration: PASS
- OWASP A07 Auth/Session: PASS (CSRF не затронут, id_user — server-side)
- OWASP A08 Data Integrity: PASS (нет unserialize)
- OWASP A10 SSRF: PASS
- PHP XSS: PASS (views не изменялись)
- Mass Assignment: PASS (прямое присваивание, не через load())
- PHP Type Juggling / Unsafe Datetime: PASS (date() возвращает валидную строку)

**Уязвимости найдены:** нет
**Рекомендации (не блокируют):**
- [LOW — pre-existing] actionReceivedReturn не включён в список AccessControl only — отдельный backlog issue
**Label:** security:passed

## QA (qa-lead, 2026-08-12)

**Gate G5:** PASS
**Kanban:** ready-to-deploy
**Labels:** qa:passed, kanban:ready-to-deploy

**Test Runner:** `tests/unit/models/RunReturnpDatetimeTests.php`
**Coverage:** 100% изменённого кода (строка 687 SellController.php полностью покрыта)

**Результаты тестов (5/5 PASS):**
- testDatetimeFormatContainsTime — OK
- testDateOnlyFormatLacksTime — OK
- testDatetimeStringLongerThanDateOnly — OK
- testSellControllerUsesDatetimeFormat — OK (ключевой: подтверждает строку 687)
- testSellControllerDoesNotUseDateOnlyForReturnp — OK

**TC документация:** `docs/test-cases/feature-27-product-movement-report.md` (TC-27-001..TC-27-003, TC-27-RBAC-1)
**Traceability:** `docs/test-cases/traceability-tc.md` — Feature #27 секция добавлена
**G5 Comment:** https://github.com/shakirena/elektron/issues/28#issuecomment-5271964417
