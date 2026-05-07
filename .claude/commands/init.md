Полная инициализация агентской системы на проекте. Выполняется один раз.

## Шаг 1. Проверь prerequisites

```bash
# GitHub CLI
gh auth status

# Git
git status

# PHP
php --version

# Composer
php composer.phar --version 2>/dev/null || composer --version
```

Если что-то не установлено — сообщи и остановись.

## Шаг 2. Проверь структуру .claude/

```bash
ls .claude/
ls .claude/agents/
ls .claude/commands/
ls .claude/memory/
ls .claude/memory/stories/
```

Должны существовать все директории и файлы агентов.

## Шаг 3. Создай структуру docs/

```bash
mkdir -p docs/specs docs/arch docs/test-cases
```

Создай `docs/traceability.md`:
```markdown
# Traceability Matrix

*Инициализировано: {date}*

| Feature | Story | Spec | Arch | Code | Unit Tests | TC Doc | E2E |
|---------|-------|------|------|------|------------|--------|-----|
| — | — | — | — | — | — | — | — |
```

Создай `docs/test-cases/traceability-tc.md`:
```markdown
# Test Case Traceability Matrix

*Инициализировано: {date}*

| Feature | Story | AC | TC | Priority | Type | E2E Automated |
|---------|-------|----|----|----------|------|---------------|
| — | — | — | — | — | — | — |
```

## Шаг 4. Инициализируй memory

Если `.claude/memory/project-summary.md` пуст — создай заготовку:
```markdown
# Project Summary

**Проект:** {название из README или CLAUDE.md}
**Стек:** PHP, Yii2, MySQL
**Build:** php yii, php composer.phar
**Tests:** php vendor/bin/codecept run unit

## GitHub Project IDs

(заполнить после /setup-board)
- PROJECT_ID: 
- STATUS_FIELD_ID:

## Ключевые модули

(заполнить на основе структуры проекта)
```

Если `.claude/memory/active-sprint.md` пуст:
```markdown
# Active Sprint

*Обновлено: {date}*

## In Development (0/5)

(пусто)

## In Testing (0/5)

(пусто)
```

Если `.claude/memory/decisions.md` пуст:
```markdown
# Architecture Decisions

*Инициализировано: {date}*

(Здесь будут накапливаться кросс-story архитектурные решения)
```

## Шаг 5. Setup labels и board

Запусти `/setup-board` если labels ещё не созданы:
```bash
gh label list --limit 5
```

## Шаг 6. Проверь GitHub scopes

```bash
gh auth status
```

Нужны scopes: `repo`, `project`, `read:org`.

Если не хватает:
```bash
gh auth refresh -s repo,project,read:org
```

## Шаг 7. Финальный отчёт

```
✅ Инициализация завершена

Структура:
  .claude/agents/ — 13 файлов агентов
  .claude/commands/ — 14 slash команд
  .claude/memory/ — project-summary, active-sprint, decisions, stories/
  docs/ — specs/, arch/, test-cases/, traceability.md

Следующие шаги:
  1. Заполни CLAUDE.md (стек, build-команды, RBAC, deploy)
  2. /setup-board — создай 35 GitHub labels
  3. /feature <описание> — создай первую фичу
  4. /analyze #1 — задекомпозируй фичу
```
