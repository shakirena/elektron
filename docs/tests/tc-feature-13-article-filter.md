# Test Cases — Feature #13: Фильтр article_number в отчётах

**Feature:** #13 — Фильтр article_number в отчётах  
**Stories covered:** #14 (SellSearch + sell/report), #15 (ArrivalSearch + arrival/report), #16 (RestSearch + arrival/rest)  
**Created:** 2026-05-08  
**Author:** qa-lead  
**Status:** Final

---

## Traceability

| TC ID      | Story | AC Description                                                   | Unit Test Method                                          |
|------------|-------|------------------------------------------------------------------|-----------------------------------------------------------|
| TC-13-001  | #14   | Sell report: ввод артикула фильтрует строки                      | `testSellSearchContainsArticleNumberLikeFilter`           |
| TC-13-002  | #14   | Sell report: пустой фильтр показывает все записи                 | `testSellSearchArticleNumberInSafeRules`                  |
| TC-13-003  | #15   | Arrival report: ввод артикула фильтрует строки                   | `testArrivalSearchContainsArticleNumberLikeFilter`        |
| TC-13-004  | #15   | Arrival report: пустой фильтр показывает все записи              | `testArrivalSearchArticleNumberInSafeRules`               |
| TC-13-005  | #16   | Rest report: колонка Artikul nomresi отображается                | `testRestViewContainsArticleNumberColumnAndAttribute`     |
| TC-13-006  | #16   | Rest report: ввод артикула фильтрует строки                      | `testRestSearchContainsArticleNumberLikeFilter`           |
| TC-13-007  | #14/#15/#16 | Фильтр без совпадений: пустая таблица                       | Static: andFilterWhere LIKE verified in all three models  |

---

## TC-13-001: Отчёт Продажи — ввод артикула фильтрует строки

**Story:** #14 (SellSearch + views/sell/report.php)  
**Type:** Functional / Unit (static verification)  
**Priority:** High

### Given
Пользователь открывает `/sell/report`. В базе есть товары с разными `article_number`.

### When
Пользователь вводит частичный артикул (например, `ART-2026`) в фильтр-строку колонки «Artikul nomresi» и применяет фильтр.

### Then
- GridView отображает только строки, у которых `product.article_number LIKE '%ART-2026%'`
- Строки с другим артикулом не отображаются

### Verification points
- [x] `SellSearch::rules()` содержит `article_number` в safe rule (строка 31)
- [x] `SellSearch::search()` содержит `andFilterWhere(['like', 'product.article_number', $this->article_number])` (строка 118)
- [x] `views/sell/report.php` объявляет `'attribute' => 'article_number'` в описании колонки (строки 183-188)
- [x] `'value' => 'idProduct.article_number'` обеспечивает вывод значения

**Unit test file:** `tests/codeception/unit/models/ArticleNumberFilterTest.php`  
**Test methods:** `testSellSearchArticleNumberInSafeRules`, `testSellSearchContainsArticleNumberLikeFilter`, `testSellReportViewContainsArticleNumberAttribute`

**Expected result:** PASS

---

## TC-13-002: Отчёт Продажи — пустой фильтр показывает все записи

**Story:** #14  
**Type:** Functional / Unit  
**Priority:** High

### Given
Пользователь открывает `/sell/report` без заполнения фильтра.

### When
`SellSearch::search()` вызывается с `article_number = null` (или пустая строка).

### Then
- `andFilterWhere` пропускает условие (Yii2 `andFilterWhere` игнорирует null/пустые значения)
- Отображаются все записи без ограничения по артикулу

### Verification points
- [x] Используется `andFilterWhere` (не `andWhere`) — null-safe поведение гарантировано фреймворком
- [x] `article_number` в safe rule принимает null и пустую строку без ошибок валидации

**Unit test file:** `tests/codeception/unit/models/ArticleNumberFilterTest.php`  
**Test methods:** `testSellSearchArticleNumberIsNotInNumericRule`

**Expected result:** PASS

---

## TC-13-003: Отчёт Приход — ввод артикула фильтрует строки

**Story:** #15 (ArrivalSearch + views/arrival/report.php)  
**Type:** Functional / Unit (static verification)  
**Priority:** High

### Given
Пользователь открывает `/arrival/report`. В базе есть товары с разными `article_number`.

### When
Пользователь вводит артикул в фильтр-строку колонки «Artikul nomresi» и применяет фильтр.

### Then
- GridView отображает только строки с совпадающим `product.article_number LIKE '%...%'`

### Verification points
- [x] `ArrivalSearch::rules()` содержит `article_number` в safe rule (строка 29)
- [x] `ArrivalSearch::search()` содержит `andFilterWhere(['like', 'product.article_number', $this->article_number])` (строка 102)
- [x] `views/arrival/report.php` объявляет `'attribute' => 'article_number'` (строки 140-145)
- [x] `'value' => 'idProduct.article_number'` обеспечивает вывод значения

**Unit test file:** `tests/codeception/unit/models/ArticleNumberFilterTest.php`  
**Test methods:** `testArrivalSearchArticleNumberInSafeRules`, `testArrivalSearchContainsArticleNumberLikeFilter`, `testArrivalReportViewContainsArticleNumberAttribute`

