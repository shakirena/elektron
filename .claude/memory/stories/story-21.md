# Story #21 — Сохранение корректного number в actionReceivedDebt

**Issue:** #21
**Родительский issue:** #19
**Тип:** type:bug
**Приоритет:** priority:high
**Статус:** in-development

---

## Задача

В методе `SellController::actionReceivedDebt` поле `number` нового Dclient не устанавливается, запись сохраняется с `number = NULL`. Необходимо заполнять `number` номером продажи клиента.

## Архитектура

Затронутый файл: `controllers/SellController.php`, метод `actionReceivedDebt` (строка 1841).

Патч: перед созданием нового Dclient определить `$sellNumber` из существующей записи (или sentinel 0), присвоить `$dclient->number`.

---

## Реализация

**Коммит:** 46b5c7ed9a8f58fe3fbec1fb9a63c90cc0e5c9e2 `fix(#2,#3,#4): patch mass-delete bug in payment flow`

**Изменённые файлы:**
- `controllers/SellController.php` — метод `actionReceivedDebt` (строка 1841)

**Патч:**
```php
$existingDclient = Dclient::find()->where(["id_client" => $id])->andWhere(['>', 'debt', 0])->one();
$sellNumber = ($existingDclient !== null && $existingDclient->number !== null)
    ? (int)$existingDclient->number
    : 0;

$dclient = new Dclient();
$dclient->debt = -$sum;
$dclient->id_client = $id;
$dclient->number = $sellNumber;  // ДОБАВЛЕНО: никогда не NULL
// ...
```

**Unit тесты:** `tests/codeception/unit/models/ActionReceivedDebtNumberTest.php`
- 6 тест-кейсов: существующий dclient с number, null number, нет dclient, результат не NULL, sentinel >= 0

---

## Security Review

Статус: security:passed (выставлен dev-lead)

---

## QA

**Статус:** qa:passed
**Дата:** 2026-05-11
**Coverage:** 100% новых строк (строки 1843-1850 SellController.php)
**Тесты:** 6/6 pass
**TC-документ:** docs/test-cases/tc-bug-001-payment-deletion.md (TC-004)
**Gate G5:** PASS
**Kanban:** kanban:ready-to-deploy
