# Test Case Traceability Matrix

*Инициализировано: 2026-05-07*
*Обновлено: 2026-05-08 (Bug #1 — stories #2, #3, #4; Feature #5 — stories #6–#12)*

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

## Feature #5 — Artikul nomresi

| Feature | Story | AC | TC | Priority | Type | E2E Automated |
|---------|-------|----|----|----------|------|---------------|
| Feature #5 | #6 | article_number nullable в БД | TC-5-004 | High | Unit + Migration | No |
| Feature #5 | #7 | article_number string max 100 | TC-5-003 | High | Unit | No |
| Feature #5 | #7 | article_number not required | TC-5-004 | High | Unit | No |
| Feature #5 | #7 | attributeLabel = 'Artikul nomresi' | TC-5-006 (label) | High | Unit | No |
| Feature #5 | #8 | Форма создания — поле article_number | TC-5-001 | High | Functional | No |
| Feature #5 | #9 | Форма редактирования — article_number сохраняется | TC-5-002 | High | Functional | No |
| Feature #5 | #10 | Sell report — колонка Artikul nomresi | TC-5-005 | Medium | Static/UI | No |
| Feature #5 | #10 | Sell find modal — колонка Artikul nomresi | TC-5-008 | Medium | Static/UI | No |
| Feature #5 | #11 | Rest report — колонка Artikul nomresi | TC-5-006 | Medium | Static/UI | No |
| Feature #5 | #12 | Arrival report — колонка Artikul nomresi | TC-5-007 | Medium | Static/UI | No |
| Feature #5 | #12 | Arrival find modal — колонка Artikul nomresi | TC-5-009 | Medium | Static/UI | No |

## Feature #5 Unit Test Mapping

| TC | Unit Test File | Test Method |
|----|---------------|-------------|
| TC-5-001 | ProductArticleNumberTest.php | testValidArticleNumberPasses |
| TC-5-003 | ProductArticleNumberTest.php | testOver100CharsIsInvalid, testExactly100CharsIsValid |
| TC-5-004 | ProductArticleNumberTest.php | testNullArticleNumberIsValid, testEmptyStringArticleNumberIsValid, testArticleNumberIsNotRequired |
| TC-5-label | ProductArticleNumberTest.php | testAttributeLabelIsArticulNomresi |
| TC-5-rule | ProductArticleNumberTest.php | testStringRuleWithMax100Exists |
| TC-5-005 | Static: views/sell/report.php lines 183–187 | — |
| TC-5-006 | Static: views/sell/rest.php lines 177–182 | — |
| TC-5-007 | Static: views/arrival/report.php lines 140–144 | — |
| TC-5-008 | Static: views/sell/find.php lines 157–163 | — |
| TC-5-009 | Static: views/arrival/find.php lines 104–110 | — |

## Feature #13 — Фильтр article_number в отчётах

| Feature | Story | AC | TC | Priority | Type | E2E Automated |
|---------|-------|----|----|----------|------|---------------|
| Feature #13 | #14 | article_number в safe rules SellSearch | TC-13-001 | High | Unit | No |
| Feature #13 | #14 | SellSearch LIKE product.article_number filter | TC-13-001 | High | Unit | No |
| Feature #13 | #14 | sell/report.php attribute article_number | TC-13-001 | High | Static/UI | No |
| Feature #13 | #14 | Пустой фильтр — все записи (sell) | TC-13-002 | High | Unit | No |
| Feature #13 | #15 | article_number в safe rules ArrivalSearch | TC-13-003 | High | Unit | No |
| Feature #13 | #15 | ArrivalSearch LIKE product.article_number filter | TC-13-003 | High | Unit | No |
| Feature #13 | #15 | arrival/report.php attribute article_number | TC-13-003 | High | Static/UI | No |
| Feature #13 | #15 | Пустой фильтр — все записи (arrival) | TC-13-004 | High | Unit | No |
| Feature #13 | #16 | article_number в safe rules RestSearch | TC-13-006 | High | Unit | No |
| Feature #13 | #16 | RestSearch LIKE product.article_number filter | TC-13-006 | High | Unit | No |
| Feature #13 | #16 | arrival/rest.php колонка Artikul nomresi видна | TC-13-005 | Medium | Static/UI | No |
| Feature #13 | #16 | arrival/rest.php attribute article_number | TC-13-006 | High | Static/UI | No |
| Feature #13 | #14/#15/#16 | Фильтр без совпадений — пустая таблица | TC-13-007 | Medium | Error Case | No |

## Feature #13 Unit Test Mapping

| TC | Unit Test File | Test Method |
|----|---------------|-------------|
| TC-13-001 | ArticleNumberFilterTest.php | testSellSearchArticleNumberInSafeRules, testSellSearchContainsArticleNumberLikeFilter, testSellReportViewContainsArticleNumberAttribute |
| TC-13-002 | ArticleNumberFilterTest.php | testSellSearchArticleNumberIsNotInNumericRule |
| TC-13-003 | ArticleNumberFilterTest.php | testArrivalSearchArticleNumberInSafeRules, testArrivalSearchContainsArticleNumberLikeFilter, testArrivalReportViewContainsArticleNumberAttribute |
| TC-13-004 | ArticleNumberFilterTest.php | testArrivalSearchArticleNumberIsNotInNumericRule |
| TC-13-005 | ArticleNumberFilterTest.php | testRestViewContainsArticleNumberColumnAndAttribute |
| TC-13-006 | ArticleNumberFilterTest.php | testRestSearchArticleNumberInSafeRules, testRestSearchContainsArticleNumberLikeFilter, testRestViewContainsArticleNumberColumnAndAttribute |
| TC-13-007 | Static: andFilterWhere LIKE verified in all three Search models | — |
