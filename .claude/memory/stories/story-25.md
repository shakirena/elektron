# Story #25: Обрезка длинного названия товара в print2.php с добавлением многоточия

**Feature:** #24 — Обрезка длинных названий товаров при печати чека 40x20мм
**Branch:** feature/24-barcode-print2-truncate
**Status:** ready-to-deploy

---

## Задача

Как оператор склада, я хочу чтобы длинные названия товаров обрезались с добавлением «…» при печати этикетки 40x20мм, чтобы название помещалось в пределах этикетки и не выходило за её границы.

**AC (Given/When/Then):**
```gherkin
Given в базе есть товар с названием длиннее 28 символов (например, «Кабель USB Type-C 1.5м нейлон чёрный»)
When оператор открывает http://elektron/web/barcode/print2 для печати этикеток
Then название товара отображается как «Кабель USB Type-C 1.5м нейло…» (обрезано до 28 символов + «…»)
```

---

## Архитектура

- Файл изменений: `views/barcode/print2.php` строки 52-55
- MAX_CHARS = 28
- Использовать `mb_strlen($name, 'UTF-8')` и `mb_substr($name, 0, $maxChars, 'UTF-8')`
- БД не изменяется, контроллер не изменяется
- Unit-тест: `tests/codeception/unit/models/Print2TruncateTest.php`

Логика (из arch документа):
```php
$productName = $res->idProduct->name;
$maxChars = 28;
if (mb_strlen($productName, 'UTF-8') > $maxChars) {
    $displayName = mb_substr($productName, 0, $maxChars, 'UTF-8') . '…';
    $size = '9px';
} elseif (strlen($productName) > 30) {
    $displayName = $productName;
    $size = '9px';
} else {
    $displayName = $productName;
    $size = '12px';
}
```

---

## Реализация

- Изменён файл: `views/barcode/print2.php` — строки 52-55 заменены на mb_strlen/mb_substr логику
- Создан unit-тест: `tests/codeception/unit/models/Print2TruncateTest.php`

---

## Security Review

**Verdict:** PASS
**Label:** security:passed выставлен dev-lead

---

## QA

**Verdict:** PASS
**Coverage:** 100% (15/15 тестов, включая Story #26)
**Тест-раннер:** tests/run_print2_tests.php (standalone, без Codeception)
**TC-документ:** docs/test-cases/feature-24-barcode-print2-truncate.md
**Gate G5:** PASS 2026-05-14

### Результаты тестов (Story #25)

| Тест | Статус |
|------|--------|
| testLongNameTruncated: заканчивается на «…» | PASS |
| testLongNameTruncated: длина = maxChars + 1 | PASS |
| testTruncatedLengthIsMaxChars | PASS |
| testNameOneOverLimitTruncated | PASS |
| testCyrillicMultibyteHandled: многоточие | PASS |
| testCyrillicMultibyteHandled: длина обрезки | PASS |
| testPrint2ViewUsesMbStrlen | PASS |
| testPrint2ViewUsesMbSubstr | PASS |
| testPrint2ViewHasCorrectMaxChars | PASS |
| testPrint2ViewAddsEllipsis | PASS |
| testPrint2ViewUsesHtmlEncode | PASS |

**AC верифицированы:**
- AC-1: длинное название обрезается до 28 символов + «…» — PASS (TC-24-001)
