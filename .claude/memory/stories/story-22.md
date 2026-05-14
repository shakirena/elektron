# Story #22 — Null-safe удаление в CostsController::actionDelete

**Issue:** #22
**Родительский issue:** #19
**Тип:** type:bug
**Приоритет:** priority:high
**Статус:** in-development

---

## Задача

В `CostsController::actionDelete` отсутствует проверка на null перед вызовом `$dclient->delete()`. Если запись в dclient не найдена — PHP Fatal Error.

## Архитектура

Затронутый файл: `controllers/CostsController.php`, метод `actionDelete` (строка 242).

Патч: добавить null-check перед `->delete()` для обоих блоков (id_type==1 с Dclient и id_type==2 с Debt).

---

## Реализация

**Коммит:** 46b5c7ed9a8f58fe3fbec1fb9a63c90cc0e5c9e2 `fix(#2,#3,#4): patch mass-delete bug in payment flow`

**Изменённые файлы:**
- `controllers/CostsController.php` — метод `actionDelete` (строки 242-271)

**Патч (id_type==1):**
```php
$dclient = Dclient::find()->where(["id" => $model->fid])->one();
if ($dclient !== null) {
    $dclient->delete();
}
```

**Патч (id_type==2):**
```php
$debt = Debt::find()->where(["id" => $model->fid])->one();
if ($debt !== null) {
    $debt->delete();
}
```

**Unit тесты:** `tests/codeception/unit/models/CostsActionDeleteNullSafeTest.php`
- 7 тест-кейсов: dclient найден (delete вызван), dclient null (delete пропущен), нет Fatal Error, то же для Debt, переменные отдельные

---

## Security Review

Статус: security:passed (выставлен dev-lead)

---

## QA

**Статус:** qa:passed
**Дата:** 2026-05-11
**Coverage:** 100% новых строк (строки 249-264 CostsController.php)
**Тесты:** 7/7 pass
**TC-документ:** docs/test-cases/tc-bug-001-payment-deletion.md (TC-005, TC-006)
**Gate G5:** PASS
**Kanban:** kanban:ready-to-deploy
