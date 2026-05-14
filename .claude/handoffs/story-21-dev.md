# Handoff: Story #21 — Dev Complete

**Story:** #21 Сохранение корректного number в actionReceivedDebt
**Branch:** master (коммит 46b5c7e)
**Дата:** 2026-05-08

## Изменённые файлы

- `controllers/SellController.php` — метод `actionReceivedDebt` (строка 1841)

## Команда build verification

```bash
/d/OSPanel/modules/php/PHP_7.3-x64/php.exe -l controllers/SellController.php
# Ожидается: No syntax errors detected
```

## Точки для security review

1. **SQL запрос через ORM:** `Dclient::find()->where(["id_client" => $id])->andWhere(['>', 'debt', 0])->one()` — параметризованный запрос, безопасен
2. **Параметры метода:** `$id, $sum, $note, $date, $kassa` — проверить на XSS/injection при использовании в DB-записи
3. **Sentinel value 0:** не NULL — проверить что 0 не является валидным номером продажи (автоинкремент начинается с 1)
4. **Сохранение:** `$dclient->save()` — Yii2 Active Record выполняет параметризованный INSERT

## Тест-файл

`tests/codeception/unit/models/ActionReceivedDebtNumberTest.php`
