# Dependency Resolver — Shared Protocol

Читается Team Leads напрямую. Не является агентом.

---

## Синтаксис зависимостей в body issue

```
Blocked by: #10, #12
Depends on: #10
Requires: #10, #12, #15
```

Все три варианта (`Blocked by`, `Depends on`, `Requires`) означают одно: текущий issue не может начаться пока blockers не завершены.

---

## Алгоритм проверки зависимостей

### Шаг 1. Извлечь зависимости

```bash
gh issue view #N --json body | jq -r '.body' | grep -E "Blocked by:|Depends on:|Requires:" | grep -oE "#[0-9]+"
```

### Шаг 2. Проверить статус каждого blocker

```bash
gh issue view #{blocker} --json state,labels | jq '{state: .state, labels: [.labels[].name]}'
```

### Шаг 3. Принять решение

| Состояние blocker | Решение |
|-------------------|---------|
| `state: "closed"` | ✅ Не блокирует — продолжай |
| `labels: ["kanban:done"]` | ✅ Не блокирует — продолжай |
| `state: "open"` без `kanban:done` | 🚫 BLOCKED — skip, сообщи |

### Шаг 4. Сообщить о блокировке

Если issue заблокирован, напиши в ответ:

```
BLOCKED: Issue #{N} заблокирован dependency #{blocker}.
Blocker #{blocker} статус: open / kanban:{column}.
Действие: skip #{N}, продолжай с незаблокированными issues.
```

---

## Circular Dependency Detection

Если при проверке обнаружено: A blocked by B, B blocked by A:

```
CIRCULAR DEPENDENCY DETECTED:
  #{A} Blocked by: #{B}
  #{B} Blocked by: #{A}

Действие: Manual intervention required.
Нельзя авторазрешить. Уведоми человека.
```

---

## Depth Limit

Если цепочка зависимостей > 10 уровней:

```
DEPENDENCY DEPTH EXCEEDED:
  #{N} → #{blocker1} → #{blocker2} → ... (> 10 уровней)

Действие: BLOCKED. Предупреди человека о слишком глубокой цепочке.
```

---

## Для analyst: добавление зависимостей

При создании зависимых stories, analyst добавляет в body:

```markdown
## Technical Notes

- Blocked by: #{data-layer-story} — требует созданную таблицу БД
- Blocked by: #{service-story} — требует реализованный сервис
```

---

## Для dev-lead и qa-lead: dependency check

Выполнять перед запуском агентов (Step 2.5 в workflow):

```bash
# Для каждого issue из списка
for issue_num in $ISSUE_LIST; do
    BLOCKERS=$(gh issue view #$issue_num --json body | jq -r '.body' | grep -E "Blocked by:|Depends on:|Requires:" | grep -oE "#[0-9]+" | tr -d '#')
    
    for blocker in $BLOCKERS; do
        STATUS=$(gh issue view $blocker --json state,labels | jq -r '.state')
        HAS_DONE=$(gh issue view $blocker --json labels | jq '[.labels[].name] | contains(["kanban:done"])')
        
        if [ "$STATUS" = "open" ] && [ "$HAS_DONE" = "false" ]; then
            echo "BLOCKED: #$issue_num blocked by #$blocker (open, not done)"
        fi
    done
done
```

---

## Для `/board`: визуализация

При отображении доски помечай заблокированные issues:

```
In Development (3/5):
  ✅ #12 Story: Login form
  ✅ #13 Story: Password validation  
  🚫 #14 Story: Admin panel [BLOCKED by #10]
```
