# Architecture: Feature #24 — Обрезка длинных названий товаров при печати чека 40x20мм

*Создано: 2026-05-14*
*Статус: analysis*
*Issue: https://github.com/shakirena/elektron/issues/24*

---

## Обзор архитектурного решения

Изменение строго ограничено слоем View — только файл `views/barcode/print2.php`. Контроллер, модели и БД не затрагиваются. Это минимально инвазивное решение.

---

## ERD (затронутые сущности)

```
Таблица: product
  id          INT PK
  name        VARCHAR(255)   <- читается, не изменяется
  ...

Таблица: barcode
  id          INT PK
  id_product  INT FK -> product.id
  count       INT
  price       INT

Таблица: bar_code (Barcodep)
  id          INT PK
  id_product  INT FK -> product.id
  name        VARCHAR(50)    <- штрих-код
```

Нет изменений схемы. ERD приводится для контекста.

---

## Компонентная карта

```
BarcodeController::actionPrint2()
    └── Barcode::find()->where(['price'=>0])->joinWith('idProduct')->all()
    └── $this->render('print2', ['model' => $model])
            └── views/barcode/print2.php  <-- ЕДИНСТВЕННЫЙ ИЗМЕНЯЕМЫЙ ФАЙЛ
                    ├── foreach $model as $res
                    │     ├── Barcodep::find()->where(['id_product'=>$res->id_product])->one()
                    │     ├── Arrival::find()->orderBy('id DESC')->one()->pricesell
                    │     └── BarcodeGenerator::widget(...)
                    │     └── echo <div>
                    │           └── <span> $displayName </span>  <-- ИЗМЕНЕНИЕ ЗДЕСЬ
                    └── window.print() JS
```

---

## API Contracts

Нет новых API. Изменение чисто на уровне rendering.

**Вход (данные из модели):**
```
$res->idProduct->name : string  — оригинальное название товара из таблицы product
```

**Выход (рендеринг):**
```
$displayName : string
  = name                                         если mb_strlen(name) <= MAX_CHARS
  = mb_substr(name, 0, MAX_CHARS, 'UTF-8') + '…' если mb_strlen(name) > MAX_CHARS
```

**Константа:**
```php
$maxChars = 28;  // символов, умещается в 40мм при font-size: 9px
```

---

## ADR-001: Обрезка в View, не в Model/Service

**Контекст:** нужно ограничить длину отображения в print2.php.

**Решение:** обрезка делается непосредственно в PHP-шаблоне `views/barcode/print2.php`.

**Альтернативы рассмотрены:**
1. Helper-функция `StringHelper::truncateForPrint2($name, $max)` в отдельном файле — избыточно для одного места использования
2. Новый метод в модели `Product::getDisplayNameForPrint()` — нарушает принцип SRP (модель не должна знать о формате печати)
3. View-only inline переменная — выбрано как наиболее простое и локализованное решение

**Статус:** ACCEPTED

---

## ADR-002: mb_strlen/mb_substr для поддержки кириллицы

**Контекст:** названия товаров содержат кириллические символы (многобайтовые в UTF-8). `strlen()` считает байты, не символы.

**Решение:** использовать `mb_strlen($name, 'UTF-8')` и `mb_substr($name, 0, $maxChars, 'UTF-8')`.

**Обоснование:** `mbstring` является стандартным расширением PHP 7.4+, всегда доступен в OSPanel.

**Статус:** ACCEPTED

---

## ADR-003: MAX_CHARS = 28

**Контекст:** нужно подобрать максимальное количество символов для шрифта 9px на этикетке 40x20мм.

**Расчёт:**
- Физическая ширина печатаемой области: ~36мм (отступы ~2мм с каждой стороны)
- При font-size 9px, средняя ширина кириллического символа: ~5-6px при 96dpi → ~1.35мм
- 36мм / 1.35мм ≈ 26-28 символов
- Выбрано 28 как верхняя граница (производительнее для типичных названий)

**Статус:** ACCEPTED

---

## Code Stubs

### Stub: views/barcode/print2.php (изменяемый участок)

Файл `views/barcode/print2.php`, строки 52-55 (текущий код):
```php
if (strlen($res->idProduct->name)>30) $size='9px';
else $size='12px';
echo "<div align='center' style='margin-bottom:16px'>".BarcodeGenerator::widget($optionsArray)."<span  style='font-size:$size !important'>".$res->idProduct->name."</span></span><br><span  style='font-size:15pt !important'><b>$pricesell AZN</b></span><br></div>";
```

