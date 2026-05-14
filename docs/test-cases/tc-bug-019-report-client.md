# Test Cases: Bug #19 — Исчезновение продаж в отчёте Движение клиентов

**Issue:** #19
**Тип:** type:bug
**Приоритет:** priority:high
**Затронутые файлы:**
- `views/move/report_client.php`
- `views/move/report_client2.php`
- `controllers/SellController.php` (actionCancel)
**Unit-тесты:** `tests/codeception/unit/models/ReportClientQueryTest.php`
**Дата создания:** 2026-05-12
**QA Lead:** qa-lead

---

## Описание бага

В отчёте «Движение клиентов» (move/report-client) продажи исчезали из отображения, если их поля `debt=0` и `sum=0`. Причина: условие `if ($move['debt']>0 || $move['sum']>0)` не включало такие строки. Помимо этого, отсутствовала защита от отмены продажи при наличии оплат и диагностика orphan-оплат.

---

## TC-001: Продажа с debt=0 и sum=0 отображается в отчёте

**Тип:** Happy Path + Regression
**Приоритет:** Critical
**AC:** Выявлена причина исчезновения продаж; отчёт отображает продажи с debt=0 и sum=0 корректно

**Preconditions:**
- Запись dclient с `number > 0`, `debt = 0`, `sum = 0` существует в БД

**Steps:**
1. Открыть отчёт `move/report-client` для клиента с такой продажей
2. Проверить наличие строки продажи в таблице отчёта

**Expected:**
- Строка продажи отображается в таблице (не пропускается)
- Значения колонок debt=0, sum=0 видны корректно

**Unit-тест (ReportClientQueryTest.php):**
- `testSellWithZeroDebtAndZeroSumIsDisplayed` — classifyDclientRow возвращает `sell_entry`
- `testSellWithIntZeroDebtAndSumIsDisplayed` — integer нули также обрабатываются

**Результат:** PASS (14/14 тестов прошло)

---

## TC-002: Orphan-оплата диагностируется и визуально выделяется

**Тип:** Error Case + Диагностика
**Приоритет:** High
**AC:** Оплаты без привязанной продажи диагностируются (Yii::warning + визуальное выделение)

**Preconditions:**
- Запись dclient с `number != null`, `debt < 0` (оплата) существует, но соответствующей строки в таблице `sell` нет

**Steps:**
1. Открыть отчёт `move/report-client` для клиента с orphan-оплатой
2. Проверить вывод диагностической строки в таблице
3. Проверить лог приложения

**Expected:**
- В отчёте отображается строка с классом `warning` и текстом о найденных платежах без продажи
- В логе присутствует запись `[report-client] Orphan payment: dclient.id=...` уровня WARNING
- Диагностика не нарушает отображение обычных строк

**Unit-тест (ReportClientQueryTest.php):**
- `testOrphanPaymentDetected` — оплата с number, отсутствующим в sellNumbers, определяется как orphan
- `testNonOrphanPaymentNotFlagged` — оплата с существующей продажей не флагируется

**Результат:** PASS

---

## TC-003: actionCancel блокируется при наличии оплат

**Тип:** Error Case + Guard
**Приоритет:** Critical
**AC:** Добавлена защита actionCancel от отмены при наличии оплат

**Preconditions:**
- Продажа с `number = X` существует в таблице sell
- Запись dclient с `number = X`, `debt < 0` (оплата) существует

**Steps:**
1. Вызвать `SellController::actionCancel($number = X)`
2. Проверить ответ

**Expected:**
- actionCancel возвращает JSON с ключом `error` ("Bu satış üçün ödəniş mövcuddur...")
- Продажа не удаляется из БД
- В логе присутствует запись `[actionCancel] Blocked cancel of sell number=X` уровня WARNING

**RBAC:**
- Проверка не зависит от прав доступа: защита срабатывает для любого аутентифицированного пользователя

**Unit-тест (ReportClientQueryTest.php):**
- `testCancelBlockedWhenPaymentExists` — hasPaymentsForSell возвращает true
- `testCancelBlockedWhenMultiplePayments` — несколько оплат также блокируют отмену
- `testCancelAllowedWhenNoPayments` — без оплат отмена разрешена (false)
- `testCancelAllowedWhenNoDclientRows` — пустой список dclient: отмена разрешена

**Результат:** PASS

---

## TC-004: Продажа с debt>0 отображается как обычно

**Тип:** Happy Path
**Приоритет:** High
**AC:** Отчёт отображает продажи с debt>0 корректно (регрессия не нарушена)

**Preconditions:**
- Запись dclient с `number > 0`, `debt > 0` (долг) существует

**Steps:**
1. Открыть отчёт `move/report-client`
2. Найти строку с этой продажей

**Expected:**
- Строка отображается как `sell_entry`
- Значения debt и sum показываются корректно

**Unit-тест (ReportClientQueryTest.php):**
- `testSellWithDebtIsDisplayed` — debt=150, sum=0 → sell_entry
- `testSellWithSumIsDisplayed` — debt=0, sum=300 → sell_entry
- `testReturnWithNumberAndNegativeDebt` — debt<0, number>0 → return (отдельная ветка)
- `testPaymentWithoutNumberIsPayment` — number=null, debt<0 → payment (отдельная ветка)
- `testRowWithoutNumberAndZeroValuesIsSkipped` — number=null, debt=0 → skip (нет данных)

**Результат:** PASS

---

## Матрица покрытия AC

| AC | Описание | TC | Unit-тест | Статус |
|----|----------|-----|-----------|--------|
| AC-1 | Выявлена причина исчезновения продаж | TC-001 | testSellWithZeroDebtAndZeroSumIsDisplayed | PASS |
| AC-2 | Отчёт отображает debt=0 и sum=0 корректно | TC-001 | testSellWithIntZeroDebtAndSumIsDisplayed | PASS |
| AC-3 | Защита actionCancel от отмены при наличии оплат | TC-003 | testCancelBlockedWhenPaymentExists | PASS |
| AC-4 | Orphan-оплаты диагностируются | TC-002 | testOrphanPaymentDetected | PASS |
| AC-5 | Unit-тесты для логики выборки написаны | — | ReportClientQueryTest.php (14 тестов) | PASS |
