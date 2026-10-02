# Story #32 — Fix: sell/find модальное окно закрывается при втором поиске

*Создано: 2026-09-30*
*Issue: https://github.com/shakirena/elektron/issues/32*
*Тип: Bug fix (View + JS)*
*Статус: analysis*

---

## Контекст

Модальное окно `#sell-modal` на странице `sell/index` загружает `sell/find` через
`.load()` (см. `web/js/main.js:360-365`). Внутри модалки — `GridView` с `pjax => true`
и фильтрами по name_product, article_number, type, id_contr, barcode, polka.

**Симптом:** при первом поиске всё работает. При втором поиске модалка закрывается,
и пользователь редиректится на `sellHomeUrl()` (через глобальный
`web/js/main.js:98-101` `$("#sell-modal").on('hidden.bs.modal', ...)`).

## Arch Notes

**Root cause (наиболее вероятный):** pjax в `views/sell/find.php` имеет
`enablePushState = true` (дефолт) → при поиске URL хост-страницы меняется на
`/sell/find?...` через `pushState`. Кроме того, `timeout = 1000ms` (дефолт) слишком
короткий — при медленном втором запросе pjax fall-back'ает в
`window.location.href = url` → начинается full-page navigation → Bootstrap Modal
триггерит `hidden.bs.modal` → срабатывает handler `web/js/main.js:98-101`
`window.location.replace(sellHomeUrl())`.

**Сопутствующие риски:**
- `$(document).on('pjax:complete', ...)` в find.php (строки 183-187) — обработчик
  регистрируется на глобальный document при каждом открытии модалки и не снимается
  → потенциальное накопление обработчиков.
- Возможные ID-коллизии `w0-*` между автогенерируемыми виджетами find.php и
  хост-страницы (низкий риск, но hardening желателен).

## Fix

**Изменение 1 (обязательное) — `views/sell/find.php:62-177`:**

- `GridView::widget([..., 'id' => 'grid-find', 'pjaxSettings' => ['options' => ['id' => 'grid-find-pjax', 'enablePushState' => false, 'timeout' => 5000]], ...])`.
- Явные id устраняют коллизии; `enablePushState=false` предотвращает изменение URL
  и navigation fallback; `timeout=5000` исключает случайный fallback при медленных
  запросах.

**Изменение 2 (обязательное) — `views/sell/find.php:181-196`:**

- Заменить `$(document).on('pjax:complete', ...)` на
  `$(document).off('pjax:complete.sell-find').on('pjax:complete.sell-find', ...)`.
- Namespacing предотвращает накопление обработчиков.

**Изменение 3 (опциональное) — `web/js/main.js:98-101`:**

- Guard в `hidden.bs.modal` для `#sell-modal` НЕ нужен, если Изменения 1+2 решают
  проблему.
- Опционально добавить `$(document).off('.sell-find')` перед `window.location.replace`
  как cleanup.
- Defensive-вариант с флагом `__sellModalNavigating` — только если после применения
  Изменений 1+2 симптом воспроизводится.

## Файлы

- Arch doc: `D:\OSPanel\domains\elektron\docs\arch\feature-32-sell-find-modal-pjax.md`
- Изменяются:
  - `D:\OSPanel\domains\elektron\views\sell\find.php`
  - `D:\OSPanel\domains\elektron\web\js\main.js` (опционально)
- Stubs:
  - `C:\Users\User\AppData\Local\Temp\claude\D--OSPanel-domains-elektron\3b9c10b7-6413-4277-a11f-9a004701d5f4\scratchpad\find.php.stub`
  - `C:\Users\User\AppData\Local\Temp\claude\D--OSPanel-domains-elektron\3b9c10b7-6413-4277-a11f-9a004701d5f4\scratchpad\find.php.stub_js`

## Verification

1. Открыть `/sell/index`, кликнуть "Axtarış" (id=`sell_dialog`).
2. Ввести значение в `#name` → Enter → GridView отфильтрован, модалка открыта.
3. Изменить значение в `#name` → Enter → **модалка остаётся открытой**, GridView
   переотрисован.
4. Повторить 5+ раз подряд — модалка не должна закрываться.
5. DevTools Network: только pjax XHR, никакой full-page navigation.
6. Regression: явное закрытие модалки (крестик/Escape/backdrop) по-прежнему
   вызывает `window.location.replace(sellHomeUrl())`.

## Реализация

*Commit: 912cfd3 (2026-09-30)*

### Изменённые файлы

1. `views/sell/find.php` — GridView `id='grid-find'`, pjaxSettings `enablePushState=false enableReplaceState=false timeout=5000`, namespaced `pjax:complete.sellFind` handler
2. `web/js/main.js` — cleanup `$(document).off('.sellFind')` в `hidden.bs.modal` обработчике

### Build

```
D:/OSPanel/modules/php/PHP_7.3-x64/php.exe -l views/sell/find.php → No syntax errors
Autoload OK
```

### Unit tests

Не применимо — pure View/JS фикс без нового PHP service layer.

## Security

Изменения только клиентские (View + JS), не затрагивают auth/RBAC/CSRF/XSS-поверхность.

## 🔒 Security Review (security-reviewer)

**Статус:** PASS ✅
**Дата:** 2026-09-30
**Коммит:** 912cfd3
**Scope:** `views/sell/find.php`, `web/js/main.js`

**Проверено:**
- OWASP A01 Access Control: ✅ нет изменений в контроллерах/RBAC
- OWASP A03 Injection: ✅ нет новых SQL-запросов, нет shell_exec
- OWASP A05 Misconfiguration: ✅ нет secrets; `enablePushState=false` улучшает posture
- OWASP A07 Auth/Session: ✅ CSRF Yii2 не затронут, нет session changes
- OWASP A10 SSRF: ✅ N/A
- PHP XSS: ✅ новая колонка `polka` без `format=>raw`, Yii2 HTML-кодирует по умолчанию
- Open Redirect: ✅ `sellHomeUrl()` возвращает только хардкод `'index'` / `'index-v2'`
- Mass Assignment: ✅ N/A — нет изменений в моделях

**Уязвимости найдены:** нет
**Рекомендации (LOW, не блокируют):** проверить существующие колонки `format=>raw` в find.php в рамках отдельной задачи
**Label:** `security:passed`
