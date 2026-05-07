Запусти анализ feature issue. Аргументы: $ARGUMENTS (номер issue, например: #5)

Запусти субагента **analysis-lead** со следующим заданием:

---

Проанализируй GitHub Issue $ARGUMENTS.

Выполни полный Analysis Flow:

1. **Status-first**: обнови kanban (kanban:analysis) и Project Board ДО начала работы
2. **Gate G1**: backlog → analysis (проверь business value, AC, дубликаты, тип, приоритет)
3. **Dependency Check**: проверь зависимости через `.claude/agents/dependency-resolver.md`
4. **Параллельно запусти**:
   - субагента **analyst**: декомпозиция на User Stories, spec-документ, SD-1..SD-5
   - субагента **architect**: arch-документ, ERD, API contracts, code stubs
5. **Gate G2**: analysis → ready-for-dev (spec + arch + INVEST + SD-1..SD-5)
6. **Обнови kanban**: kanban:ready-for-dev (после PASS G2)
7. Запусти **doc-sync**: обнови traceability.md

Читай shared-протоколы:
- `.claude/agents/quality-gates.md` — G1 и G2 чеклисты + report templates
- `.claude/agents/kanban-board-sync.md` — как обновлять labels и Project Board
- `.claude/agents/dependency-resolver.md` — как проверять зависимости

Для каждого Gate оставь Quality Gate Report comment в issue с реальным выводом проверок.
