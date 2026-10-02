# Feature #13 Spec: Фильтр article_number в отчётах

**GitHub Issue:** #13
**Status:** ready-for-dev
**Priority:** medium
**Stories:** #14, #15, #16

## Context

Поле `article_number` уже существует в таблице `product` (добавлено в feature #5).
Три отчёта (Продажи, Приход, Остатки) не имеют фильтра по этому полю.

## Functional Requirements

| ID | Требование |
|---|---|
| FR-1 | SellSearch фильтрует записи по `product.article_number` (LIKE) |
| FR-2 | sell/report.php отображает активный фильтр в колонке Artikul nomresi |
| FR-3 | ArrivalSearch фильтрует записи по `product.article_number` (LIKE) |
| FR-4 | arrival/report.php отображает активный фильтр в колонке Artikul nomresi |
| FR-5 | RestSearch фильтрует записи по `product.article_number` (LIKE) |
| FR-6 | arrival/rest.php отображает новую колонку Artikul nomresi с фильтром |

## Acceptance Criteria

### Story #14 — SellSearch
- SellSearch: `article_number` добавлен в safe rules
- SellSearch: `andFilterWhere(['like', 'product.article_number', $this->article_number])`
- sell/report.php: колонка Artikul nomresi содержит `'attribute' => 'article_number'`

### Story #15 — ArrivalSearch
- ArrivalSearch: `article_number` добавлен в safe rules
- ArrivalSearch: `andFilterWhere(['like', 'product.article_number', $this->article_number])`
- arrival/report.php: колонка Artikul nomresi содержит `'attribute' => 'article_number'`

### Story #16 — RestSearch
- RestSearch: `article_number` добавлен в safe rules
- RestSearch: `andFilterWhere(['like', 'product.article_number', $this->article_number])`
- arrival/rest.php: новая колонка Artikul nomresi с value через `idProduct.article_number` и фильтром

## Out of Scope

- Другие отчёты (кроме sell/report, arrival/report, arrival/rest)
- Экспорт в Excel/PDF
- Точный поиск (только LIKE)
- Миграции БД (поле уже существует)
