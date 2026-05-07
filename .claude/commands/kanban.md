Полный pipeline от идеи до деплоя. Аргументы: $ARGUMENTS (описание feature)

Выполни полный Kanban Pipeline для новой feature:

## Шаг 1. Создай Feature Issue

```bash
gh issue create \
  --title "[Feature] {название из описания}" \
  --body "## Описание
$ARGUMENTS

## Acceptance Criteria
- [ ] [сформулируй AC из описания]

## Вне Scope
- [что не входит]" \
  --label "type:feature,kanban:backlog,priority:medium"
```

Запомни номер созданного issue как FEATURE_N.

## Шаг 2. Analysis (`/analyze`)

Запусти **analysis-lead**:
- G1: backlog → analysis
- Параллельно: analyst (stories + spec) + architect (arch + stubs)
- G2: analysis → ready-for-dev
- doc-sync

## Шаг 3. Development (`/develop`)

Для каждой созданной story запусти **dev-lead**:
- G3: ready-for-dev → in-development
- developer (worktree) + security-reviewer
- G4: code checks
- Security review (синхронно)
- kanban → testing (только после security:passed)

## Шаг 4. Testing (`/test all`)

Для каждой story в kanban:testing запусти **qa-lead**:
- Проверка security:passed (КРИТИЧНО)
- G5: testing → ready-to-deploy
- Параллельно: tester (unit tests) + test-case-writer (TC docs)
- kanban → ready-to-deploy (при PASS)

## Шаг 5. Deploy (`/deploy staging`)

Запусти **ops-lead**:
- G6: Pre-flight (ready-to-deploy + qa:passed + security:passed)
- devops: pre-deploy checks + deploy + health + smoke + logs
- G6: Post-deploy
- kanban → done, закрыть issues

---

**Выполняй шаги последовательно.** Каждый следующий шаг запускается после завершения предыдущего.

При BLOCKED на любом gate — остановись и сообщи что заблокировало.