После замены (stub):
```php
// --- Feature #24: обрезка длинных названий для этикетки 40x20мм ---
$productName = $res->idProduct->name;
$maxChars = 28;
if (mb_strlen($productName, 'UTF-8') > $maxChars) {
    $displayName = mb_substr($productName, 0, $maxChars, 'UTF-8') . '…';
    $size = '9px';
} elseif (strlen($productName) > 30) {
    $displayName = $productName;
    $size = '9px';
} else {
    $displayName = $productName;
    $size = '12px';
}
// --- конец Feature #24 ---
echo "<div align='center' style='margin-bottom:16px'>"
    . BarcodeGenerator::widget($optionsArray)
    . "<span style='font-size:" . $size . " !important'>" . Html::encode($displayName) . "</span>"
    . "<br><span style='font-size:15pt !important'><b>" . $pricesell . " AZN</b></span><br>"
    . "</div>";
```

### Stub: tests/unit/services/Print2TruncateTest.php (unit test)

```php
<?php

namespace tests\unit\services;

use Codeception\Test\Unit;

/**
 * Unit-тесты для логики обрезки названий товаров в print2 шаблоне
 * Feature #24: Обрезка длинных названий при печати чека 40x20мм
 */
class Print2TruncateTest extends Unit
{
    private int $maxChars = 28;

    /**
     * Хелпер-функция, имитирующая логику шаблона print2.php
     */
    private function truncateForPrint2(string $name): string
    {
        if (mb_strlen($name, 'UTF-8') > $this->maxChars) {
            return mb_substr($name, 0, $this->maxChars, 'UTF-8') . '…';
        }
        return $name;
    }

    public function testShortNameUnchanged(): void
    {
        $name = 'Кабель HDMI 1м';
        $this->assertEquals($name, $this->truncateForPrint2($name));
    }

    public function testExactlyMaxCharsUnchanged(): void
    {
        $name = str_repeat('А', $this->maxChars); // ровно 28 символов
        $this->assertEquals($name, $this->truncateForPrint2($name));
    }

    public function testLongNameTruncated(): void
    {
        $name = 'Кабель USB Type-C 1.5м нейлон чёрный';
        $result = $this->truncateForPrint2($name);
        $this->assertLessThanOrEqual($this->maxChars + 1, mb_strlen($result, 'UTF-8')); // +1 за "…"
        $this->assertStringEndsWith('…', $result);
    }

    public function testTruncatedLengthIsMaxChars(): void
    {
        $name = str_repeat('Б', 50);
        $result = $this->truncateForPrint2($name);
        $withoutEllipsis = mb_substr($result, 0, -1, 'UTF-8');
        $this->assertEquals($this->maxChars, mb_strlen($withoutEllipsis, 'UTF-8'));
    }

    public function testEmptyNameUnchanged(): void
    {
        $this->assertEquals('', $this->truncateForPrint2(''));
    }

    public function testCyrillicMultibyteHandled(): void
    {
        // "Кириллица" — 9 символов, каждый 2 байта в UTF-8
        $name = str_repeat('Ж', 35); // 35 кириллических символов
        $result = $this->truncateForPrint2($name);
        $this->assertStringEndsWith('…', $result);
        $this->assertEquals($this->maxChars, mb_strlen(mb_substr($result, 0, -1, 'UTF-8'), 'UTF-8'));
    }
}
```

---

## Затронутые файлы

| Файл | Изменение | Тип |
|------|-----------|-----|
| `views/barcode/print2.php` | Заменить строки 52-55 на stub с mb_strlen/mb_substr | MODIFY |
| `tests/unit/services/Print2TruncateTest.php` | Создать unit-тесты | CREATE |

**Не затрагиваются:**
- `controllers/BarcodeController.php`
- `models/Barcode.php`, `models/Barcodep.php`, `models/Product.php`
- Миграции
- Все остальные шаблоны barcode/

---

## Риски

| Риск | Вероятность | Митигация |
|------|------------|-----------|
| MAX_CHARS слишком мало для конкретного принтера | Низкая | Константу `$maxChars` легко поднять без рефакторинга |
| mbstring недоступен | Очень низкая | mbstring входит в PHP 7.4 core |
| Регрессия других шаблонов | Нет | Изменение строго изолировано в print2.php |
