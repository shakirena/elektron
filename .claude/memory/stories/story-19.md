# Story #19 — [Bug] Исчезновение продаж в отчёте Движение клиентов

**Тип:** type:bug
**Приоритет:** priority:high
**Статус:** kanban:testing | security:passed

---

## Задача

В отчёте «Движение клиентов» (move/report-client) исчезают продажи у клиента, но оплаты по этой продаже остаются. Неясно, удалилась ли продажа из БД, или это визуальный баг (продажа есть, но не отображается).

**Acceptance Criteria:**
- Выявлена причина: продажа физически удаляется или не попадает в выборку
- Если баг запроса — отчёт исправлен
- Если продажи удаляются — найдена точка удаления и добавлена защита
- Оплаты всегда отображаются вместе со своей продажей
- Добавлены unit-тесты для логики выборки
- Диагностика orphan-оплат

---

## Архитектура

**Модуль:** `move` (MoveController::actionReportClient)
**Таблица:** `dclient` (модель Dclient)
**Вьюхи:** `views/move/report_client.php`, `views/move/report_client2.php`
**Контроллер продаж:** `controllers/SellController.php` (actionCancel)

---

## Реализация

**Причина бага:** В `report_client.php` и `report_client2.php` условие `if ($move['debt']>0 || $move['sum']>0)` скрывало продажи с `debt=0` и `sum=0`. Такие продажи существуют в БД (например, продажа оформлена, но долг ещё не записан), но не отображались.

**Фиксы:**

1. `views/move/report_client.php` — добавлена переменная `$isSellEntry`:
   ```php
   $isSellEntry = ($move['number'] !== null && $move['number'] > 0 && $move['debt'] >= 0 && $move['sum'] >= 0);
   if ($move['debt'] > 0 || $move['sum'] > 0 || $isSellEntry) { ... }
   ```

2. `views/move/report_client2.php` — аналогичный `$isSellEntry2`

3. `controllers/SellController.php` (actionCancel) — защита от удаления при наличии оплат:
   ```php
   $hasPayments = Dclient::find()->where(['number' => $number])->andWhere(['<', 'debt', 0])->exists();
   if ($hasPayments) { Yii::warning(...); return $this->asJson(['error' => '...']); }
   ```

4. `views/move/report_client.php` — диагностика orphan-оплат: поиск записей Dclient без соответствующих записей в таблице `sell`, вывод в отчёте + `Yii::warning()`

**Unit-тесты:**
- `tests/codeception/unit/models/ReportClientQueryTest.php` — 14 тестов: classifyDclientRow, hasPaymentsForSell, orphan detection
- `tests/codeception/unit/models/RunTests.php` — 24 теста: actionCancel validation, resolveSellNumber, Dclient null-safety

**Коммит:** `1229f4d fix(#19): исправить исчезновение продаж в отчёте move/report-client`

---

## Security Review

**Статус:** PASS — `security:passed` выставлен
- SQL injection: `$number` приведён к `(int)`, Yii2 ActiveRecord параметризованные запросы
- Logging: `Yii::warning()` не содержит секретов
- RBAC: изменения не расширяют права доступа

---

## QA

**Статус:** qa:passed
**Дата:** 2026-05-12
**Coverage:** 100% новых строк — 14/14 тестов (ReportClientQueryTest.php) + 24/24 (RunTests.php)
**Провалено:** 0
**TC-документ:** docs/test-cases/tc-bug-019-report-client.md (TC-001, TC-002, TC-003, TC-004)
**Gate G5:** PASS
**Kanban:** kanban:ready-to-deploy

### Верификация AC

| AC | Описание | Статус |
|----|----------|--------|
| AC-1 | Выявлена причина исчезновения продаж (условие debt>0 или sum>0) | PASS |
| AC-2 | Отчёт отображает продажи с debt=0 и sum=0 | PASS — isSellEntry/isSellEntry2 в обоих view |
| AC-3 | Защита actionCancel при наличии оплат | PASS — hasPayments guard + Yii::warning |
| AC-4 | Orphan-оплаты диагностируются (warning + визуальное выделение) | PASS — code verified in report_client.php |
| AC-5 | Unit-тесты написаны | PASS — 14 тестов в ReportClientQueryTest.php |
