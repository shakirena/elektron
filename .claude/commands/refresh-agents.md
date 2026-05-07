Синхронизируй агентов из источника. Аргументы: $ARGUMENTS (путь к источнику или URL)

Синхронизируй агентов из другого проекта-источника.

## Определи источник

Если аргумент указан: `$ARGUMENTS` — это путь к `.claude/agents/` источника.
Иначе — спроси пользователя.

## Что синхронизировать

Сравни файлы агентов между текущим проектом и источником:

```bash
# Список агентов в текущем проекте
ls .claude/agents/

# Список агентов в источнике (если локальный путь)
ls $ARGUMENTS/
```

## Стратегия обновления

**Обновлять** (заменить):
- Team Lead агенты (analysis-lead, dev-lead, qa-lead, ops-lead)
- Shared протоколы (quality-gates, dependency-resolver, kanban-board-sync)
- Рабочие агенты (analyst, architect, security-reviewer, tester, test-case-writer, e2e-tester, devops, doc-sync)

**НЕ обновлять** (проект-специфичное):
- `developer.md` — содержит стек-специфичный код (PHP/Yii2)
- `.claude/memory/` — содержит данные текущего проекта
- `.claude/commands/` — команды проекта

## Выполни синхронизацию

```bash
# Для каждого агента из источника
cp $ARGUMENTS/analysis-lead.md .claude/agents/analysis-lead.md
cp $ARGUMENTS/dev-lead.md .claude/agents/dev-lead.md
cp $ARGUMENTS/qa-lead.md .claude/agents/qa-lead.md
cp $ARGUMENTS/ops-lead.md .claude/agents/ops-lead.md
cp $ARGUMENTS/quality-gates.md .claude/agents/quality-gates.md
cp $ARGUMENTS/dependency-resolver.md .claude/agents/dependency-resolver.md
cp $ARGUMENTS/kanban-board-sync.md .claude/agents/kanban-board-sync.md
# ... другие агенты кроме developer.md
```

## Отчёт

```
Синхронизировано:
  ✅ analysis-lead.md
  ✅ dev-lead.md
  ...

Пропущено (проект-специфичное):
  ℹ️ developer.md
  ℹ️ .claude/memory/

Проверь developer.md вручную на совместимость с новой версией.
```
