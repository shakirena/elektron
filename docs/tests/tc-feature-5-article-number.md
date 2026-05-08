# Test Cases — Feature #5: Artikul nomresi (Article Number)

**Feature:** #5 — Артикульный номер товара  
**Stories covered:** #6, #7, #8, #9, #10, #11, #12  
**Created:** 2026-05-08  
**Author:** qa-lead  
**Status:** Final

---

## Traceability

| TC ID    | Story | AC Description                                         | Unit Test Method                              |
|----------|-------|--------------------------------------------------------|-----------------------------------------------|
| TC-5-001 | #8    | Product creation with article_number persists          | (integration — manual)                        |
| TC-5-002 | #9    | Product edit — article_number is saved correctly       | (integration — manual)                        |
| TC-5-003 | #7    | article_number > 100 chars is rejected by validation   | `testOver100CharsIsInvalid`                   |
| TC-5-004 | #6    | article_number nullable — product creation without it  | `testNullArticleNumberIsValid`                |
| TC-5-005 | #10   | Sell report shows Artikul nomresi column               | Static verification — views/sell/report.php   |
| TC-5-006 | #11   | Rest report shows Artikul nomresi column               | Static verification — views/sell/rest.php     |
| TC-5-007 | #12   | Arrival report shows Artikul nomresi column            | Static verification — views/arrival/report.php|
| TC-5-008 | #10   | Sell find modal shows Artikul nomresi column           | Static verification — views/sell/find.php     |
| TC-5-009 | #12   | Arrival find modal shows Artikul nomresi column        | Static verification — views/arrival/find.php  |

---

## TC-5-001: Happy path — создание товара с article_number

**Story:** #8 (форма создания товара)  
**Type:** Functional / Integration  
**Priority:** High

### Given
Пользователь находится на странице создания нового товара (`/product/create`).

### When
Пользователь заполняет поле «Artikul nomresi» значением `ART-2026-001` и сохраняет форму.

### Then
- Товар создаётся в БД с `article_number = 'ART-2026-001'`
- Поле отображается в форме редактирования между «Malın adı» и «Mal grupu»
- Нет валидационных ошибок

### Verification points
- [ ] Поле `article_number` присутствует в `views/product/_form.php` (строка 25)
- [ ] Поле расположено между `name` и `id_type` (confirmed in file)
- [ ] `maxlength` атрибут выставлен через `['maxlength' => true]`

**Expected result:** PASS

---

## TC-5-002: Редактирование товара — поле article_number сохраняется

**Story:** #9 (редактирование товара)  
**Type:** Functional / Integration  
**Priority:** High

### Given
Существующий товар с `article_number = 'OLD-001'`.

### When
Пользователь открывает форму редактирования, меняет `article_number` на `NEW-001` и сохраняет.

### Then
- В БД обновляется `article_number = 'NEW-001'`
- Форма повторно загружается с новым значением

### Verification points
- [ ] Поле `article_number` присутствует в `rules()` с validator `string, max=>100`
- [ ] `attributeLabels()` возвращает `'Artikul nomresi'` для ключа `article_number`

**Expected result:** PASS

---

## TC-5-003: Валидация — article_number > 100 символов отклоняется

**Story:** #7 (валидация модели)  
**Type:** Unit  
**Priority:** High

### Given
Объект `Product` с `article_number` длиной 101 символ.

### When
Вызывается `$model->validate(['article_number'])`.

### Then
- Метод возвращает `false`
- `$model->hasErrors('article_number')` возвращает `true`

### Verification points
- [ ] Unit test `testOver100CharsIsInvalid` проходит (GREEN)
- [ ] Unit test `testExactly100CharsIsValid` проходит (граничное значение)

**Expected result:** PASS  
**Unit test file:** `tests/codeception/unit/models/ProductArticleNumberTest.php`

---

## TC-5-004: article_number nullable — создание без поля работает

**Story:** #6 (nullable поле)  
**Type:** Unit + Migration  
**Priority:** High

### Given
Объект `Product` без установки `article_number` (значение `null`).

### When
Вызывается `$model->validate(['article_number'])`.

### Then
- Метод возвращает `true`
- Нет ошибок валидации для `article_number`

### Verification points
- [ ] Unit test `testNullArticleNumberIsValid` проходит (GREEN)
- [ ] Unit test `testEmptyStringArticleNumberIsValid` проходит
- [ ] Unit test `testArticleNumberIsNotRequired` проходит
- [ ] Миграция `m260508_000001_add_article_number_to_product.php` использует `->null()` (confirmed)

