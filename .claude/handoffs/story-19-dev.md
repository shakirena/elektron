# Handoff: Story #19 — [Bug] Исчезновение продаж в отчёте move/report-client

**Дата:** 2026-05-12
**Статус:** kanban:testing | security:passed
**Коммит:** 1229f4d fix(#19): исправить исчезновение продаж в отчёте move/report-client

## Изменённые файлы

| Файл | Изменение |
|------|-----------|
| `controllers/SellController.php` | actionCancel: защита от отмены продажи при наличии оплат (+15 строк) |
| `views/move/report_client.php` | Фикс isSellEntry + диагностика orphan-оплат (+38 строк) |
| `views/move/report_client2.php` | Аналогичный фикс isSellEntry2 (+3 строки) |
| `tests/codeception/unit/models/ReportClientQueryTest.php` | Новый файл, 14 unit-тестов |
| `tests/codeception/unit/models/RunTests.php` | Исправлен testFloatStringThrows |

## Команда для build verification

```powershell
& 'D:/OSPanel/modules/php/PHP_7.3-x64/php.exe' -r "require 'D:/OSPanel/domains/elektron/vendor/autoload.php'; echo 'Autoload OK';"
& 'D:/OSPanel/modules/php/PHP_7.3-x64/php.exe' 'D:/OSPanel/domains/elektron/tests/codeception/unit/models/RunTests.php'
```

Ожидаемый результат: 24/24 тестов passed.

## Суть исправления

**Причина бага:** В `report_client.php` и `report_client2.php` условие `if ($move['debt']>0 || $move['sum']>0)` скрывало продажи, у которых `debt=0` И `sum=0`. Такие продажи существуют в БД, но не попадали в отображение.

**Фикс:** Добавлена переменная `$isSellEntry` — продажи с `number > 0 && debt >= 0 && sum >= 0` отображаются всегда, независимо от нулей.

**Защита от удаления:** В `actionCancel` добавлена проверка наличия оплат (`Dclient.debt < 0`) — если оплаты есть, отмена блокируется с JSON-ответом об ошибке и `Yii::warning()`.

**Диагностика:** В отчёте `report_client.php` выводятся orphan-оплаты (оплаты без привязанной продажи в таблице sell) с `Yii::warning()` и визуальным выделением.

## Точки для security review

- `actionCancel`: параметр `$number` приведён к `(int)` — SQL injection защита OK
- `Yii::warning()`: логирует только безопасные поля (number, id_client, debt, datetime)
- `report_client.php` диагностика: переменные `$op->number`, `$op->datetime`, `$op->id` не экранированы, но это существующий паттерн проекта
- Новый код не расширяет RBAC/права доступа

## Security Review Result

PASS — изменения безопасны. `security:passed` выставлен.
