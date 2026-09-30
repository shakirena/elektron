# Story #33 — Исправить закрытие sell/find модалки при повторном pjax-поиске

*Создано: 2026-09-30*
*Issue: https://github.com/shakirena/elektron/issues/33*
*Тип: Bug fix (View + JS)*
*Статус: ready-to-deploy*
*Parent Feature: #32*

---

## Контекст

Story реализует фикс модального окна `#sell-modal` на страницах `sell/index` и
`sell/index-v2`. При втором (и последующем) поиске через pjax-GridView внутри
модалки — модалка закрывалась, кассир перенаправлялся на sellHomeUrl().

## Изменённые файлы (Commit: 912cfd3)

1. `views/sell/find.php` — явные id GridView и pjax-контейнера, запрет pushState/replaceState, увеличенный timeout, namespaced handler
2. `web/js/main.js` — cleanup namespaced handler при закрытии модалки

## Fix (детали)

- `'id' => 'grid-find'` — явный id, устраняет коллизию w0-*
- `'id' => 'grid-find-pjax'` — явный id pjax-контейнера
- `enablePushState => false` — URL хост-страницы sell/index не меняется
- `enableReplaceState => false` — полная блокировка history API
- `timeout => 5000` — исключает fallback в window.location.href
- `$(document).off('pjax:complete.sellFind').on('pjax:complete.sellFind', ...)` — namespaced handler предотвращает накопление
- `$(document).off('.sellFind')` в hidden.bs.modal — cleanup при закрытии модалки

## Security Review

**Статус:** PASS ✅ (label `security:passed` выставлен dev-lead)
**Коммит:** 912cfd3
Изменения чисто клиентские — не затрагивают auth/RBAC/CSRF/XSS-поверхность.

## QA

**Статус:** PASS ✅
**Дата:** 2026-09-30
**Coverage:** 100% изменённых файлов (view + JS file-content assertions)
**Тесты:** 10/10 PASS, 22 assertions (время: 303ms)
**Файл тестов:** `tests/codeception/unit/models/SellFindPjaxConfigTest.php`
**TC-документация:** `docs/test-cases/feature-32-sell-find-modal-pjax.md` (15 TC-кейсов, AC-1..AC-6 + RBAC)
**Traceability:** `docs/test-cases/traceability-tc.md` — добавлена секция Feature #32

## Kanban

- Commit: 912cfd3 (2026-09-30) — dev complete
- QA: 2026-09-30 → PASS
- Статус: `kanban:ready-to-deploy`, `qa:passed`
- G5 Report: https://github.com/shakirena/elektron/issues/33#issuecomment-5908914142
