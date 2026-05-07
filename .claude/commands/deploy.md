Запусти деплой. Аргументы: $ARGUMENTS (staging | production | all)

Запусти субагента **ops-lead** со следующим заданием:

---

Выполни деплой: $ARGUMENTS

**Если аргумент "staging" или "all" или не указан:**

Найди все ready-to-deploy issues:
```bash
gh issue list --label "kanban:ready-to-deploy,qa:passed,security:passed" --json number,title
```

Выполни Deploy Flow:

1. **Gate G6 Pre-flight**: для каждого issue проверь наличие `kanban:ready-to-deploy` + `qa:passed` + `security:passed` + build OK
   - Любой check FAIL → СТОП для этого issue
2. **Запусти devops**:
   - Pre-deploy: тесты + build
   - Документация rollback команды (ДО деплоя)
   - Deploy: git pull, composer install --no-dev, migrate
   - Health check endpoint
   - Smoke test
   - Logs check (60 секунд)
3. **Gate G6 Post-deploy**: Health OK + Smoke OK + Logs clean
   - PASS → kanban:done + deployed:staging, закрыть issues
   - Health FAIL → НЕМЕДЛЕННЫЙ ROLLBACK → BLOCKED
4. Если все stories feature done → закрыть parent feature issue

**Если аргумент "production":**

1. Проверь наличие `deployed:staging` labels у issues
2. Запусти devops для подготовки Production Checklist + rollback команды
3. СТОП — напиши: "⚠️ Awaiting approval. Post `APPROVE PRODUCTION DEPLOY` to proceed."
4. НЕ деплоить до получения `APPROVE PRODUCTION DEPLOY` от человека

Читай: `.claude/agents/quality-gates.md` раздел G6, `.claude/agents/kanban-board-sync.md`
Quality Gate Report G6 с реальным выводом health check + smoke test.
