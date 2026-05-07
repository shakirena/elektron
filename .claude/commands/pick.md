Выбери следующую задачу по приоритету. Аргументы: $ARGUMENTS (колонка: backlog | ready-for-dev | testing | ready-to-deploy)

Найди следующую задачу для работы в указанной колонке (или во всей доске).

## Определи колонку

Если аргумент указан — ищи в нём. Иначе — right-to-left: ready-to-deploy → testing → ready-for-dev → backlog.

## Алгоритм выбора (по приоритету)

```bash
# Приоритет: critical → high → medium → low
# Колонка: kanban:{column}

# 1. Сначала critical
gh issue list \
  --label "kanban:{column},priority:critical" \
  --json number,title,labels \
  --jq 'first'

# 2. Если нет critical — high
gh issue list \
  --label "kanban:{column},priority:high" \
  --json number,title,labels \
  --jq 'first'

# и т.д.
```

## Дополнительные фильтры

- Исключи issues с `Blocked by:` открытыми blockers
- Для ready-for-dev: проверь WIP limit (< 5 in-development)
- Для testing: проверь наличие `security:passed`

## Вывод

```
Следующая задача: #{N} — {title}
Колонка: {column}
Приоритет: {priority}
Компонент: {component}

Рекомендованная команда: /{команда} #{N}
```

Если нет доступных задач — сообщи об этом и предложи проверить блокировки через `/board`.
