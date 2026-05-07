Запусти тестирование stories. Аргументы: $ARGUMENTS (номер issue или "all")

Запусти субагента **qa-lead** со следующим заданием:

---

Протестируй: $ARGUMENTS

Если аргумент "all" — найди все issues с label `kanban:testing`:
```bash
gh issue list --label "kanban:testing" --json number,title
```

Для каждого issue выполни полный Testing Flow:

1. **Status-first**: добавь `qa:in-progress` ДО начала работы
2. **Pre-flight**: проверь build, наличие developer comment
3. **Security Check** (КРИТИЧНО): проверь наличие `security:passed` label
   - Отсутствует → BLOCKED, НЕ тестировать, запроси `/develop #{N}`
4. **WIP Check**: testing < 5 issues
5. **Dependency Check**: читай `.claude/agents/dependency-resolver.md`
6. **Параллельно запусти**:
   - **tester**: unit tests ТОЛЬКО (coverage ≥ 95%, < 2 мин, моки)
   - **test-case-writer**: TC-документация из spec + arch + AC
7. **Gate G5**: testing → ready-to-deploy (coverage, AC verified, TC doc, security:passed)
   - PASS → kanban:ready-to-deploy + qa:passed
   - FAIL → kanban:in-development + qa:failed + Bug Issue
8. **doc-sync**: обнови traceability.md + TC → tests mapping
9. Дополни `.claude/memory/stories/story-{N}.md` раздел ✅ QA

Читай shared-протоколы:
- `.claude/agents/quality-gates.md` — G5 чеклист
- `.claude/agents/kanban-board-sync.md` — обновление kanban

НИКОГДА не выставляй `security:*` labels — только читай.
Quality Gate Report G5 с реальным coverage report.
