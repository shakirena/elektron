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

## GitHub Repository

- Repo: https://github.com/shakirena/elektron
- Remote: origin → https://github.com/shakirena/elektron.git

## GitHub Project IDs

- PROJECT_ID: PVT_kwHOBagplc4BXEYV
- STATUS_FIELD_ID: PVTSSF_lAHOBagplc4BXEYVzhST3EA
- Board URL: https://github.com/users/shakirena/projects/1
- Status Options:
  - Backlog: db8eef0c
  - Analysis: 446f2234
  - Ready for Dev: 1a6aac8f
  - In Development: 8e28ac78
  - Testing: fcbaa431
  - Ready to Deploy: dccb685c
  - Done: a7553ecc

## Ключевые модули

- Site: контроллер, login/logout, contact form
- Admin: AdminController (минимальный)
