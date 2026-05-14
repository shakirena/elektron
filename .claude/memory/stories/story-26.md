# Story #26: Корректное отображение коротких названий товаров без обрезки в print2.php

**Feature:** #24 — Обрезка длинных названий товаров при печати чека 40x20мм
**Branch:** feature/24-barcode-print2-truncate
**Status:** ready-to-deploy

---

## Задача

Как оператор склада, я хочу чтобы короткие названия товаров (до 28 символов) отображались полностью при печати этикетки 40x20мм, чтобы многоточие не добавлялось к названиям, которые уже умещаются в этикетку.

**AC (Given/When/Then):**
```gherkin
Given в базе есть товар с названием не длиннее 28 символов (например, «Кабель HDMI 1м»)
When оператор открывает http://elektron/web/barcode/print2 для печати
Then название товара отображается полностью без добавления «…»
```

---

## Архитектура

- Файл изменений: `views/barcode/print2.php` — та же логика что и Story #25
- Условие: `if (mb_strlen($name, 'UTF-8') <= 28)` — отображать без обрезки
- Покрывается теми же unit-тестами в `Print2TruncateTest` (метод `testShortNameUnchanged`, `testExactlyMaxCharsUnchanged`)

---

## Реализация

- Реализована в той же логике что и Story #25 (elseif/else ветки)
- Unit-тест: `tests/codeception/unit/models/Print2TruncateTest.php` — методы testShortNameUnchanged, testExactlyMaxCharsUnchanged

---

## Security Review

**Verdict:** PASS
**Label:** security:passed выставлен dev-lead

---

## QA

**Verdict:** PASS
**Coverage:** 100% (3/3 теста для Story #26)
**Тест-раннер:** tests/run_print2_tests.php (standalone, без Codeception)
**TC-документ:** docs/test-cases/feature-24-barcode-print2-truncate.md
**Gate G5:** PASS 2026-05-14

### Результаты тестов (Story #26)

| Тест | Статус |
|------|--------|
| testShortNameUnchanged | PASS |
| testExactlyMaxCharsUnchanged | PASS |
| testEmptyNameUnchanged | PASS |

**AC верифицированы:**
- AC-1: короткое название ≤ 28 символов отображается полностью без «…» — PASS (TC-24-007)