**Expected result:** PASS  
**Unit test file:** `tests/codeception/unit/models/ProductArticleNumberTest.php`

---

## TC-5-005: Отчёт Продажи — колонка Artikul nomresi видна

**Story:** #10  
**Type:** Static / UI verification  
**Priority:** Medium

### Given
Пользователь открывает отчёт продаж (`/sell/report`).

### When
Загружается GridView с данными продаж.

### Then
- Колонка с label `'Artikul nomresi'` отображается после колонки barcode
- Значение берётся из `idProduct.article_number`

### Verification points
- [ ] `views/sell/report.php` строки 183–187: колонка `Artikul nomresi` присутствует (confirmed)
- [ ] `value => 'idProduct.article_number'` — связь через relation `idProduct` (confirmed)

**Expected result:** PASS

---

## TC-5-006: Отчёт Остатки — колонка Artikul nomresi видна

**Story:** #11  
**Type:** Static / UI verification  
**Priority:** Medium

### Given
Пользователь открывает отчёт остатков (`/sell/rest` или `/arrival/table-rest`).

### When
Загружается GridView с данными остатков.

### Then
- Колонка `Artikul nomresi` отображается
- Значение вычисляется через closure: `$model->idProduct ? $model->idProduct->article_number : ''`

### Verification points
- [ ] `views/sell/rest.php` строки 177–182: closure-колонка присутствует (confirmed)
- [ ] null-safe проверка `$model->idProduct ?` защищает от ошибок при отсутствии связи (confirmed)

**Expected result:** PASS

---

## TC-5-007: Отчёт Приход — колонка Artikul nomresi видна

**Story:** #12  
**Type:** Static / UI verification  
**Priority:** Medium

### Given
Пользователь открывает отчёт прихода (`/arrival/report`).

### When
Загружается GridView с данными прихода.

### Then
- Колонка `Artikul nomresi` отображается после barcode
- Значение берётся из `idProduct.article_number`

### Verification points
- [ ] `views/arrival/report.php` строки 140–144: колонка `Artikul nomresi` присутствует (confirmed)
- [ ] `value => 'idProduct.article_number'` (confirmed)

**Expected result:** PASS

---

## TC-5-008: Модальное окно Продажи (find) — колонка Artikul nomresi видна

**Story:** #10  
**Type:** Static / UI verification  
**Priority:** Medium

### Given
Пользователь открывает модальное окно поиска товара при создании продажи (`/sell/find`).

### When
GridView загружается с таблицей товаров.

### Then
- Колонка `Artikul nomresi` отображается после barcode
- Значение: `product.article_number`

### Verification points
- [ ] `views/sell/find.php` строки 157–163: колонка присутствует (confirmed)
- [ ] `value => 'product.article_number'` — прямое обращение к relation `product` (confirmed)

**Expected result:** PASS

---

## TC-5-009: Модальное окно Прихода (find) — колонка Artikul nomresi видна

**Story:** #12  
**Type:** Static / UI verification  
**Priority:** Medium

### Given
Пользователь открывает модальное окно поиска товара при создании прихода (`/arrival/find`).

### When
GridView загружается с таблицей товаров.

### Then
- Колонка `Artikul nomresi` отображается после barcode
- Значение: `article_number` (прямой атрибут модели Product)

### Verification points
- [ ] `views/arrival/find.php` строки 104–110: колонка присутствует (confirmed)
- [ ] `value => 'article_number'` — прямой атрибут, т.к. dataProvider — это Product-записи (confirmed)

**Expected result:** PASS

---

## Error Cases

| TC ID     | Scenario                                    | Expected behaviour                          |
|-----------|---------------------------------------------|---------------------------------------------|
| TC-5-003E | article_number = 101 символ                 | validate() = false, hasErrors() = true      |
| TC-5-E02  | article_number = null в форме               | Форма сохраняется без ошибки                |
| TC-5-E03  | idProduct = null в sell/rest GridView       | Closure вернёт '', колонка не упадёт        |

---

## RBAC Test Cases

| TC ID     | Role              | Action              | Expected                    |
|-----------|-------------------|---------------------|-----------------------------|
| TC-5-R01  | authenticated     | Create product      | article_number field visible |
| TC-5-R02  | authenticated     | View sell report    | Artikul nomresi column shown |
| TC-5-R03  | guest (not logged)| Access /product     | Redirect to login           |

*Note: project uses simple role model (guest / authenticated). No per-role field restriction on article_number.*
