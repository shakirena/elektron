Создай GitHub Issue для новой feature.

Аргументы: $ARGUMENTS — описание feature

## Что нужно сделать

1. Проанализируй описание: `$ARGUMENTS`

2. Создай GitHub Issue:

```bash
gh issue create \
  --title "[Feature] {краткое название}" \
  --body "## Описание

{развёрнутое описание фичи}

## Ценность

{что получит пользователь}

## Acceptance Criteria

- [ ] AC-1: {первый критерий приёмки}
- [ ] AC-2: {второй критерий приёмки}

## Вне Scope

- {что не включено}

## Notes

{технические заметки если есть}" \
  --label "type:feature,kanban:backlog,priority:medium"
```

3. Уточни приоритет по описанию:
   - `priority:critical` — блокирует пользователей / критическая ошибка
   - `priority:high` — важная фича / влияет на core workflow
   - `priority:medium` — полезная фича / улучшение
   - `priority:low` — nice-to-have / косметика

4. Выведи: номер созданного issue + ссылку

5. Подскажи следующий шаг: `/analyze #{N}` для декомпозиции на User Stories
