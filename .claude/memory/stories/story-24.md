# Feature #24 Memory

**Название:** Обрезка длинных названий товаров при печати чека 40x20мм
**URL:** https://github.com/shakirena/elektron/issues/24
**Статус:** kanban:ready-for-dev
**Дата анализа:** 2026-05-14

---

## Раздел: User Stories (analyst)

| Story | Title | Size | Status |
|-------|-------|------|--------|
| #25 | Story: Обрезка длинного названия товара в print2.php с добавлением многоточия | S | kanban:ready-for-dev |
| #26 | Story: Корректное отображение коротких названий товаров без обрезки в print2.php | S | kanban:ready-for-dev |

**Acceptance Criteria:**
- AC-1: Название обрезается до MAX_CHARS (28 символов) + многоточие при длине > 28
- AC-2: Оригинальное название в БД не изменяется
- AC-3: Добавляется «…» при обрезке
- AC-4: Только шаблон print2.php, не экранное отображение
- AC-5: Короткие названия (≤ 28) без изменений

---

## Раздел: Architecture (architect)

**Scope изменений:** только `views/barcode/print2.php` (строки 52-55)

**Константы:**
- MAX_CHARS = 28 символов

**Ключевые решения:**
- ADR-001: обрезка в View, не в Model/Service
- ADR-002: mb_strlen/mb_substr для поддержки кириллицы (UTF-8)
- ADR-003: MAX_CHARS = 28 (расчёт: ~36мм / 1.35мм на символ)

**Затронутые файлы:**
- MODIFY: `views/barcode/print2.php` (строки 52-55)
- CREATE: `tests/unit/services/Print2TruncateTest.php` (6 unit-тестов)

**Документация:**
- Spec: `docs/specs/feature-24-barcode-print2-truncate.md`
- Arch: `docs/arch/feature-24-barcode-print2-truncate.md`

---

## Контекст кодовой базы

- Контроллер: `BarcodeController::actionPrint2()` — загружает Barcode модели с joinWith idProduct
- Шаблон: `views/barcode/print2.php` — текущая логика: `strlen > 30 ? '9px' : '12px'`
- Модель Barcode: таблица `barcode` (id, id_product, count, price)
- Модель Barcodep: таблица `bar_code` (id, id_product, name = штрих-код)
- Модель Product: таблица product, поле `name` — не изменяется
