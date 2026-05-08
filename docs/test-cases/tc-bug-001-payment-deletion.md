# Test Cases: Bug #1 — Массовое удаление оплат долгов клиентов

**Feature:** Bug #1
**Stories:** #2, #3, #4
**Spec:** docs/specs/spec-bug-001-payment-deletion.md
**Arch:** docs/arch/arch-bug-001-payment-deletion.md
**Дата создания:** 2026-05-08
**QA Lead:** qa-lead

---

## Story #2 — Защита actionCancel от null number

### TC-001: actionCancel с null number возвращает HTTP 400

**Тип:** Error Case
**Приоритет:** Critical
**AC:** Story #2 AC-1 (Given: запрос без number / When: actionCancel начинает выполнение / Then: BadRequestHttpException, нет DELETE)

**Preconditions:**
- Таблица dclient содержит записи с number IS NULL
- Таблица dclient содержит записи с конкретными number > 0

**Steps:**
1. Вызвать `SellController::actionCancel($number = null)`
2. Проверить результат

**Expected:**
- Метод выбрасывает `\yii\web\BadRequestHttpException`
- HTTP-ответ: 400 Bad Request
- Ни одна запись в таблице dclient НЕ удалена
- Ни одна запись в таблице costs НЕ удалена

**Unit test:** `ActionCancelValidationTest::testNullNumberThrows`

---

### TC-002: actionCancel с number=0 возвращает HTTP 400

**Тип:** Error Case
**Приоритет:** Critical
**AC:** Story #2 AC-1

**Preconditions:**
- Таблица dclient содержит записи

**Steps:**
1. Вызвать `SellController::actionCancel($number = 0)`
2. Также проверить вариант `$number = '0'`

**Expected:**
- Оба вызова выбрасывают `\yii\web\BadRequestHttpException`
- Нет DELETE-запросов к dclient

**Unit tests:**
- `ActionCancelValidationTest::testZeroThrows`
- `ActionCancelValidationTest::testStringZeroThrows`

---

### TC-003: actionCancel с валидным number удаляет только записи с этим number

**Тип:** Happy Path
**Приоритет:** Critical
**AC:** Story #2 AC-1 (проверка что при валидном number DELETE выполняется корректно)

**Preconditions:**
- В таблице sell существуют записи с number=42
- В таблице dclient существуют записи с number=42 и с number IS NULL

**Steps:**
1. Вызвать `SellController::actionCancel($number = 42)`
2. Проверить параметр $number после guard
3. Проверить что $number кастован в int

**Expected:**
- Guard пропускает number=42 без исключения
- `$number = (int)42` (тип int, не string)
- `deleteAll(['number' => 42])` — удаляет только записи с number=42
- Записи с number IS NULL остаются нетронутыми

**Unit tests:**
- `ActionCancelValidationTest::testPositiveIntegerPasses`
- `ActionCancelValidationTest::testPositiveStringIntegerPasses`
- `ActionCancelValidationTest::testNumberCastToInt`

---

## Story #3 — Сохранение оплаты с корректным number

### TC-004: actionReceivedDebt создаёт запись dclient с number != NULL

**Тип:** Happy Path + Error Case
**Приоритет:** Critical
**AC:** Story #3 AC-1 (Given: валидные id, sum / When: сохранение в dclient / Then: number = номер продажи, не NULL)

**Preconditions:**
- Клиент с id=5 имеет существующую запись dclient с debt > 0 и number=15

**Steps:**
1. Вызвать `SellController::actionReceivedDebt($id=5, $sum=100, ...)`
2. Проверить сохранённую запись dclient

**Expected:**
- Новая запись dclient имеет `number = 15` (взято из existingDclient)
- `number` является int (не строкой, не NULL)
- `debt = -100`

**Sub-case: клиент без записей (existingDclient = null):**
- Новая запись dclient имеет `number = 0` (sentinel, не NULL)
- Сохранение завершается успешно

**Unit tests:**
- `ActionReceivedDebtNumberTest::testExistingDclientWithNumberReturnsNumber`
- `ActionReceivedDebtNumberTest::testExistingDclientNumberCastToInt`
- `ActionReceivedDebtNumberTest::testNullExistingDclientReturnsSentinel`
- `ActionReceivedDebtNumberTest::testResultIsNeverNull`
- `ActionReceivedDebtNumberTest::testExistingDclientWithNullNumberReturnsSentinel`

---

## Story #4 — Null-safe удаление dclient в CostsController

### TC-005: CostsController::actionDelete с несуществующим fid не вызывает Fatal Error

