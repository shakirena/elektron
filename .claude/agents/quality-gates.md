# Quality Gates — Shared Protocol

Читается Team Leads напрямую. Не является агентом.

---

## G1: backlog → analysis

**Enforcer:** analysis-lead
**Trigger:** начало `/analyze #N`

### Чеклист G1

- [ ] **Business value**: понятно что пользователь получит (не технический task)
- [ ] **AC присутствуют**: хотя бы один Acceptance Criteria в body issue
- [ ] **Нет дубликатов**: нет открытых issues с похожим названием/scope
  ```bash
  gh issue list --state open --search "{ключевые слова}" --json number,title
  ```
- [ ] **Тип выставлен**: один из `type:feature`, `type:bug`, `type:tech-debt`, `type:spike`
- [ ] **Приоритет выставлен**: один из `priority:critical`, `priority:high`, `priority:medium`, `priority:low`

### Verdicts G1

| Verdict | Условие | Действие |
|---------|---------|----------|
| PASS ✅ | Все 5 пунктов выполнены | Переход в kanban:analysis, продолжить |
| NEEDS WORK ⚠️ | 1-2 пункта не выполнены | Fix inline (добавить метки, AC), повторить |
| BLOCKED 🚫 | Дубликат или полное отсутствие AC/value | Оставить kanban:backlog, comment |

### Report Template G1

```markdown
## Quality Gate G1 Report

**Gate:** G1 (backlog → analysis)
**Issue:** #{N}
**Verdict:** PASS ✅ / NEEDS WORK ⚠️ / BLOCKED 🚫

| Проверка | Статус | Детали |
|----------|--------|--------|
| Business value | ✅/❌ | [детали] |
| AC присутствуют | ✅/❌ | [детали] |
| Нет дубликатов | ✅/❌ | [детали или: нет дубликатов] |
| Тип выставлен | ✅/❌ | type:feature |
| Приоритет выставлен | ✅/❌ | priority:high |

**Действие:** [что сделано / что заблокировало]
```

---

## G2: analysis → ready-for-dev

**Enforcer:** analysis-lead
**Trigger:** после завершения analyst + architect

### Чеклист G2

- [ ] **Spec создан**: `docs/specs/feature-{N}-{name}.md` существует и содержит FR + NFR
- [ ] **Arch создан**: `docs/arch/feature-{N}-{name}.md` существует с ERD и API contracts
- [ ] **SD-1**: каждая story имеет ровно ОДИН Given/When/Then блок
  ```bash
  # Проверь stories (type:story + parent feature #N)
  gh issue list --label "type:story" --json number,title,body
  ```
- [ ] **SD-3**: нет story с `size:xl`
  ```bash
  gh issue list --label "type:story,size:xl" --json number,title
  ```
- [ ] **SD-5**: нет ` и ` или ` and ` в title stories
- [ ] **INVEST**: все stories Independent и Testable
- [ ] **Code stubs**: architect создал stub-файлы

### Verdicts G2

| Verdict | Условие | Действие |
|---------|---------|----------|
| PASS ✅ | Все 7 пунктов выполнены | Переход в kanban:ready-for-dev |
| NEEDS WORK ⚠️ | Мелкие нарушения SD | Попросить analyst/architect фикснуть |
| BLOCKED 🚫 | Spec/arch отсутствует, нарушение SD-1 | Оставить kanban:analysis |

### Report Template G2

```markdown
## Quality Gate G2 Report

**Gate:** G2 (analysis → ready-for-dev)
**Feature:** #{N}
**Verdict:** PASS ✅ / NEEDS WORK ⚠️ / BLOCKED 🚫

| Проверка | Статус | Детали |
|----------|--------|--------|
| Spec создан | ✅/❌ | docs/specs/feature-{N}-{name}.md |
| Arch создан | ✅/❌ | docs/arch/feature-{N}-{name}.md |
| SD-1 (один G/W/T) | ✅/❌ | [перечисли нарушения или "OK"] |
| SD-3 (нет size:xl) | ✅/❌ | [список или "нет"] |
| SD-5 (нет "и"/"and") | ✅/❌ | [список или "OK"] |
| INVEST-compliant | ✅/❌ | [детали] |
| Code stubs | ✅/❌ | [список файлов] |

**Stories созданы:** #{M1}, #{M2}, #{M3}
**Действие:** [что сделано]
```

