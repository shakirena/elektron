Покажи Kanban-доску с метриками, WIP и блокировками.

Получи и отобрази текущее состояние Kanban-доски:

```bash
# Все открытые issues с kanban labels
gh issue list --label "kanban:backlog" --json number,title,labels,assignees --jq '.[] | {n: .number, t: .title}' 
gh issue list --label "kanban:analysis" --json number,title,labels --jq '.[] | {n: .number, t: .title}'
gh issue list --label "kanban:ready-for-dev" --json number,title,labels --jq '.[] | {n: .number, t: .title}'
gh issue list --label "kanban:in-development" --json number,title,labels --jq '.[] | {n: .number, t: .title}'
gh issue list --label "kanban:testing" --json number,title,labels --jq '.[] | {n: .number, t: .title}'
gh issue list --label "kanban:ready-to-deploy" --json number,title,labels --jq '.[] | {n: .number, t: .title}'
```

Отобрази в формате:

```
╔══════════════════════════════════════════════════════════════╗
║                    KANBAN BOARD                              ║
╠══════════════════════════════════════════════════════════════╣
║ BACKLOG          │ ANALYSIS    │ READY-DEV  │ IN-DEV (N/5)  ║
║ ─────────────── │ ─────────── │ ────────── │ ────────────── ║
║ #5 Feature X    │ #3 Story A  │ #7 Story D │ #9 Story F ✅  ║
║ #6 Feature Y    │             │ #8 Story E │ #10 Story G 🚫 ║
╠══════════════════════════════════════════════════════════════╣
║ TESTING (N/5)   │ READY-DEPL  │ DONE       │                ║
║ ─────────────── │ ─────────── │ ────────── │                ║
║ #11 Story H ✅  │ #13 Story J │ #14 Story K│                ║
║                 │             │            │                ║
╚══════════════════════════════════════════════════════════════╝

✅ security:passed  🚫 BLOCKED  ⚠️ qa:failed
```

## Метрики

```
WIP In-Development: N/5
WIP Testing: N/5
Cycle time (avg): N days

Blocked issues: #список
Issues without security:passed: #список
```

## Блокировки

Для каждого issue с `Blocked by:` — проверь статус blocker:
```bash
gh issue view #{N} --json body | grep -E "Blocked by:"
```

Выведи список заблокированных с причиной.

## Следующие шаги

Предложи конкретные команды:
- Если есть ready-to-deploy: `/deploy staging`
- Если есть testing: `/test all`
- Если есть ready-for-dev: `/develop #N #M`
- Если есть backlog: `/analyze #N`
