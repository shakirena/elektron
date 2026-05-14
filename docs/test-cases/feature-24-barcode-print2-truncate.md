# Test Cases: Feature #24 — Обрезка длинных названий товаров при печати чека 40x20мм

*Создано: 2026-05-14*
*QA-lead: qa-lead*
*Issue: https://github.com/shakirena/elektron/issues/24*
*Stories: #25, #26*

---

## Контекст

Шаблон `views/barcode/print2.php` обрезает названия товаров до 28 символов с добавлением `…` при печати этикеток 40x20 мм. Логика использует `mb_strlen` / `mb_substr` с UTF-8 для корректной обработки кириллицы.

---

## Story #25: Обрезка длинного названия с добавлением многоточия

### TC-24-001: Happy Path — длинное название обрезается

| Поле | Значение |
|------|----------|
| **ID** | TC-24-001 |
| **Story** | #25 |
| **Приоритет** | High |
| **Тип** | Happy Path |
| **AC** | Given длинное название > 28 символов → Then отображается обрезанным с `…` |

**Шаги:**

1. Товар с названием «Кабель USB Type-C 1.5м нейлон чёрный» (37 символов)
2. Вызов логики обрезки (шаблон print2.php)
3. Проверить результат

**Ожидаемый результат:**

- Результат равен «Кабель USB Type-C 1.5м нейло…»
- Длина результата = 29 символов (28 + символ «…»)
- Строка заканчивается на «…»
- `$size = '9px'`

**Unit-тест:** `testLongNameTruncated` в `Print2TruncateTest.php`

---

### TC-24-002: Happy Path — длина обрезанной части ровно 28 символов

| Поле | Значение |
|------|----------|
| **ID** | TC-24-002 |
| **Story** | #25 |
| **Приоритет** | High |
| **Тип** | Happy Path |
| **AC** | MAX_CHARS = 28 (FR-2) |

**Шаги:**

1. Товар с названием из 50 символов «Б»
2. Применить логику обрезки

**Ожидаемый результат:**

- Часть до «…» содержит ровно 28 символов
- `mb_strlen(mb_substr(result, 0, -1, 'UTF-8'), 'UTF-8') === 28`

**Unit-тест:** `testTruncatedLengthIsMaxChars` в `Print2TruncateTest.php`

---

### TC-24-003: Edge Case — название на 1 символ длиннее лимита

| Поле | Значение |
|------|----------|
| **ID** | TC-24-003 |
| **Story** | #25 |
| **Приоритет** | Medium |
| **Тип** | Error Case (граничный случай) |
| **AC** | Обрезка при mb_strlen > 28 (FR-1) |

**Шаги:**

1. Товар с названием из 29 символов (maxChars + 1)
2. Применить логику обрезки

**Ожидаемый результат:**

- Строка заканчивается на «…»

**Unit-тест:** `testNameOneOverLimitTruncated` в `Print2TruncateTest.php`

---

### TC-24-004: Happy Path — кириллические многобайтовые символы

| Поле | Значение |
|------|----------|
| **ID** | TC-24-004 |
| **Story** | #25 |
| **Приоритет** | High |
| **Тип** | Happy Path |
| **AC** | Использовать mb_strlen/mb_substr (FR-6) |

**Шаги:**

1. Товар с названием из 35 кириллических символов «Ж» (каждый по 2 байта UTF-8)
2. Применить логику обрезки

**Ожидаемый результат:**

- Строка заканчивается на «…»
- Обрезанная часть = ровно 28 символов (не 28 байт)
- `strlen(result) != mb_strlen(result)` — подтверждает многобайтовую обработку

**Unit-тест:** `testCyrillicMultibyteHandled` в `Print2TruncateTest.php`

---

### TC-24-005: Проверка кода — mb_strlen в print2.php

| Поле | Значение |
|------|----------|
| **ID** | TC-24-005 |
| **Story** | #25 |
| **Приоритет** | High |
| **Тип** | Code Structure |
| **AC** | FR-6: использовать mb_strlen/mb_substr |

**Проверка:**

- Файл `views/barcode/print2.php` содержит `mb_strlen($productName, 'UTF-8')`
- Файл содержит `mb_substr($productName, 0, $maxChars, 'UTF-8')`
- Файл содержит `$maxChars = 28`
- Файл содержит `'…'` (символ многоточия U+2026)

**Unit-тест:** `testPrint2ViewUsesMbStrlen`, `testPrint2ViewUsesMbSubstr`, `testPrint2ViewHasCorrectMaxChars`, `testPrint2ViewAddsEllipsis` в `Print2TruncateTest.php`

---

### TC-24-006: XSS-защита — Html::encode в print2.php

| Поле | Значение |
|------|----------|
| **ID** | TC-24-006 |
| **Story** | #25 |
| **Приоритет** | High |
| **Тип** | Security |
| **AC** | security:passed — Html::encode($displayName) |

**Проверка:**

- Файл `views/barcode/print2.php` содержит `Html::encode($displayName)`
- Вывод названия товара защищён от XSS

