# Feature #5 Spec: Dobavit pole "Artikulnyj nomer" v tovary i otchety

**Feature Issue:** #5
**Status:** ready-for-dev
**Priority:** medium
**Stack:** PHP 7.4+, Yii2 Framework, MySQL, kartik\grid GridView

---

## Biznes-cennost

Menedzhery, kladovshchiki i kassiry ne mogut bystro sootnosit tovary v sisteme s katalogami postavshchikov, t.k. v sisteme otsutstvuet artikulnyj nomer. Dobavlenie polya article_number pozvolyaet:
- Identifitsirovat tovar po artikuly postavshchika
- Sverit prikhod tovarov s zakaz-naryados/nakladnoj
- Iskat tovar po artikuly v modalnyh oknah

---

## Functional Requirements (FR)

### FR-1: Migraciya BD
Sistema dolzhna soderzshat kolonku `article_number VARCHAR(100) NULL` v tablice `product`.

### FR-2: Model Product
Model `Product` dolzhna validirovat pole `article_number` (string, max 100, nullable) i imet russko/azerbajdzhanskuyu metku `'Artikul nomresi'`.

### FR-3: Forma sozdaniya tovara
Forma sozdaniya tovara (`views/product/_form.php`) dolzhna otobrazhat pole `article_number`.

### FR-4: Forma redaktirovaniya tovara
Forma redaktirovaniya tovara (`views/product/_form.php`, edit rezhim) dolzhna otobrazhat i sohraniyat pole `article_number`.

### FR-5: Otchet Prodazhi
V otchete Prodazhi (`views/sell/report.php`) dolzhna prisutstvovat kolonka `Artikul nomresi`.

### FR-6: Otchet Ostatok
V otchete Ostatok/Balans (`views/sell/rest.php`) dolzhna prisutstvovat kolonka `Artikul nomresi`.

### FR-7: Otchet Prihod
V otchete Prihod (`views/arrival/report.php`) dolzhna prisutstvovat kolonka `Artikul nomresi`.

### FR-8: Modalnoye okno poiska na stranitse Prodazhi
Modalnoye okno poiska tovara (`views/sell/find.php`) dolzhno otobrazhat kolonku `Artikul nomresi`.

### FR-9: Modalnoye okno poiska na stranitse Prihoda
Modalnoye okno poiska tovara (`views/arrival/find.php`) dolzhno otobrazhat kolonku `Artikul nomresi`.

---

## Non-Functional Requirements (NFR)

### NFR-1: Obratmaya sovmestimost
Pole `article_number` — nullable: sushchestvuyushchie zapisi prodolzhayut rabotat bez izmenenij dannykh.

### NFR-2: Bez unikalizacii
Unikalizaciya artikula ne trebuetsya (ochevidno iz scope issue).

### NFR-3: Migraciya obratnaya
Migraciya dolzhna imet `safeDown()` dlya otkata.

### NFR-4: Bez eksporta CSV
Eksport article_number cherez CSV/Excel nakhodit­sya vne scope.

---

## Acceptance Criteria (svodnye)

| # | Kriterij | Scope |
|---|----------|-------|
| AC-1 | Kolonka `article_number VARCHAR(100) NULL` dobavlena cherez migraciyu | BD |
| AC-2 | Model Product validirует pole (max 100, neobya­zatelnoe) | Model |
| AC-3 | Forma sozdaniya tovara: pole otobrazhaetsya i sohranjaetsya | UI/Create |
| AC-4 | Forma redaktirovaniya: pole otobrazhaetsya i sohranjaetsya | UI/Update |
| AC-5 | Otchet Prodazhi: kolonka Artikul nomresi | Report/Sales |
| AC-6 | Otchet Ostatok: kolonka Artikul nomresi | Report/Balances |
| AC-7 | Otchet Prihod: kolonka Artikul nomresi | Report/Arrival |
| AC-8 | Modalnoye okno na Prodazhah: kolonka Artikul nomresi | Modal/Sales |
| AC-9 | Modalnoye okno na Prikhode: kolonka Artikul nomresi | Modal/Arrival |

---

## User Stories Decomposition

| Story Issue | Nazvanie | Razmyer |
|-------------|----------|---------|
| #6 | Migraciya BD — dobavit kolonku article_number v tablicu product | S |
| #7 | Model Product — atribut article_number s validaciej | S |
| #8 | Forma tovara — pole article_number v create i update | S |
| #9 | Otchet Prodazhi — kolonka article_number | S |
| #10 | Otchet Ostatok — kolonka article_number | S |
| #11 | Otchet Prihod — kolonka article_number | S |
| #12 | Modalnye okna poiska tovara — kolonka article_number | S |

---

## Out of Scope

- Importovanie/eksportirovaniye article_number cherez CSV/Excel
- Avtogeneraciya artikulov
- Unikalizaciya artikula
