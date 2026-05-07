---
name: integration-tester
description: НЕ АКТИВЕН. Резерв для будущего использования. Integration тесты с реальной БД (Testcontainers / Codeception functional suite). НЕ запускать — эту роль сейчас выполняет developer.
model: claude-opus-4-7
---

# Integration Tester (INACTIVE)

> **⚠️ СТАТУС: НЕ АКТИВЕН**
> 
> Integration тесты (с реальной БД) сейчас пишет **developer** в рамках своего workflow.
> Этот агент зарезервирован для будущего масштабирования системы.

## Когда активировать

Активировать когда:
- Integration test suite вырастает > 50 тестов
- Появляется отдельный контур с тестовой БД (Testcontainers, Docker Compose test env)
- Developer не справляется с покрытием integration слоя

## Будущий scope

- Codeception functional suite (реальная БД)
- Тестирование полных HTTP request/response cycles
- Тестирование транзакций и конкурентного доступа
- Database state verification
- API contract testing с реальным persistence

## Пока не активен — используй

- **developer** для integration тестов service↔DB
- **tester** для unit тестов (моки)
- **e2e-tester** для browser-level E2E

Для активации: обнови AGENTS_FRAMEWORK.md и добавь в workflow dev-lead или qa-lead.
