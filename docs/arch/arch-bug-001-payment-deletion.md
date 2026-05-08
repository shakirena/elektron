# Architecture: Bug #1 — Массовое удаление оплат долгов клиентов

**Issue:** #1
**Тип:** type:bug — минимальные патчи, без рефакторинга
**Дата:** 2026-05-08

---

## Затронутые компоненты

```
controllers/SellController.php     — actionCancel (патч 1), actionReceivedDebt (патч 2)
controllers/CostsController.php    — actionDelete (патч 3)
models/Dclient.php                 — только чтение, изменений нет
```

---

## Патч 1: SellController::actionCancel (Story #2)

### Проблема

```php
// БЫЛО: строка 1018
public function actionCancel($number) {
    // $number может быть null если URL: /sell/cancel (без параметра)
    // ... много кода ...
    $barcode->deleteAll(['number' => $number]);
    // Если $number=null -> DELETE FROM dclient WHERE number IS NULL -> КАТАСТРОФА
}
```

### Решение (diff-style)

```php
// СТАЛО: добавить в начало actionCancel ПЕРЕД любым кодом
public function actionCancel($number = null) {
+   if (empty($number) || !ctype_digit((string)$number) || (int)$number <= 0) {
+       throw new \yii\web\BadRequestHttpException('Параметр number обязателен и должен быть положительным целым числом.');
+   }
+   $number = (int)$number;

    foreach (Sell::find()->where(['number' => $number])->all() as $row) {
        // ... существующий код без изменений ...
    }

    $barcode = new Dclient();
    $barcode->deleteAll(['number' => $number]);  // безопасно: $number гарантированно > 0
    // ...
}
```

### Почему ctype_digit

- `empty(0)` = true — поэтому дополнительная проверка `(int)$number <= 0`
- `ctype_digit` отклоняет дробные числа, строки, спецсимволы
- Cast в int гарантирует тип для `deleteAll`

### ADR-1

Выбор: throw exception vs return redirect.
Решение: throw `BadRequestHttpException` (HTTP 400) — это ошибка клиента (неверный запрос), не серверная. Yii2 обрабатывает это стандартно через ErrorHandler, логирует, возвращает JSON/HTML в зависимости от Accept-header.
Отклонено: redirect — скрывает ошибку, не сообщает вызывающей стороне о проблеме.

---

## Патч 2: SellController::actionReceivedDebt (Story #3)

### Проблема

```php
// БЫЛО: строки 1837-1848
public function actionReceivedDebt($id, $sum, $note, $date, $kassa) {
    $sum2 = $sum;
    $dclient = Dclient::find()->where(["id_client" => $id])->one();
    $sell = Sell::find()->where(['number' => $dclient->number])->one()->id_user;

    $dclient = new Dclient();    // ПЕРЕЗАПИСЫВАЕТ $dclient выше
    $dclient->debt = -$sum;
    $dclient->id_client = $id;
    $dclient->note = $note;
    $dclient->datetime = $date . date(" H:i:s");
    // $dclient->number НЕ ЗАДАЁТСЯ -> NULL -> уязвимость
    $dclient->save();
}
```

### Решение (diff-style)

```php
// СТАЛО:
public function actionReceivedDebt($id, $sum, $note, $date, $kassa) {
    $sum2 = $sum;
    $existingDclient = Dclient::find()->where(["id_client" => $id])->one();
+   $saleNumber = ($existingDclient !== null && $existingDclient->number !== null)
+       ? (int)$existingDclient->number
+       : 0;

    // Строка $sell была нужна только для id_user — переменная не используется далее
    // (оставить как есть или убрать — вне scope данного патча)

    $dclient = new Dclient();
    $dclient->debt = -$sum;
    $dclient->id_client = $id;
+   $dclient->number = $saleNumber;   // ДОБАВЛЕНО: никогда не NULL
    $dclient->note = $note;
    $dclient->datetime = $date . date(" H:i:s");
    $dclient->save();
    // ...
}
```

### Почему sentinel 0 а не NULL

- NULL в `number` — корень проблемы (баг #1)
- 0 никогда не является реальным номером продажи (автоинкремент начинается с 1)
- `deleteAll(['number' => 0])` удалит только записи с number=0, не все NULL-записи
- Альтернатива (DB migration: NOT NULL DEFAULT 0) — вне scope

### ADR-2

Выбран минимальный патч: добавление одной строки `$dclient->number = $saleNumber`.
Отклонено: рефакторинг с выделением SalePaymentService — слишком большой scope для критического хотфикса.

---

## Патч 3: CostsController::actionDelete (Story #4)

### Проблема

```php
// БЫЛО: строки 242-267
public function actionDelete($id) {
    $model = $this->findModel($id);
    if ($model->id_type == 1) {
        if ($model->fid) {
            $dclient = Dclient::find()->where(["id" => $model->fid])->one();
            $dclient->delete();   // Fatal Error если $dclient = null
        } else {
            return $this->redirect(['index']);
        }
    }
    if ($model->id_type == 2) {
        if ($model->fid) {
            $dclient = Debt::find()->where(["id" => $model->fid])->one();
            $dclient->delete();   // Fatal Error если $dclient = null
        } else {
            return $this->redirect(['index']);
        }
    }
    $this->findModel($id)->delete();
    return $this->redirect(['transfer']);
}
```

### Решение (diff-style)

```php
// СТАЛО:
public function actionDelete($id) {
    $model = $this->findModel($id);
    if ($model->id_type == 1) {
        if ($model->fid) {
            $dclient = Dclient::find()->where(["id" => $model->fid])->one();
+           if ($dclient !== null) {
                $dclient->delete();
+           }
        } else {
            return $this->redirect(['index']);
        }
    }
    if ($model->id_type == 2) {
        if ($model->fid) {
            $dclient = Debt::find()->where(["id" => $model->fid])->one();
+           if ($dclient !== null) {
                $dclient->delete();
+           }
        } else {
            return $this->redirect(['index']);
        }
    }
    $this->findModel($id)->delete();
    return $this->redirect(['transfer']);
}
```

### ADR-3

Null-check добавлен молча (без throw). Причина: удаление уже-удалённой записи — idempotent операция, не ошибка клиента. Альтернатива (Yii2 findOne + 404) — меняет поведение существующих пользователей.

---

## Sequence Diagram: actionCancel (после патча)

```
Client                SellController          DB
  |                        |                   |
  |-- GET /sell/cancel --->|                   |
  |   ?number=null         |                   |
  |                        |-- validate ------->X (fail-fast)
  |<-- HTTP 400 -----------|                   |
  |                        |                   |

  |-- GET /sell/cancel --->|                   |
  |   ?number=42           |                   |
  |                        |-- validate ------->OK
  |                        |-- SELECT Sell ---->|
  |                        |<-- rows -----------|
  |                        |-- DELETE dclient ->|  (WHERE number=42)
  |                        |<-- OK -------------|
  |<-- HTTP 200 -----------|                   |
```

---

## Затронутые таблицы (только чтение схемы, без ALTER)

| Таблица | Поле | Тип | Изменение |
|---------|------|-----|-----------|
| dclient | number | int(11) NULL | НЕТ (патч не меняет схему) |
| dclient | number | int(11) NULL | Данные: новые записи будут иметь 0 вместо NULL |

---

## Code Stubs

Данные патчи — точечные правки существующих методов. Новые классы, интерфейсы и файлы не создаются. Стабы не требуются.
