# Test Case Traceability Matrix

*Инициализировано: 2026-05-07*
*Обновлено: 2026-05-08 (Bug #1 — stories #2, #3, #4)*

| Feature | Story | AC | TC | Priority | Type | E2E Automated |
|---------|-------|----|----|----------|------|---------------|
| Bug #1 | #2 | FR-1.1 валидация number | TC-001 | Critical | Error Case | No |
| Bug #1 | #2 | FR-1.2 BadRequestHttpException | TC-001 | Critical | Error Case | No |
| Bug #1 | #2 | FR-1.1 number=0 | TC-002 | Critical | Error Case | No |
| Bug #1 | #2 | FR-1.3 нет DELETE при невалидном number | TC-001, TC-002 | Critical | Error Case | No |
| Bug #1 | #2 | FR-1.1 валидный number проходит | TC-003 | Critical | Happy Path | No |
| Bug #1 | #3 | FR-2.1 number заполнен при сохранении | TC-004 | Critical | Happy Path | No |
| Bug #1 | #3 | FR-2.2 sentinel 0 если продажа не найдена | TC-004 | Critical | Error Case | No |
| Bug #1 | #3 | FR-2.3 number никогда не NULL | TC-004 | Critical | Error Case | No |
| Bug #1 | #4 | FR-3.1 if ($dclient !== null) перед delete() | TC-005 | Critical | Error Case | No |
| Bug #1 | #4 | FR-3.2 redirect без Fatal Error если null | TC-005 | Critical | Error Case | No |
| Bug #1 | #4 | FR-3.3 защита для Debt::find() id_type=2 | TC-006 | High | Happy Path | No |
| Bug #1 | #4 | FR-3.3 $debt != $dclient variable naming | TC-006 | High | RBAC/Struct | No |

## Unit Test Mapping

| TC | Unit Test File | Test Method |
|----|---------------|-------------|
| TC-001 | ActionCancelValidationTest.php | testNullNumberThrows, testEmptyStringThrows |
| TC-002 | ActionCancelValidationTest.php | testZeroThrows, testStringZeroThrows, testNegativeNumberThrows, testAlphaStringThrows, testFloatStringThrows |
| TC-003 | ActionCancelValidationTest.php | testPositiveIntegerPasses, testPositiveStringIntegerPasses, testOneIsValid, testNumberCastToInt |
| TC-004 | ActionReceivedDebtNumberTest.php | testExistingDclientWithNumberReturnsNumber, testExistingDclientNumberCastToInt, testExistingDclientWithNullNumberReturnsSentinel, testNullExistingDclientReturnsSentinel, testResultIsNeverNull, testSentinelIsZeroNotNegative |
| TC-005 | CostsActionDeleteNullSafeTest.php | testDclientFoundDeleteIsCalled, testDclientNotFoundDeleteIsSkipped, testDclientNullDoesNotThrowFatalError |
| TC-006 | CostsActionDeleteNullSafeTest.php | testDebtFoundDeleteIsCalled, testDebtNotFoundDeleteIsSkipped, testDebtNullDoesNotThrowFatalError, testDebtVariableIsDistinctFromDclient |