---

## G3: ready-for-dev → in-development

**Enforcer:** dev-lead
**Trigger:** начало `/develop #N`

### Чеклист G3

- [ ] **Label**: issue имеет `kanban:ready-for-dev`
- [ ] **Spec существует**: `docs/specs/feature-{N}-{name}.md`
- [ ] **Arch существует**: `docs/arch/feature-{N}-{name}.md`
- [ ] **AC testable**: ровно один Given/When/Then блок (SD-1)
- [ ] **No blockers**: нет открытых `Blocked by:` dependencies
  ```bash
  gh issue view #N --json body | grep -E "Blocked by:|Depends on:|Requires:"
  ```
- [ ] **WIP < 5**: не превышен WIP limit
  ```bash
  gh issue list --label "kanban:in-development" --json number | jq length
  ```

### Verdicts G3

| Verdict | Условие | Действие |
|---------|---------|----------|
| PASS ✅ | Все 6 пунктов выполнены | Начать разработку |
| NEEDS WORK ⚠️ | WIP == 5, нет spec | Дождаться или запросить spec |
| BLOCKED 🚫 | Blocker открыт, WIP >= 5 | Skip, уведомить |

### Report Template G3

```markdown
## Quality Gate G3 Report

**Gate:** G3 (ready-for-dev → in-development)
**Story:** #{N}
**Verdict:** PASS ✅ / NEEDS WORK ⚠️ / BLOCKED 🚫

| Проверка | Статус | Детали |
|----------|--------|--------|
| Label kanban:ready-for-dev | ✅/❌ | |
| Spec существует | ✅/❌ | |
| Arch существует | ✅/❌ | |
| AC testable (SD-1) | ✅/❌ | |
| No blockers | ✅/❌ | [список blockers или "нет"] |
| WIP < 5 | ✅/❌ | Текущий WIP: N |
```

---

## G4: in-development → testing (code checks)

**Enforcer:** dev-lead
**Trigger:** после завершения developer

### Чеклист G4

- [ ] **Build OK**:
  ```bash
  php -r "require 'vendor/autoload.php';" && echo "Autoload OK"
  php composer.phar install --no-dev --no-interaction 2>&1
  ```
- [ ] **Lint чист** (если phpcs настроен):
  ```bash
  php vendor/bin/phpcs --standard=PSR12 {changed-files} 2>&1
  ```
- [ ] **Unit tests написаны**: файлы в `tests/unit/` для новых сервисов
  ```bash
  ls tests/unit/services/
  ```
- [ ] **Нет orphaned файлов**: все новые файлы связаны с кодом
- [ ] **Нет TODO/FIXME** в новом коде:
  ```bash
  git diff origin/develop...HEAD | grep -E "^\+.*TODO|^\+.*FIXME"
  ```
- [ ] **Нет debug-кода**: нет var_dump, print_r, die()
  ```bash
  git diff origin/develop...HEAD | grep -E "^\+.*var_dump|^\+.*print_r|^\+.*die\("
  ```

### Verdicts G4

| Verdict | Условие | Действие |
|---------|---------|----------|
| PASS ✅ | Build OK + тесты есть + нет debug | Запустить security-reviewer |
| NEEDS WORK ⚠️ | TODO/FIXME, мелкие issues | Developer фикснет inline |
| BLOCKED 🚫 | Build FAIL | Developer чинит build |

### Report Template G4

