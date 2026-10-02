# Feature #13 Arch: Фильтр article_number в отчётах

**GitHub Issue:** #13
**Stories:** #14, #15, #16

## Overview

Добавление LIKE-фильтра по `product.article_number` в три существующих Search-модели и соответствующие view-файлы. Никаких миграций, новых таблиц или API не требуется.

## Затронутые файлы

### Story #14 — models/SellSearch.php

**Что изменить:**

В методе `rules()` добавить `'article_number'` в safe-список:
```php
// До:
[['datetime', 'date_issue','sn','date_start','date_end','type','id_product','id_store','barcode','name_product','id','name_client','status'], 'safe'],

// После:
[['datetime', 'date_issue','sn','date_start','date_end','type','id_product','id_store','barcode','name_product','id','name_client','status','article_number'], 'safe'],
```

В методе `search()` добавить andFilterWhere после существующих фильтров:
```php
->andFilterWhere(['like', 'product.article_number', $this->article_number])
```

Relation `idProduct` уже подключён через `joinWith(['idProduct.idType', 'idProduct.barcodes', 'idClient'])`.

### Story #14 — views/sell/report.php (строки ~183-187)

В существующей колонке `Artikul nomresi` добавить атрибут для активации фильтра GridView:
```php
'attribute' => 'article_number',
```

### Story #15 — models/ArrivalSearch.php

В методе `rules()` добавить `'article_number'` в safe-список:
```php
// До:
[['datetime','date_start','date_end', 'id_product','type','name_product','barcode','type_name'], 'safe'],

// После:
[['datetime','date_start','date_end', 'id_product','type','name_product','barcode','type_name','article_number'], 'safe'],
```

В методе `search()`:
```php
->andFilterWhere(['like', 'product.article_number', $this->article_number])
```

Relation `idProduct` доступен через `joinWith(['idProduct.idType'])` + JOIN bar_code.

### Story #15 — views/arrival/report.php

В существующей колонке `Artikul nomresi` добавить:
```php
'attribute' => 'article_number',
```

### Story #16 — models/RestSearch.php

В методе `rules()` добавить `'article_number'` в safe-список:
```php
// До:
[['datetime','date_start','date_end', 'id_product','type','sumsell','name_product','type_name','barcode'], 'safe'],

// После:
[['datetime','date_start','date_end', 'id_product','type','sumsell','name_product','type_name','barcode','article_number'], 'safe'],
```

В методе `search()`:
```php
->andFilterWhere(['like', 'product.article_number', $this->article_number])
```

### Story #16 — views/arrival/rest.php

Добавить новую колонку GridView (новая колонка, не редактирование существующей):
```php
[
    'attribute' => 'article_number',
    'label' => 'Artikul nomresi',
    'value' => function ($model) {
        return $model->idProduct->article_number ?? '';
    },
],
```

## ADR

**ADR-1: LIKE вместо точного совпадения**
Решение: использовать `andFilterWhere(['like', ...])` для удобства поиска по частичному артикулу.
Альтернатива: точное совпадение `=`. Отклонено — пользователи могут не знать полный артикул.

**ADR-2: Без миграций**
Поле `article_number` уже существует в таблице `product` (feature #5). Никаких изменений схемы БД не требуется.

**ADR-3: RestSearch — новая колонка вместо редактирования**
В arrival/rest.php колонки Artikul nomresi нет, поэтому добавляется новая. В sell/report.php и arrival/report.php колонка уже есть — только добавляется `attribute`.
