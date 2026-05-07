# Kanban Board Sync — Shared Protocol

Читается Team Leads напрямую. Не является агентом.

---

## Двойная синхронизация (ОБЯЗАТЕЛЬНО)

При каждой смене колонки выполнить ОБА шага:
1. **Label** — убрать старый `kanban:*`, добавить новый
2. **Project Board Status** — через `gh project item-edit`

**ПОРЯДОК: СНАЧАЛА статус → ПОТОМ работа.**

---

## Шаг 1. Обновить Label

```bash
# Убрать старую колонку, добавить новую
gh issue edit #{N} \
  --remove-label "kanban:{old-column}" \
  --add-label "kanban:{new-column}"
```

### Переходы и labels

| Переход | Remove | Add |
|---------|--------|-----|
| backlog → analysis | `kanban:backlog` | `kanban:analysis` |
| analysis → ready-for-dev | `kanban:analysis` | `kanban:ready-for-dev` |
| ready-for-dev → in-development | `kanban:ready-for-dev` | `kanban:in-development` |
| in-development → testing | `kanban:in-development` | `kanban:testing` |
| testing → ready-to-deploy | `kanban:testing` | `kanban:ready-to-deploy` |
| ready-to-deploy → done | `kanban:ready-to-deploy` | `kanban:done` |
| any → in-development (fail) | текущий | `kanban:in-development` |

---

## Шаг 2. Обновить Project Board

### Найти Project ID и Issue Item ID

```bash
# Получи PROJECT_ID (один раз, сохрани в памяти)
gh project list --owner {owner} --format json | jq '.projects[] | select(.title == "{Project Name}") | .id'

# Получи item ID для issue в проекте
gh project item-list {PROJECT_ID} --owner {owner} --format json | \
  jq ".items[] | select(.content.number == {ISSUE_NUMBER}) | .id"
```

### Обновить Status через GraphQL

```bash
# Получи field ID для Status
gh api graphql -f query='
  query {
    node(id: "{PROJECT_ID}") {
      ... on ProjectV2 {
        fields(first: 20) {
          nodes {
            ... on ProjectV2SingleSelectField {
              id
              name
              options { id name }
            }
          }
        }
      }
    }
  }
' | jq '.data.node.fields.nodes[] | select(.name == "Status")'
```

```bash
# Обнови Status
gh api graphql -f query='
  mutation {
    updateProjectV2ItemFieldValue(input: {
      projectId: "{PROJECT_ID}"
      itemId: "{ITEM_ID}"
      fieldId: "{STATUS_FIELD_ID}"
      value: { singleSelectOptionId: "{OPTION_ID}" }
    }) {
      projectV2Item { id }
    }
  }
'
```

### Маппинг Status → Option Name

| Kanban колонка | Project Status name |
|----------------|---------------------|
| backlog | Backlog |
| analysis | Analysis |
| ready-for-dev | Ready for Dev |
| in-development | In Development |
| testing | Testing |
| ready-to-deploy | Ready to Deploy |
| done | Done |

---

## Кэширование Project IDs

Team Leads кэшируют IDs в `.claude/memory/project-summary.md` (секция "GitHub Project IDs") чтобы не запрашивать каждый раз.

```markdown
## GitHub Project IDs

- PROJECT_ID: PVT_xxxxx
- STATUS_FIELD_ID: PVTSSF_xxxxx
- Status Options:
  - Backlog: xxxxx
  - Analysis: xxxxx
  - Ready for Dev: xxxxx
  - In Development: xxxxx
  - Testing: xxxxx
  - Ready to Deploy: xxxxx
  - Done: xxxxx
```

---

## Упрощённый вариант (если Project Board не настроен)

Если GitHub Project Board не настроен — только Label обновление:

```bash
gh issue edit #{N} --remove-label "kanban:{old}" --add-label "kanban:{new}"
```

В этом случае `/board` команда читает только labels для отображения колонок.

---

## Дополнительные labels при переходах

| Переход | Дополнительно |
|---------|---------------|
| → in-development | ничего |
| → testing | убедись что `security:passed` выставлен dev-lead |
| → ready-to-deploy | добавить `qa:passed` (qa-lead) |
| → done | закрыть issue (`gh issue close #{N}`) |
| failed QA | добавить `qa:failed`, вернуть в `in-development` |

---

## Быстрая команда для done

```bash
gh issue edit #{N} \
  --remove-label "kanban:ready-to-deploy" \
  --add-label "kanban:done,deployed:staging"

gh issue close #{N} --comment "Задеплоено на staging. [Deploy Report в предыдущем комментарии]"
```
