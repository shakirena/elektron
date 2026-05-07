Запусти все Team Leads параллельно по всей доске. Аргументы: $ARGUMENTS (develop | test | analyze | dry-run)

## Swarm Mode

Все Team Leads работают ПАРАЛЛЕЛЬНО по своим колонкам.

⚠️ Предупреждение: возможны race conditions на labels при параллельной работе.

### Определи issues для каждого Team Lead

```bash
# analysis-lead: все в backlog
BACKLOG=$(gh issue list --label "kanban:backlog" --json number --jq '.[].number')

# dev-lead: все в ready-for-dev
READY=$(gh issue list --label "kanban:ready-for-dev" --json number --jq '.[].number')

# qa-lead: все в testing
TESTING=$(gh issue list --label "kanban:testing" --json number --jq '.[].number')

# ops-lead: все в ready-to-deploy
DEPLOY=$(gh issue list --label "kanban:ready-to-deploy" --json number --jq '.[].number')
```

### Если аргумент "dry-run"

Только покажи что будет сделано:
```
SWARM DRY-RUN:
  analysis-lead → analyze: #{список}
  dev-lead → develop: #{список}
  qa-lead → test: #{список}
  ops-lead → deploy: #{список}
```
Не запускай агентов.

### Если аргумент "develop" (или нет аргумента)

Запусти **параллельно** (если issues есть в соответствующих колонках):
- **analysis-lead** для BACKLOG issues
- **dev-lead** для READY issues
- **qa-lead** для TESTING issues
- **ops-lead** для DEPLOY issues

### Если аргумент "test"

Запусти только **qa-lead** для всех TESTING issues.

### Если аргумент "analyze"

Запусти только **analysis-lead** для всех BACKLOG issues.

---

После завершения всех Team Leads покажи итоговый статус доски.
