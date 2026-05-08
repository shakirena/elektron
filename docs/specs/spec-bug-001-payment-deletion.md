# Spec: Bug #1 — Массовое удаление оплат долгов клиентов

**Issue:** #1
**Тип:** type:bug
**Приоритет:** priority:critical
**Статус:** ready-for-dev
**Дата:** 2026-05-08

---

## Контекст

Yii2 Basic Application (Elektron). При отмене продажи метод `actionCancel` вызывает `Dclient::deleteAll(['number' => $number])`. Если `$number = null`, Yii2 генерирует `DELETE FROM dclient WHERE number IS NULL` — удаляет ВСЕ оплаты долгов с `number = NULL`. Все оплаты из `actionReceivedDebt` сохраняются с `number = NULL`, что делает их мишенью.

---

## Story 1 — Защита actionCancel от null number (Issue #2)

### Описание

Добавить валидацию параметра `$number` в начале `SellController::actionCancel`. Если параметр невалиден — вернуть HTTP 400, не выполнять DELETE.

### Functional Requirements

- FR-1.1: Метод `actionCancel` должен проверять `$number` на null, пустую строку и нечисловое значение
- FR-1.2: При невалидном `$number` метод выбрасывает `\yii\web\BadRequestHttpException`
- FR-1.3: При невалидном `$number` ни одна строка в таблицах `dclient`, `costs` не удаляется

### Non-Functional Requirements

- NFR-1.1: Валидация выполняется до любых DB-операций (fail-fast)
- NFR-1.2: Исключение логируется стандартным Yii2 error handler

### Acceptance Criteria (SD-1)

**Given** HTTP-запрос к /sell/cancel приходит без параметра number (или number=null, number=0, number='abc')
**When** `actionCancel` начинает выполнение
**Then** метод выбрасывает `BadRequestHttpException` (HTTP 400) и не выполняет ни одного запроса DELETE к таблице dclient

### Тест-сценарии

**Happy Path:**
- Вызов `/sell/cancel?number=42` с существующей продажей → продажа отменяется, связанные dclient-записи с number=42 удаляются корректно

**Error Cases:**
- Вызов `/sell/cancel` без параметра → HTTP 400, dclient не тронут
- Вызов `/sell/cancel?number=0` → HTTP 400, dclient не тронут
- Вызов `/sell/cancel?number=abc` → HTTP 400, dclient не тронут
- Вызов `/sell/cancel?number=` (пустая строка) → HTTP 400, dclient не тронут

---

## Story 2 — Сохранение оплаты с корректным number в actionReceivedDebt (Issue #3)

### Описание

В методе `SellController::actionReceivedDebt` поле `number` нового Dclient не устанавливается, запись сохраняется с `number = NULL`. Необходимо заполнять `number` номером продажи клиента.

### Functional Requirements

- FR-2.1: При создании записи оплаты (Dclient) поле `number` должно быть заполнено номером продажи
- FR-2.2: Если продажа клиента не найдена — использовать sentinel-значение 0 (не NULL)
- FR-2.3: Поле `number` никогда не должно сохраняться как NULL

### Non-Functional Requirements

- NFR-2.1: Запрос к Sell выполняется один раз (не дублировать строку 1840)
- NFR-2.2: Изменение не затрагивает логику расчёта долга

### Acceptance Criteria (SD-1)

**Given** кассир вызывает actionReceivedDebt с валидными параметрами id клиента и sum
**When** система сохраняет запись оплаты в таблицу dclient
**Then** сохранённая запись имеет поле number равное номеру последней продажи клиента (целое число, не NULL)

### Тест-сценарии

**Happy Path:**
- Клиент имеет продажу number=15, вызов actionReceivedDebt → новая dclient-запись имеет number=15

**Error Cases:**
- Клиент не имеет ни одной продажи → новая dclient-запись имеет number=0 (не NULL)
- Sell::find() возвращает null → fallback на 0, сохранение не прерывается

---

## Story 3 — Null-safe удаление dclient в CostsController::actionDelete (Issue #4)

### Описание

В `CostsController::actionDelete` отсутствует проверка на null перед вызовом `$dclient->delete()`. Если запись в dclient не найдена — PHP Fatal Error.

### Functional Requirements

- FR-3.1: Перед вызовом `$dclient->delete()` проверять, что `$dclient !== null`
- FR-3.2: Если запись не найдена — продолжать выполнение (redirect) без ошибки
- FR-3.3: Аналогичная защита для `Debt::find()` в том же методе

### Non-Functional Requirements

- NFR-3.1: Минимальный патч без изменения бизнес-логики
- NFR-3.2: Нет изменений в структуре DB

### Acceptance Criteria (SD-1)

**Given** администратор удаляет расход (Costs) с id_type=1, у которого связанная запись dclient уже отсутствует в БД
**When** метод actionDelete выполняет `Dclient::find()->where(['id' => $model->fid])->one()`
**Then** метод завершается redirect без PHP Fatal Error "Call to a member function delete() on null"

### Тест-сценарии

**Happy Path:**
- Расход с id_type=1 и существующим dclient.id → dclient удаляется, redirect на transfer

**Error Cases:**
- Расход с id_type=1, dclient не найден → redirect на transfer, Fatal Error отсутствует
- Расход с id_type=2, Debt не найден → redirect на transfer, Fatal Error отсутствует

---

## Вне Scope

- Рефакторинг системы оплат целиком
- Изменение схемы таблицы dclient (добавление NOT NULL constraint)
- Восстановление уже удалённых данных
- Изменение логики расчёта балансов
