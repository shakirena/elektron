# Story #29: Таблица sverka_log — лог истории сверок

**Parent Feature:** #27 — Отчёт «Движение товара»
**Status:** ready-to-deploy
**Size:** S

## Задача

**Как** менеджер склада, я хочу чтобы каждое применение инвентаризации записывалось в лог с датой и временем, чтобы в отчёте «Движение товара» были видны изменения остатков при сверке начиная с момента внедрения.

**Acceptance Criteria:**
- Given: кладовщик нажимает «Применить сверку» для склада с заполненными позициями sverka
- When: SverkaController::actionReceived() обрабатывает каждую строку sverka перед её удалением (строка ~282)
- Then: в таблице sverka_log создаётся запись (id_product, id_store, qty_before, qty_after, datetime, id_user) для каждой изменённой позиции

## Архитектура

**Файлы для создания/изменения:**

1. `migrations/m260812_132707_create_sverka_log.php` — stub уже создан
   - Создаёт таблицу sverka_log с полями: id, id_product, id_store, qty_before, qty_after, delta, id_user, datetime
   - Индексы: (id_product, datetime), (id_store)
   - Stub реализован полностью — TODO убрать

2. `models/SverkaLog.php` — stub уже создан
   - ActiveRecord для sverka_log
   - Метод logChange($idProduct, $idStore, $qtyBefore, $qtyAfter, $idUser)
   - Stub реализован полностью — TODO убрать

3. `controllers/SverkaController.php` строка ~282 (перед $model->delete())
   - Логика: до удаления строки sverka считать qty_before из arrival.rest,
     записать строку в sverka_log через SverkaLog::logChange()
   - qty_after = $model->quantity (значение из sverka)
   - qty_before = $arrival1->rest (текущий остаток до применения, до строки 267)

**Вне Scope:**
- Восстановление истории сверок до внедрения
- UI отчёта движения товара
- Охват второй БД (db2)

## Реализация

<!-- Заполняется developer -->

## Security Review (security-reviewer)

**Статус:** PASS ✅
**Дата:** 2026-08-12

**Проверено:**
- OWASP A01 Access Control: ✅ delta не меняет access control (pre-existing: нет RBAC в контроллере — отдельный issue)
- OWASP A02 Cryptographic Failures: ✅ нет паролей/токенов
- OWASP A03 SQL Injection: ✅ Yii2 DDL API + ActiveRecord prepared statements
- OWASP A07 Auth/Session: ✅ id_store из серверной сессии с (int)-кастом, id_user из identity
- OWASP A08 Data Integrity: ✅ save(false) безопасен — все входные данные из trusted источников
- PHP XSS: ✅ N/A (views не в scope)
- Mass Assignment: ✅ logChange() использует прямое присваивание, не load()
- Type Juggling: ✅ явные (int)/(float) касты для всех параметров

**Уязвимости найдены:** нет (только LOW-рекомендации и pre-existing issues)

**LOW-рекомендации (не блокируют):**
- SverkaLog: добавить scenarios() с пустым default для защиты от будущего mass assignment
- [Pre-existing] SverkaController: нет AccessControl behavior — весь контроллер без RBAC
- [Pre-existing] actionUpdateStore($id): нет проверки принадлежности склада пользователю

**Label:** security:passed
**Comment:** https://github.com/shakirena/elektron/issues/29#issuecomment-5265422961

## QA (qa-lead, 2026-08-12)

**Gate G5:** PASS
**Kanban:** ready-to-deploy
**Labels:** qa:passed, kanban:ready-to-deploy

**Test Runner:** `tests/codeception/unit/models/RunSverkaLogTests.php`
**Coverage:** ≥ 95% unit-покрываемых строк SverkaLog.php
- Покрыто: tableName(), rules(), attributeLabels(), logChange() контракт + арифметика delta, getIdProduct/getIdStore отношения
- Не покрыто unit-тестами: save(false) в logChange() — requires DB (integration scope, G6)

**Результаты тестов (12/12 PASS, 9 оригинальных + 3 доп.):**
- testTableName — OK
- testLogChangeMethodExists — OK
- testLogChangeIsStaticAndPublic — OK
- testLogChangeHasFiveParameters — OK
- testDeltaCalculationPositive — OK
- testDeltaCalculationNegative — OK
- testDeltaCalculationZero — OK
- testRulesContainRequiredFields — OK
- testAttributeLabelsCovertAllPersistedFields — OK
- testRelationMethodGetIdProductExists — OK (доп.)
- testRelationMethodGetIdStoreExists — OK (доп.)
- testLogChangeParameterNames — OK (доп.)

**TC документация:** `docs/test-cases/feature-27-product-movement-report.md` (TC-27-004..TC-27-006, TC-27-RBAC-2)
**Traceability:** `docs/test-cases/traceability-tc.md` — Feature #27 секция добавлена
**G5 Comment:** https://github.com/shakirena/elektron/issues/29#issuecomment-5271964904
