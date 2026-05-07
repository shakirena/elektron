Автопилот right-to-left по всей доске. Аргументы: $ARGUMENTS (колонка | dry-run)

## Sweep Mode — Right-to-Left

Безопасный автопилот: обрабатывает колонки в порядке справа налево.
Минимум race conditions (каждая колонка завершается до следующей).

**Порядок**: deploy → test → develop → analyze

### Шаг 1. Получи текущее состояние доски

```bash
echo "=== READY TO DEPLOY ===" && gh issue list --label "kanban:ready-to-deploy,qa:passed,security:passed" --json number,title --jq '.[] | "#\(.number) \(.title)"'
echo "=== TESTING ===" && gh issue list --label "kanban:testing" --json number,title --jq '.[] | "#\(.number) \(.title)"'
echo "=== READY FOR DEV ===" && gh issue list --label "kanban:ready-for-dev" --json number,title --jq '.[] | "#\(.number) \(.title)"'
echo "=== BACKLOG ===" && gh issue list --label "kanban:backlog" --json number,title --jq '.[] | "#\(.number) \(.title)"'
```

### Если аргумент "dry-run"

Покажи план без запуска агентов:
```
SWEEP DRY-RUN (right-to-left):
1. ops-lead → deploy: #{список ready-to-deploy}
2. qa-lead → test: #{список testing}
3. dev-lead → develop: #{список ready-for-dev}
4. analysis-lead → analyze: #{список backlog}
```
СТОП — не запускай.

### Если аргумент — конкретная колонка

Обрабатывай только указанную колонку:
- `deploy` → только ops-lead
- `test` → только qa-lead
- `develop` → только dev-lead
- `analyze` → только analysis-lead

### Иначе — полный sweep

Выполняй последовательно (не параллельно):

**1. Deploy** (если есть ready-to-deploy):
Запусти **ops-lead** → дождись завершения

**2. Test** (если есть в testing):
Запусти **qa-lead** → дождись завершения

**3. Develop** (если есть в ready-for-dev):
Запусти **dev-lead** → дождись завершения

**4. Analyze** (если есть в backlog):
Запусти **analysis-lead** → дождись завершения

После sweep покажи финальное состояние доски (как `/board`).