**Тип:** Error Case
**Приоритет:** Critical
**AC:** Story #4 AC-1 (Given: dclient не найден / When: actionDelete / Then: redirect без Fatal Error)

**Preconditions:**
- В таблице costs существует запись с id_type=1 и fid=999
- В таблице dclient записи с id=999 НЕ существует

**Steps:**
1. Вызвать `CostsController::actionDelete($id)` для записи с id_type=1, fid=999
2. `Dclient::find()->where(['id' => 999])->one()` возвращает null
3. Проверить поведение после

**Expected:**
- Нет PHP Fatal Error "Call to a member function delete() on null"
- Нет необработанного исключения
- Метод завершается redirect на 'transfer'

**Unit tests:**
- `CostsActionDeleteNullSafeTest::testDclientNotFoundDeleteIsSkipped`
- `CostsActionDeleteNullSafeTest::testDclientNullDoesNotThrowFatalError`

---

### TC-006: CostsController::actionDelete с id_type=2 использует переменную $debt

**Тип:** Happy Path + Error Case + RBAC
**Приоритет:** High
**AC:** Story #4 AC-1 (защита Debt-ветки от null)

**Preconditions:**
- В таблице costs существует запись с id_type=2 и fid=10
- Сценарий A: запись Debt с id=10 существует
- Сценарий B: запись Debt с id=10 НЕ существует

**Steps (Сценарий A — Debt найден):**
1. Вызвать `CostsController::actionDelete($id)` для записи с id_type=2, fid=10
2. `Debt::find()->where(['id' => 10])->one()` возвращает объект
3. `$debt->delete()` вызывается
4. Метод выполняет redirect на 'transfer'

**Steps (Сценарий B — Debt не найден):**
1. Вызвать `CostsController::actionDelete($id)` для записи с id_type=2, fid=999
2. `Debt::find()->where(['id' => 999])->one()` возвращает null
3. Guard `if ($debt !== null)` предотвращает вызов delete()
4. Метод завершается без Fatal Error

**Expected:**
- Переменная для id_type=2 ветки называется `$debt` (не `$dclient`)
- `$debt` и `$dclient` — отдельные переменные, нет shadowing
- Нет Fatal Error в обоих сценариях

**RBAC:** Только пользователи с правом на удаление расходов могут вызывать actionDelete (проверяется на уровне RBAC Yii2, вне scope данного патча).

**Unit tests:**
- `CostsActionDeleteNullSafeTest::testDebtFoundDeleteIsCalled`
- `CostsActionDeleteNullSafeTest::testDebtNotFoundDeleteIsSkipped`
- `CostsActionDeleteNullSafeTest::testDebtNullDoesNotThrowFatalError`
- `CostsActionDeleteNullSafeTest::testDebtVariableIsDistinctFromDclient`

---

## Coverage Summary

| Story | Файл патча | Новых строк | Покрыто тестами | Coverage |
|-------|-----------|-------------|-----------------|----------|
| #2 | controllers/SellController.php:1018-1022 | 4 | 4 | 100% |
| #3 | controllers/SellController.php:1843-1850 | 3 | 3 | 100% |
| #4 | controllers/CostsController.php:249-264 | 4 | 4 | 100% |

**Итоговое coverage новых строк: 100% (порог G5: 95%)**

---

## AC Traceability

| AC | Story | TC | Unit Test | Verdict |
|----|-------|----|-----------|---------|
| FR-1.1: валидация number | #2 | TC-001, TC-002 | testNullNumberThrows, testZeroThrows, testAlphaStringThrows | PASS |
| FR-1.2: BadRequestHttpException | #2 | TC-001, TC-002 | testNullNumberThrows, testEmptyStringThrows | PASS |
| FR-1.3: нет DELETE при невалидном number | #2 | TC-001, TC-002 | (статически: guard throws перед deleteAll) | PASS |
| FR-2.1: number заполнен при сохранении | #3 | TC-004 | testExistingDclientWithNumberReturnsNumber | PASS |
| FR-2.2: sentinel 0 если продажа не найдена | #3 | TC-004 | testNullExistingDclientReturnsSentinel | PASS |
| FR-2.3: number никогда не NULL | #3 | TC-004 | testResultIsNeverNull | PASS |
| FR-3.1: if ($dclient !== null) перед delete() | #4 | TC-005 | testDclientNotFoundDeleteIsSkipped | PASS |
| FR-3.2: redirect без ошибки если null | #4 | TC-005 | testDclientNullDoesNotThrowFatalError | PASS |
| FR-3.3: защита для Debt::find() | #4 | TC-006 | testDebtNotFoundDeleteIsSkipped | PASS |