```markdown
## Quality Gate G4 Report

**Gate:** G4 (code checks before security review)
**Story:** #{N}
**Verdict:** PASS ✅ / NEEDS WORK ⚠️ / BLOCKED 🚫

| Проверка | Статус | Вывод |
|----------|--------|-------|
| Build OK | ✅/❌ | `[вывод команды]` |
| Lint | ✅/❌/N/A | `[вывод или "не настроен"]` |
| Unit tests написаны | ✅/❌ | [список test-файлов] |
| Нет TODO/FIXME | ✅/❌ | [список или "нет"] |
| Нет debug-кода | ✅/❌ | [список или "нет"] |
```

---

## G5: testing → ready-to-deploy

**Enforcer:** qa-lead
**Trigger:** после завершения tester + test-case-writer

### Чеклист G5

- [ ] **security:passed ОБЯЗАТЕЛЕН**:
  ```bash
  gh issue view #N --json labels | jq '.labels[].name' | grep "security:passed"
  ```
- [ ] **Coverage ≥ 95%** (hotfix: ≥ 50%): из coverage report tester
- [ ] **Все тесты green**: нет failed tests
- [ ] **AC верифицированы**: каждый AC покрыт тестом
- [ ] **TC doc создан**: `docs/test-cases/feature-{N}-{name}.md`
- [ ] **traceability-tc.md обновлён**

### Verdicts G5

| Verdict | Условие | Действие |
|---------|---------|----------|
| PASS ✅ | Все 6 пунктов, security:passed | Переход ready-to-deploy, qa:passed |
| NEEDS WORK ⚠️ | Coverage 90-94%, missing TC | Tester добавляет тесты |
| BLOCKED 🚫 | security:passed отсутствует, coverage < 50% | СТОП, вернуть в development |

**ВАЖНО**: G5 BLOCKED если `security:passed` отсутствует — даже если coverage 100%.

### Report Template G5

```markdown
## Quality Gate G5 Report

**Gate:** G5 (testing → ready-to-deploy)
**Story:** #{N}
**Verdict:** PASS ✅ / NEEDS WORK ⚠️ / BLOCKED 🚫

| Проверка | Статус | Детали |
|----------|--------|--------|
| security:passed label | ✅/🚫 | **КРИТИЧНО** |
| Coverage | ✅/❌ | 97% (порог: 95%) |
| Все тесты green | ✅/❌ | 12/12 passed |
| AC верифицированы | ✅/❌ | AC-1 ✅, AC-2 ✅ |
| TC doc создан | ✅/❌ | docs/test-cases/feature-{N}-{name}.md |
| traceability-tc обновлён | ✅/❌ | |
```

---

## G6: ready-to-deploy → done

**Enforcer:** ops-lead
**Trigger:** после деплоя devops

### Чеклист G6 Pre-deploy

- [ ] `kanban:ready-to-deploy` ✓
- [ ] `qa:passed` ✓
- [ ] `security:passed` ✓
- [ ] Build OK

### Чеклист G6 Post-deploy

- [ ] **Tests green** перед деплоем (devops запустил)
- [ ] **Health check OK**: HTTP 200 от health endpoint
- [ ] **Smoke test OK**: основные страницы отвечают
- [ ] **Logs clean**: нет ERROR/FATAL в первые 60 секунд
- [ ] **Rollback задокументирован** в comment

### Verdicts G6

| Verdict | Условие | Действие |
|---------|---------|----------|
| PASS ✅ | Все 5 post-deploy + rollback | kanban:done, deployed:staging |
| BLOCKED 🚫 | Health FAIL | НЕМЕДЛЕННЫЙ ROLLBACK |

### Report Template G6

```markdown
## Quality Gate G6 Report

**Gate:** G6 (deploy verification)
**Story:** #{N}
**Target:** staging / production
**Verdict:** PASS ✅ / BLOCKED 🚫

| Проверка | Статус | Вывод |
|----------|--------|-------|
| Pre-deploy tests | ✅/❌ | `[вывод codecept run unit]` |
| Deploy | ✅/❌ | [команды выполнены] |
| Health Check | ✅/❌ | HTTP {status} |
| Smoke Test | ✅/❌ | / → 200, /login → 200 |
| Logs (60s) | ✅/❌ | [нет ошибок / список] |
| Rollback готов | ✅ | `[rollback команда]` |
```
