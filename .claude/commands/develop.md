Запусти разработку stories. Аргументы: $ARGUMENTS (номера issues, например: #5 #6 #7)

Запусти субагента **dev-lead** со следующим заданием:

---

Реализуй stories: $ARGUMENTS

Выполни полный Development Flow:

1. **WIP Check**: проверь что in-development < 5 issues
2. **Dependency Check**: читай `.claude/agents/dependency-resolver.md` для каждого issue
3. **Gate G3**: ready-for-dev → in-development (label, spec, arch, AC testable, deps, WIP)
4. **Status-first**: обнови kanban (kanban:in-development) ДО начала разработки
5. **Запусти developer**: в worktree isolation для каждого issue
   - Читает `.claude/memory/stories/story-{N}.md` (экономия токенов)
   - Branch: feature/{parent-N}-{name}
   - Data Layer → Service → API → Frontend
   - Unit tests для service layer
   - Build verification
   - Создаёт `.claude/handoffs/story-{N}-dev.md`
6. **Gate G4**: code checks (build, lint, tests написаны, нет TODO/FIXME/debug)
7. **Security Review** (СИНХРОННО): запусти **security-reviewer**, жди результата
   - PASS → label: security:passed
   - FAIL → developer исправляет (max 3 цикла)
8. **Обнови kanban**: kanban:testing (ТОЛЬКО после security:passed)
9. **doc-sync**: обнови traceability.md

Читай shared-протоколы:
- `.claude/agents/quality-gates.md` — G3, G4 чеклисты
- `.claude/agents/kanban-board-sync.md` — обновление kanban
- `.claude/agents/dependency-resolver.md` — зависимости

Для каждого Gate оставь Quality Gate Report comment с реальным выводом команд.
Несколько stories обрабатывай параллельно (каждая в своём worktree).