**Unit-тест:** `testPrint2ViewUsesHtmlEncode` в `Print2TruncateTest.php`

---

## Story #26: Корректное отображение коротких названий без обрезки

### TC-24-007: Happy Path — короткое название отображается без изменений

| Поле | Значение |
|------|----------|
| **ID** | TC-24-007 |
| **Story** | #26 |
| **Приоритет** | High |
| **Тип** | Happy Path |
| **AC** | Given название ≤ 28 символов → Then отображается полностью без «…» |

**Шаги:**

1. Товар с названием «Кабель HDMI 1м» (14 символов)
2. Применить логику обрезки

**Ожидаемый результат:**

- Результат идентичен входному названию «Кабель HDMI 1м»
- Многоточие «…» НЕ добавляется

**Unit-тест:** `testShortNameUnchanged` в `Print2TruncateTest.php`

---

### TC-24-008: Edge Case — название ровно 28 символов не обрезается

| Поле | Значение |
|------|----------|
| **ID** | TC-24-008 |
| **Story** | #26 |
| **Приоритет** | High |
| **Тип** | Error Case (граничный случай) |
| **AC** | FR-5: короткие названия (≤ MAX_CHARS) отображаются без изменений |

**Шаги:**

1. Товар с названием из 28 символов «А» (ровно maxChars)
2. Применить логику обрезки

**Ожидаемый результат:**

- Результат = исходное название (28 символов «А»)
- Многоточие НЕ добавляется
- Граничный случай: mb_strlen == maxChars не триггерит обрезку

**Unit-тест:** `testExactlyMaxCharsUnchanged` в `Print2TruncateTest.php`

---

### TC-24-009: Edge Case — пустое название не изменяется

| Поле | Значение |
|------|----------|
| **ID** | TC-24-009 |
| **Story** | #26 |
| **Приоритет** | Medium |
| **Тип** | Error Case |
| **AC** | FR-5: короткие (≤ 28 символов, включая пустую строку) без изменений |

**Шаги:**

1. Пустое название товара `''`
2. Применить логику обрезки

**Ожидаемый результат:**

- Результат = `''` (пустая строка)
- Нет ошибок, нет многоточия

**Unit-тест:** `testEmptyNameUnchanged` в `Print2TruncateTest.php`

---

### TC-24-010: RBAC — шаблон print2.php без авторизационной логики

| Поле | Значение |
|------|----------|
| **ID** | TC-24-010 |
| **Story** | #25 и #26 |
| **Приоритет** | Low |
| **Тип** | RBAC Test Case |
| **AC** | FR-4: изменение только в print2.php, RBAC не затрагивается |

**Проверка:**

- Логика обрезки находится в View-слое, не в Controller/Model
- Нет новых RBAC-правил или проверок ролей
- Нет новых endpoints
- Контроллер `BarcodeController::actionPrint2` не изменён

**Статус:** PASS (подтверждено security review)

---

## Сводная таблица TC

| TC ID | Story | Тип | Unit-тест | Статус |
|-------|-------|-----|-----------|--------|
| TC-24-001 | #25 | Happy Path | testLongNameTruncated | PASS |
| TC-24-002 | #25 | Happy Path | testTruncatedLengthIsMaxChars | PASS |
| TC-24-003 | #25 | Edge Case | testNameOneOverLimitTruncated | PASS |
| TC-24-004 | #25 | Happy Path (multibyte) | testCyrillicMultibyteHandled | PASS |
| TC-24-005 | #25 | Code Structure | testPrint2ViewUsesMbStrlen, testPrint2ViewUsesMbSubstr, testPrint2ViewHasCorrectMaxChars, testPrint2ViewAddsEllipsis | PASS |
| TC-24-006 | #25 | Security | testPrint2ViewUsesHtmlEncode | PASS |
| TC-24-007 | #26 | Happy Path | testShortNameUnchanged | PASS |
| TC-24-008 | #26 | Edge Case | testExactlyMaxCharsUnchanged | PASS |
| TC-24-009 | #26 | Error Case | testEmptyNameUnchanged | PASS |
| TC-24-010 | #25+#26 | RBAC | (static verification) | PASS |

**Итого: 10 TC, все PASS**

---

## AC Traceability

| AC | Описание | TC ID | Покрыт |
|----|----------|-------|--------|
| AC-1 (Story #25) | Обрезка до 28 символов + «…» | TC-24-001, TC-24-002, TC-24-003 | Да |
| AC-1 (Story #26) | Короткие названия без изменений | TC-24-007, TC-24-008 | Да |
| FR-1 | Обрезка с «…» (U+2026) | TC-24-001, TC-24-005 | Да |
| FR-2 | MAX_CHARS = 28 | TC-24-002, TC-24-005 | Да |
| FR-3 | БД не изменяется | TC-24-010 (RBAC check) | Да |
| FR-5 | Короткие без изменений | TC-24-007, TC-24-008, TC-24-009 | Да |
| FR-6 | Кириллица через mb_* | TC-24-004, TC-24-005 | Да |
