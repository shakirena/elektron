Создай 35 GitHub-меток и настрой Project Board. Выполняется один раз при инициализации проекта.

## Шаг 1. Создай все labels

```bash
# === Kanban колонки ===
gh label create "kanban:backlog" --color "0E8A16" --description "Backlog" --force
gh label create "kanban:analysis" --color "0075CA" --description "Analysis in progress" --force
gh label create "kanban:ready-for-dev" --color "7057FF" --description "Ready for development" --force
gh label create "kanban:in-development" --color "FBCA04" --description "In development" --force
gh label create "kanban:testing" --color "E4E669" --description "In testing" --force
gh label create "kanban:ready-to-deploy" --color "F9A826" --description "Ready to deploy" --force
gh label create "kanban:done" --color "006B75" --description "Done" --force

# === Типы ===
gh label create "type:feature" --color "0075CA" --description "Feature request" --force
gh label create "type:epic" --color "3E4B9E" --description "Epic" --force
gh label create "type:story" --color "7057FF" --description "User story" --force
gh label create "type:bug" --color "D73A4A" --description "Bug report" --force
gh label create "type:tech-debt" --color "E4E669" --description "Technical debt" --force
gh label create "type:hotfix" --color "B60205" --description "Hotfix (skips G1/G2)" --force
gh label create "type:spike" --color "BFDADC" --description "Research spike" --force

# === Приоритеты ===
gh label create "priority:critical" --color "B60205" --description "Critical priority" --force
gh label create "priority:high" --color "D93F0B" --description "High priority" --force
gh label create "priority:medium" --color "E4E669" --description "Medium priority" --force
gh label create "priority:low" --color "0E8A16" --description "Low priority" --force

# === Размеры ===
gh label create "size:xs" --color "C2E0C6" --description "XS: < 2 hours" --force
gh label create "size:s" --color "C2E0C6" --description "S: half a day" --force
gh label create "size:m" --color "C2E0C6" --description "M: 1-2 days" --force
gh label create "size:l" --color "C2E0C6" --description "L: 3-5 days" --force

# === Компоненты ===
gh label create "component:backend" --color "1D76DB" --description "Backend component" --force
gh label create "component:frontend" --color "1D76DB" --description "Frontend component" --force
gh label create "component:ai" --color "1D76DB" --description "AI/ML component" --force
gh label create "component:infra" --color "1D76DB" --description "Infrastructure" --force
gh label create "component:docs" --color "1D76DB" --description "Documentation" --force

# === QA статусы ===
gh label create "qa:in-progress" --color "FBCA04" --description "QA in progress" --force
gh label create "qa:passed" --color "0E8A16" --description "QA passed (coverage >= 95%)" --force
gh label create "qa:failed" --color "D73A4A" --description "QA failed" --force

# === Security статусы ===
gh label create "security:passed" --color "0E8A16" --description "Security review passed" --force
gh label create "security:failed" --color "B60205" --description "Security review failed" --force

# === Deploy статусы ===
gh label create "deployed:staging" --color "0075CA" --description "Deployed to staging" --force
gh label create "deployed:production" --color "006B75" --description "Deployed to production" --force
```

## Шаг 2. Проверь созданные labels

```bash
gh label list --limit 50 | sort
```

Должно быть 35 labels.

## Шаг 3. Создай GitHub Project Board (если нужен)

```bash
# Создай проект
gh project create --title "Kanban Board" --owner {owner}

# Получи PROJECT_ID
gh project list --owner {owner} --format json | jq '.projects[] | select(.title == "Kanban Board") | .id'
```

Сохрани PROJECT_ID в `.claude/memory/project-summary.md` секция "GitHub Project IDs".

## Шаг 4. Добавь колонки (Status field) в Project

Через UI GitHub Projects или GraphQL:
- Backlog
- Analysis  
- Ready for Dev
- In Development
- Testing
- Ready to Deploy
- Done

## Результат

```
✅ 35 labels созданы
✅ GitHub Project Board готов (или: ℹ️ настройте через UI)

Следующий шаг: /init для полной инициализации системы
```
