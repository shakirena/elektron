# Test Case Traceability Matrix

*Инициализировано: 2026-05-07*
*Обновлено: 2026-08-13 (Feature #27 — story #31: TC-27-010, TC-27-011, TC-27-012, TC-27-013, TC-27-014, TC-27-RBAC-4)*

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

## Feature #24 — Обрезка длинных названий при печати 40x20мм

| Feature | Story | AC | TC | Priority | Type | E2E Automated |
|---------|-------|----|----|----------|------|---------------|
| Feature #24 | #25 | Given длинное название > 28 символов Then обрезается с «…» | TC-24-001 | High | Happy Path | No |
| Feature #24 | #25 | MAX_CHARS = 28 символов | TC-24-002 | High | Happy Path | No |
| Feature #24 | #25 | Граничный случай: 29 символов обрезается | TC-24-003 | Medium | Edge Case | No |
| Feature #24 | #25 | Кириллица обрабатывается через mb_* | TC-24-004 | High | Happy Path | No |
| Feature #24 | #25 | print2.php использует mb_strlen/mb_substr/$maxChars/«…» | TC-24-005 | High | Code Structure | No |
| Feature #24 | #25 | XSS-защита: Html::encode($displayName) | TC-24-006 | High | Security | No |
| Feature #24 | #26 | Given короткое название ≤ 28 Then отображается полностью | TC-24-007 | High | Happy Path | No |
| Feature #24 | #26 | Граничный случай: ровно 28 символов — без обрезки | TC-24-008 | High | Edge Case | No |
| Feature #24 | #26 | Пустое название — без изменений | TC-24-009 | Medium | Error Case | No |
| Feature #24 | #25+#26 | RBAC: логика в View-слое, без новых ролей | TC-24-010 | Low | RBAC | No |

## Feature #24 Unit Test Mapping

| TC | Unit Test File | Test Method |
|----|---------------|-------------|
| TC-24-001 | Print2TruncateTest.php | testLongNameTruncated |
| TC-24-002 | Print2TruncateTest.php | testTruncatedLengthIsMaxChars |
| TC-24-003 | Print2TruncateTest.php | testNameOneOverLimitTruncated |
| TC-24-004 | Print2TruncateTest.php | testCyrillicMultibyteHandled |
| TC-24-005 | Print2TruncateTest.php | testPrint2ViewUsesMbStrlen, testPrint2ViewUsesMbSubstr, testPrint2ViewHasCorrectMaxChars, testPrint2ViewAddsEllipsis |
| TC-24-006 | Print2TruncateTest.php | testPrint2ViewUsesHtmlEncode |
| TC-24-007 | Print2TruncateTest.php | testShortNameUnchanged |
| TC-24-008 | Print2TruncateTest.php | testExactlyMaxCharsUnchanged |
| TC-24-009 | Print2TruncateTest.php | testEmptyNameUnchanged |
| TC-24-010 | Static: views/barcode/print2.php + security review comment | — |

## Feature #27 — Отчёт «Движение товара»

| Feature | Story | AC | TC | Priority | Type | E2E Automated |
|---------|-------|----|----|----------|------|---------------|
| Feature #27 | #28 | returnp.data содержит DATETIME после возврата | TC-27-001 | High | Happy Path | No |
| Feature #27 | #28 | Старые записи returnp (до миграции) — время 00:00:00 | TC-27-002 | Medium | Error/Edge Case | No |
| Feature #27 | #28 | Два возврата подряд фиксируют разное время | TC-27-003 | Medium | Edge Case | No |
| Feature #27 | #28 | Неаутентифицированный не может вызвать actionReceivedReturn | TC-27-RBAC-1 | High | RBAC | No |
| Feature #27 | #29 | Запись в sverka_log при применении сверки | TC-27-004 | High | Happy Path | No |
| Feature #27 | #29 | qty_before == qty_after → delta=0, запись всё равно создаётся | TC-27-005 | Medium | Error/Edge Case | No |
| Feature #27 | #29 | Несколько позиций в сверке → несколько записей в sverka_log | TC-27-006 | Medium | Edge Case | No |
| Feature #27 | #29 | Неаутентифицированный не может применить сверку | TC-27-RBAC-2 | High | RBAC | No |
| Feature #27 | #30 | UNION из 6 источников, сортировка DESC | TC-27-007 | High | Happy Path | No |
| Feature #27 | #30 | guard-логика: id_product required | TC-27-008 | High | Error Case | No |
| Feature #27 | #30 | фильтры date_from/date_to, id_store, operation_type | TC-27-009 | Medium | Functional | No |
| Feature #27 | #30 | NFR-2 — RBAC на уровне контроллера, не модели | TC-27-RBAC-3 | High | RBAC | No |
| Feature #27 | #31 | GridView с хронологическим списком операций по выбранному товару | TC-27-010 | High | Happy Path | No |
| Feature #27 | #31 | Предупреждение при открытии без выбора товара | TC-27-011 | High | Error Case | No |
| Feature #27 | #31 | Фильтрация по диапазону дат сужает результат | TC-27-012 | Medium | Functional | No |
| Feature #27 | #31 | Типы операций отображаются цветными badge | TC-27-013 | Medium | UI | No |
| Feature #27 | #31 | Footer GridView показывает сумму quantity | TC-27-014 | Low | UI | No |
| Feature #27 | #31 | NFR-2 — неаутентифицированный перенаправляется на login | TC-27-RBAC-4 | Critical | RBAC | No |

## Feature #27 Unit Test Mapping

| TC | Unit Test File | Test Method |
|----|---------------|-------------|
| TC-27-001 | ReturnpDatetimeTest.php | testSellControllerUsesDatetimeFormat, testDatetimeFormatContainsTime |
| TC-27-002 | ReturnpDatetimeTest.php | testDateOnlyFormatLacksTime |
| TC-27-003 | ReturnpDatetimeTest.php | testDatetimeStringLongerThanDateOnly, testSellControllerDoesNotUseDateOnlyForReturnp |
| TC-27-004 | SverkaLogTest.php | testTableName, testRulesContainRequiredFields, testAttributeLabelsCovertAllPersistedFields |
| TC-27-005 | SverkaLogTest.php | testDeltaCalculationZero |
| TC-27-006 | SverkaLogTest.php | testDeltaCalculationPositive, testDeltaCalculationNegative |
| TC-27-RBAC-1 | Static: AccessControl в SellController + NFR-2 | — |
| TC-27-RBAC-2 | Static: AccessControl в SverkaController + NFR-2 | — |
| TC-27-007 | ProductMovementSearchTest.php | (integration test — happy path требует реальной БД; unit-покрытие обратного случая: testEmptyDataProviderWhenIdProductMissing) |
| TC-27-008 | ProductMovementSearchTest.php | testEmptyDataProviderWhenIdProductMissing, testRulesRequireIdProduct |
| TC-27-009 | ProductMovementSearchTest.php | testBuildBindingsMapping, testBuildBindingsNullableFiltersAreNull, testBuildUnionSqlContainsAllSources |
| TC-27-RBAC-3 | Static: ProductMovementSearch не содержит AccessControl | — |
| TC-27-010 | RunProductMovementTests.php | testActionReportDelegatesSearchToModel |
| TC-27-011 | RunProductMovementTests.php | testActionReportDelegatesSearchToModel (guard-case) |
| TC-27-012 | RunProductMovementTests.php | testBuildBindingsMapping (via ProductMovementSearch) |
| TC-27-013 | Static: views/product-movement/report.php badge closures | — |
| TC-27-014 | Static: views/product-movement/report.php footer array_sum | — |
| TC-27-RBAC-4 | RunProductMovementTests.php | testBehaviorsContainsAccessControl, testAccessControlAllowsOnlyAuthenticatedRole |
