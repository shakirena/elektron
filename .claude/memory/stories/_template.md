# Story #{N}: {Название}

**Feature:** #{F} — {Feature Name} | **Created:** {date}

---

## 📋 Задача (analyst)

**Story:** Как [роль], я хочу [действие], чтобы [ценность].

**AC:** Given [состояние] / When [действие] / Then [результат]

**Компоненты:** backend / frontend / infra
**Зависимости:** Blocked by #N (если есть) / нет
**Размер:** xs / s / m / l
**Приоритет:** critical / high / medium / low

**Stories в feature:**
- #{M1}: [название]
- #{M2}: [название]

---

## 🏗️ Архитектура (architect)

**Паттерн:** [MVC / Service Layer / Repository]

**Новые файлы:**
- `models/{ModelName}.php` — ActiveRecord модель
- `services/{FeatureName}Service.php` — бизнес-логика
- `controllers/{FeatureName}Controller.php` — REST API
- `migrations/m{date}_create_{table}.php` — БД миграция

**Ключевые решения:**
- [ADR суть в одном предложении]

**API endpoints:**
- POST /api/{resource} — [описание]
- GET /api/{resource}/{id} — [описание]

**DB таблицы:** {table_name}(id, field1, created_at, updated_at)

---

## 💻 Реализация (developer)

**Branch:** feature/{P}-{name}
**Commit:** feat(#{N}): [описание]

**Реализовано:**
- [ ] Data layer: модель + миграция
- [ ] Service layer: CRUD
- [ ] Controller: endpoints
- [ ] Unit tests

**Ключевые файлы:** 
**Build:** php yii migrate + codecept run unit — [OK/FAIL]

---

## 🔒 Security Review (security-reviewer)

**Статус:** PASS ✅ / FAIL 🚫
**Дата:** {date}

**Проверено:**
- OWASP A01-A10: ✅
- PHP XSS: ✅
- Mass Assignment: ✅
- SQL Injection: ✅

**Уязвимости:** нет / [список]
**Label:** security:passed / security:failed

---

## ✅ QA (qa-lead)

**Coverage:** N%
**Unit Tests:** N тестов (все PASS / N failed)
**TC Doc:** docs/test-cases/feature-{N}-{name}.md
**Traceability:** обновлено
**E2E:** не автоматизировано / TC-001, TC-003 автоматизированы
