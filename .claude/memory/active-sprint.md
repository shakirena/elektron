# Active Sprint

*Обновлено: 2026-05-08*

## Deployed: Staging

### Feature #5 — Артикульный номер товара (COMPLETED 2026-05-08)

- #6 Story: Migraciya BD — deployed:staging, kanban:done
- #7 Story: Model Product — deployed:staging, kanban:done
- #8 Story: Forma tovara — deployed:staging, kanban:done
- #9 Story: Otchet Prodazhi — deployed:staging, kanban:done
- #10 Story: Otchet Ostatok — deployed:staging, kanban:done
- #11 Story: Otchet Prihod — deployed:staging, kanban:done
- #12 Story: Modalnye okna poiska — deployed:staging, kanban:done

**Деплой:** 2026-05-08
**Migration:** m260508_000001_add_article_number_to_product — APPLIED
**Rollback:** php yii migrate/down 1 --interactive=0

### Feature #13 — Фильтр article_number в отчётах (COMPLETED 2026-05-08)

- #14 Story: Фильтр article_number в SellSearch и отчёте Продажи — deployed:staging, kanban:done
- #15 Story: Фильтр article_number в ArrivalSearch и отчёте Прихода — deployed:staging, kanban:done
- #16 Story: Колонка и фильтр article_number в RestSearch и отчёте Остатков — deployed:staging, kanban:done

**Деплой:** 2026-05-08
**Migration:** нет (только PHP файлы — модели и views)
**Rollback:** git revert 8faad47 --no-edit
**Commits:** 8faad47 (реализация), 77dbd61 (тесты + TC docs)

## In Development (0/5)

(пусто)

## In Testing (0/5)

(пусто)

---

*Этот файл обновляют Team Leads (dev-lead, qa-lead, ops-lead) при каждом изменении WIP.*
*Рабочие агенты (developer, tester) не трогают этот файл.*
