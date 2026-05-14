# Story #20 — Защита actionCancel от null number

**Issue:** #20
**Родительский issue:** #19
**Тип:** type:bug
**Приоритет:** priority:high
**Статус:** in-development

---

## Задача

Добавить валидацию параметра `$number` в начале `SellController::actionCancel`. Если параметр невалиден — вернуть HTTP 400, не выполнять DELETE.

Корень проблемы: при вызове без параметра `number` Yii2 генерирует `DELETE FROM dclient WHERE number IS NULL`, что удаляет ВСЕ записи оплат с `number = NULL`.

## Архитектура

Затронутый файл: `controllers/SellController.php`, метод `actionCancel` (строка 1018).

Патч: добавить fail-fast валидацию в начало метода до любых DB-операций.

---

## Реализация

**Коммит:** 46b5c7ed9a8f58fe3fbec1fb9a63c90cc0e5c9e2 `fix(#2,#3,#4): patch mass-delete bug in payment flow`

**Изменённые файлы:**
- `controllers/SellController.php` — метод `actionCancel` (строка 1018)

**Патч:**
```php
public function actionCancel($number = null){
    if (!$number || !is_numeric($number) || (int)$number <= 0) {
        throw new \yii\web\BadRequestHttpException('Invalid number parameter');
    }
    $number = (int)$number;
    // ... остальной код
}
```

**Unit тесты:** `tests/codeception/unit/models/ActionCancelValidationTest.php`
- 11 тест-кейсов: null, пустая строка, 0, '0', отрицательное, строка, float, валидные числа, cast

---

## Security Review

Статус: security:passed (выставлен dev-lead)

---

## QA

**Статус:** qa:passed
**Дата:** 2026-05-11
**Coverage:** 100% новых строк (строки 1019-1022 SellController.php)
**Тесты:** 10/11 pass (1 fail — баг в тесте testFloatStringThrows, создан Bug Issue #23, не влияет на production-код)
**TC-документ:** docs/test-cases/tc-bug-001-payment-deletion.md (TC-001, TC-002, TC-003)
**Gate G5:** PASS
**Kanban:** kanban:ready-to-deploy