**Expected result:** PASS

---

## TC-13-004: Отчёт Приход — пустой фильтр показывает все записи

**Story:** #15  
**Type:** Functional / Unit  
**Priority:** High

### Given
Пользователь открывает `/arrival/report` без заполнения фильтра по артикулу.

### When
`ArrivalSearch::search()` вызывается с `article_number = null`.

### Then
- `andFilterWhere` пропускает условие, отображаются все записи прихода

### Verification points
- [x] Используется `andFilterWhere` — null-safe
- [x] `article_number` в safe rule ArrivalSearch принимает null без ошибок

**Unit test file:** `tests/codeception/unit/models/ArticleNumberFilterTest.php`  
**Test methods:** `testArrivalSearchArticleNumberIsNotInNumericRule`

**Expected result:** PASS

---

## TC-13-005: Отчёт Остатки — колонка Artikul nomresi отображается

**Story:** #16 (RestSearch + views/arrival/rest.php)  
**Type:** Static / UI verification  
**Priority:** Medium

### Given
Пользователь открывает `/arrival/rest` (отчёт остатков).

### When
Загружается GridView с таблицей остатков.

### Then
- В таблице присутствует колонка с label «Artikul nomresi»
- Значение отображается из `idProduct.article_number`
- Колонка имеет фильтр-поле (поскольку объявлен `attribute`)

### Verification points
- [x] `views/arrival/rest.php` строки 102-108: объявлена колонка с `'attribute' => 'article_number'`
- [x] `'value' => 'idProduct.article_number'` — связь через relation `idProduct`
- [x] `'label' => 'Artikul nomresi'` — корректная метка

**Unit test file:** `tests/codeception/unit/models/ArticleNumberFilterTest.php`  
**Test method:** `testRestViewContainsArticleNumberColumnAndAttribute`

**Expected result:** PASS

---

## TC-13-006: Отчёт Остатки — ввод артикула фильтрует строки

**Story:** #16  
**Type:** Functional / Unit (static verification)  
**Priority:** High

### Given
Пользователь открывает `/arrival/rest`. В базе есть товары с разными `article_number`.

### When
Пользователь вводит артикул в фильтр-поле колонки «Artikul nomresi».

### Then
- GridView отображает только остатки товаров, у которых `product.article_number LIKE '%...%'`

### Verification points
- [x] `RestSearch::rules()` содержит `article_number` в safe rule (строка 30)
- [x] `RestSearch::search()` содержит `andFilterWhere(['like', 'product.article_number', $this->article_number])` (строка 115)
- [x] `views/arrival/rest.php` объявляет `'attribute' => 'article_number'` для активации фильтра в GridView

**Unit test file:** `tests/codeception/unit/models/ArticleNumberFilterTest.php`  
**Test methods:** `testRestSearchArticleNumberInSafeRules`, `testRestSearchContainsArticleNumberLikeFilter`, `testRestViewContainsArticleNumberColumnAndAttribute`

**Expected result:** PASS

---

## TC-13-007: Фильтр без совпадений — пустая таблица

**Story:** #14 / #15 / #16  
**Type:** Error Case  
**Priority:** Medium

### Given
В базе нет товаров с артикулом, содержащим введённую строку (например, `ZZZNOTEXIST`).

### When
Пользователь вводит `ZZZNOTEXIST` в фильтр любого из трёх отчётов.

### Then
- GridView отображает пустую таблицу (ноль строк)
- Нет ошибки PHP/SQL
- Страница остаётся работоспособной

### Verification points
- [x] Yii2 `andFilterWhere(['like', ...])` возвращает 0 строк без exception при отсутствии совпадений
- [x] `article_number` является `safe` во всех трёх Search-моделях — параметр принимается без ошибки валидации
- [x] Не используется `andWhere` (который бросил бы ошибку для пустого набора)

**Expected result:** PASS

---

## Error Cases Summary

| TC ID      | Scenario                                          | Expected behaviour                               |
|------------|---------------------------------------------------|--------------------------------------------------|
| TC-13-007  | Фильтр без совпадений во всех трёх отчётах        | Пустая таблица, нет PHP/SQL ошибок               |
| TC-13-E01  | article_number = null при загрузке страницы       | andFilterWhere игнорирует null, все записи видны |
| TC-13-E02  | article_number содержит спецсимволы SQL (%, _)    | LIKE-фильтр обрабатывается Yii2 PDO — безопасно  |

---

## RBAC Test Cases

| TC ID      | Role              | Action                         | Expected                                  |
|------------|-------------------|--------------------------------|-------------------------------------------|
| TC-13-R01  | authenticated     | Открыть /sell/report с фильтром | Фильтр работает, данные отображаются     |
| TC-13-R02  | authenticated     | Открыть /arrival/report с фильтром | Фильтр работает                        |
| TC-13-R03  | authenticated     | Открыть /arrival/rest с фильтром | Фильтр работает, колонка видна          |
| TC-13-R04  | guest (not logged) | Доступ к /sell/report          | Redirect на login                        |

*Note: фильтр `article_number` не имеет дополнительных RBAC-ограничений сверх базовой аутентификации.*
