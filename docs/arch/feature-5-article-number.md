# Feature #5 Architecture: Dobavit pole "Artikulnyj nomer" v tovary i otchety

**Feature Issue:** #5
**Stack:** PHP 7.4+, Yii2, MySQL, kartik\grid GridView
**Arch Doc Created:** 2026-05-08

---

## ERD — Izmeneniya skhemy BD

### Tekushchaya skhema tablicy `product` (fragmenty)

```sql
CREATE TABLE `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `id_type` int(11) NOT NULL,
  `boxing` int(11) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

### Izmenenie

```sql
ALTER TABLE `product`
  ADD COLUMN `article_number` VARCHAR(100) NULL AFTER `name`;
```

### Migraciya (Yii2)

Fajl: `migrations/m260508_000001_add_article_number_to_product.php`

```php
<?php

use yii\db\Migration;

class m260508_000001_add_article_number_to_product extends Migration
{
    public function safeUp()
    {
        $this->addColumn('product', 'article_number', $this->string(100)->null()->after('name'));
    }

    public function safeDown()
    {
        $this->dropColumn('product', 'article_number');
    }
}
```

---

## Zatragivaemye fajly

| Fajl | Tip izmeneniya | Story |
|------|----------------|-------|
| `migrations/m260508_000001_add_article_number_to_product.php` | Sozdat (novyj fajl) | #6 |
| `models/Product.php` | Izmenit: rules(), attributeLabels() | #7 |
| `views/product/_form.php` | Izmenit: dobavit field | #8 |
| `views/sell/report.php` | Izmenit: dobavit kolonku GridView | #9 |
| `views/sell/rest.php` | Izmenit: dobavit kolonku GridView | #10 |
| `views/arrival/report.php` | Izmenit: dobavit kolonku GridView | #11 |
| `views/sell/find.php` | Izmenit: dobavit kolonku GridView | #12 |
| `views/arrival/find.php` | Izmenit: dobavit kolonku GridView | #12 |

---

## Code Stubs

### 1. Model Product.php — rules() i attributeLabels()

```php
// V metode rules() dobavit:
[['article_number'], 'string', 'max' => 100],
[['article_number'], 'default', 'value' => null],

// V metode attributeLabels() dobavit:
'article_number' => 'Artikul nomresi',
```

### 2. views/product/_form.php

```php
// Posle <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'article_number')->textInput(['maxlength' => true]) ?>
```

### 3. views/sell/report.php — kolonka v GridView

```php
// Posle kolonki name_product:
[
    'label'  => 'Artikul nomresi',
    'value'  => 'idProduct.article_number',
    'format' => 'raw',
    'width'  => '120px',
],
```

### 4. views/sell/rest.php — kolonka v GridView

```php
// Posle kolonki name_product:
[
    'label'  => 'Artikul nomresi',
    'value'  => 'idProduct.article_number',
    'format' => 'raw',
    'width'  => '120px',
],
```

### 5. views/arrival/report.php — kolonka v GridView

```php
// Posle kolonki name_product:
[
    'label'  => 'Artikul nomresi',
    'value'  => 'idProduct.article_number',
    'format' => 'raw',
    'width'  => '120px',
],
```

### 6. views/sell/find.php — kolonka v GridView modalnogo okna

```php
// Posle kolonki 'barcode':
[
    'label'  => 'Artikul nomresi',
    'value'  => 'product.article_number',
    'format' => 'raw',
    'width'  => '120px',
],
```

### 7. views/arrival/find.php — kolonka v GridView modalnogo okna

```php
// Posle kolonki 'barcode' (stroka 103):
[
    'label'  => 'Artikul nomresi',
    'value'  => 'article_number',
    'format' => 'raw',
    'width'  => '120px',
],
```

---

## Analiz relyacij

Vse otchety poluchayut dostup k `article_number` cherez uzhe sushchestvuyushchie ActiveRecord-relyacii:

- `SellSearch` -> `Sell::idProduct()` -> `Product::article_number`
- `RestSearch` -> `Arrival::idProduct()` -> `Product::article_number`
- `ArrivalSearch` -> `Arrival::idProduct()` -> `Product::article_number`
- `sell/find.php` ispolzuet `RestSearch` ili `SellProductSearch` s relyaciej `product`
- `arrival/find.php` ispolzuet model `Product` napryamuyu (GridView otobrazhaet `Product`)

**Novye relyacii sozdavat ne nuzhno** — vse relyacii uzhe sushchestvuyut v kodbaze.

---

## ADR (Architecture Decision Records)

### ADR-1: Nullable kolona bez UNIQUE

**Reshenie:** `article_number VARCHAR(100) NULL` bez UNIQUE constraint.
**Prichina:** Issue yavno isklyuchaet unikalizaciya artikula iz scope. Dobavlenie UNIQUE ogranichit vvod dannykh dlya sushchestvuyushchikh tovarov bez artikula.

### ADR-2: Dostup k article_number cherez relyaciyu, a ne JOIN

**Reshenie:** Ispol'zovat `idProduct.article_number` v GridView value closure.
**Prichina:** Yii2 lazy loading podderzhivaet eto iz korobki; dopolnitelnye JOIN v Search-modeli ne nuzhny dlya proslogo otobrazheniya.

### ADR-3: Pole v forme — odna forma _form.php dlya create i update

**Reshenie:** Dobavit pole v `_form.php` odin raz.
**Prichina:** Yii2 pattern — create.php i update.php oba renderyat `_form.php`. Izmenenie odnoj fajla pokryvaet oba scenariya.

---

## Poryadok deploya

1. Zapustit migraciyu: `php yii migrate --interactive=0`
2. Sbrosat kesh (esli est): `php yii cache/flush-all`
3. Izmeneniya PHP-fajlov — bez restar­ta servera (interpretiruyutsya on-the-fly)
