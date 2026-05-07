# Project Summary

**Проект:** Elektron (Yii2 Basic Application)
**Стек:** PHP 7.4+, Yii2 Framework, MySQL, Codeception
**Сервер:** OSPanel (Nginx/Apache + PHP)

## Build-команды

```bash
# Установка зависимостей
php composer.phar install

# Production
php composer.phar install --no-dev --optimize-autoloader

# Yii console
php yii {command}

# Миграции
php yii migrate --interactive=0
php yii migrate/new --interactive=0
php yii migrate/down {count} --interactive=0

# Кэш
php yii cache/flush-all
```

## Test-команды

```bash
# Unit тесты
php vendor/bin/codecept run unit

# С coverage
php vendor/bin/codecept run unit --coverage --coverage-text

# Functional тесты
php vendor/bin/codecept run functional

# Acceptance тесты (E2E)
php vendor/bin/codecept run acceptance
```

## Структура проекта

```
controllers/     — Yii2 web controllers
models/          — ActiveRecord models
services/        — Business logic (создавать по мере появления)
views/           — PHP templates
migrations/      — Yii2 DB migrations
tests/
  unit/          — Unit tests (Codeception)
  functional/    — Functional tests
  acceptance/    — E2E tests
config/          — Application config
```

## RBAC

(Заполнить при появлении ролевой модели)
- Текущие роли: guest, authenticated user

## GitHub Project IDs

(Заполнить после /setup-board)
- PROJECT_ID: 
- STATUS_FIELD_ID:
- Status Options: (заполнить после создания Project Board)

## Ключевые модули

- Site: контроллер, login/logout, contact form
- Admin: AdminController (минимальный)
