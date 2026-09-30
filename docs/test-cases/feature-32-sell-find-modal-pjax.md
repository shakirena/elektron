# TC-документация: Feature #32 — Fix sell/find modal pjax

**Feature:** #32  
**Story:** #33 — Исправить закрытие sell/find модалки при повторном pjax-поиске  
**Дата:** 2026-09-30  
**Статус:** Draft  
**Automated:** Нет (изменения в View/JS)

---

## Описание изменений

- `views/sell/find.php`: GridView id='grid-find', pjaxSettings (id='grid-find-pjax', enablePushState=false, enableReplaceState=false, timeout=5000), namespaced handler `$(document).off('pjax:complete.sellFind').on('pjax:complete.sellFind', ...)`
- `web/js/main.js`: `$(document).off('.sellFind')` перед `window.location.replace(sellHomeUrl())` в обработчике `hidden.bs.modal` у `#sell-modal`

---

## AC-1: ≥3 поисков подряд — модалка остаётся открытой

### TC-32-001 — Happy Path: три поиска подряд, модалка не закрывается

**Type:** Happy Path  
**Priority:** Critical  
**Automated:** No

**Preconditions:**
- Пользователь авторизован (роль '@' — кассир)
- На странице `sell/index` есть открытая продажа
- В базе есть товары с разными названиями

**Steps:**
1. Открыть `sell/index`
2. Нажать кнопку «Axtarış» — открывается модалка `#sell-modal`
3. В поле `#name` ввести поисковый запрос 1 (например «чай»), нажать Enter или кнопку поиска
4. Дождаться обновления результатов в GridView
5. В поле `#name` ввести поисковый запрос 2 (например «кофе»), нажать Enter
6. Дождаться обновления результатов
7. В поле `#name` ввести поисковый запрос 3 (например «сахар»), нажать Enter
8. Дождаться обновления результатов

**Expected Result:**
- После каждого поиска модалка `#sell-modal` остаётся открытой
- Результаты в GridView `#grid-find` обновляются без перезагрузки страницы
- URL страницы остаётся `sell/index` без дополнительных параметров
- Нет дублирования обработчиков pjax

---

### TC-32-002 — Error Case: второй поиск при медленном сервере (timeout > 1с)

**Type:** Error Case  
**Priority:** High  
**Automated:** No

**Preconditions:**
- Пользователь авторизован
- На странице `sell/index` открыта модалка поиска
- Сетевая задержка сервера > 1 секунды (можно эмулировать через DevTools → Network → Throttling «Slow 3G»)

**Steps:**
1. Открыть `sell/index`, открыть модалку `#sell-modal`
2. В DevTools → Network установить throttling «Slow 3G»
3. Ввести поисковый запрос 1 и нажать Enter
4. До получения ответа ввести поисковый запрос 2
5. Дождаться окончания обоих запросов

**Expected Result:**
- Модалка не закрывается ни во время, ни после загрузки
- pjax timeout=5000 (5 секунд) — запрос не обрывается преждевременно при задержке < 5с
- Результаты отображаются корректно после завершения последнего запроса

---

## AC-2: Фокус возвращается в поле #name после поиска

### TC-32-003 — Happy Path: фокус в #name после pjax-обновления

**Type:** Happy Path  
**Priority:** Medium  
**Automated:** No

**Preconditions:**
- Пользователь авторизован
- Открыта модалка поиска `#sell-modal`

**Steps:**
1. Ввести поисковый запрос в поле `#name`
2. Нажать Enter или кнопку поиска
3. После завершения pjax-запроса (обновление GridView) не кликать мышью

**Expected Result:**
- Фокус автоматически перемещается в поле `#name`
- Пользователь может сразу начать вводить следующий запрос без лишних кликов

---

### TC-32-004 — Edge Case: фокус после нескольких поисков подряд

**Type:** Edge Case  
**Priority:** Medium  
**Automated:** No

**Preconditions:**
- Пользователь авторизован
- Открыта модалка поиска

**Steps:**
1. Выполнить поиск 1 → убедиться, что фокус в `#name`
2. Выполнить поиск 2 → убедиться, что фокус в `#name`
3. Выполнить поиск 3 → убедиться, что фокус в `#name`

**Expected Result:**
- После каждого из трёх поисков фокус стабильно возвращается в `#name`
- Нет ситуации, когда обработчик `pjax:complete.sellFind` срабатывает несколько раз (предотвращается через `.off` перед `.on`)

---

## AC-3: addSell работает после нескольких поисков

### TC-32-005 — Happy Path: добавление товара в чек после 3 поисков

**Type:** Happy Path  
**Priority:** Critical  
**Automated:** No

**Preconditions:**
- Пользователь авторизован
- На странице `sell/index` есть активный чек
- В базе есть товары

**Steps:**
1. Открыть модалку поиска
2. Выполнить поиск 1, поиск 2, поиск 3
3. В результатах поиска 3 кликнуть по строке товара (обработчик `addSell`)

**Expected Result:**
- Товар добавляется в чек (`#grid-arrival` обновляется)
- Модалка закрывается после добавления (штатное поведение `addSell`)
- Данные товара корректны (не «замёрзли» от первого поиска)

---

### TC-32-006 — Error Case: addSell без предварительного поиска

**Type:** Error Case  
**Priority:** Low  
**Automated:** No

**Preconditions:**
- Пользователь авторизован
- Открыта модалка поиска, GridView пустой или показывает дефолтные результаты

**Steps:**
1. Открыть модалку без выполнения поиска
2. Если в GridView есть товары — кликнуть по строке

**Expected Result:**
- Штатное поведение — товар добавляется корректно
- Нет ошибок в консоли браузера

---

## AC-4: URL хост-страницы не меняется на sell/find?...

### TC-32-007 — Happy Path: URL не изменяется при поиске

**Type:** Happy Path  
**Priority:** Critical  
**Automated:** No

**Preconditions:**
- Пользователь авторизован
- Открыта страница `sell/index`

**Steps:**
1. Запомнить текущий URL (должен быть `sell/index` или аналог)
2. Открыть модалку поиска
3. Выполнить поиск
4. Проверить URL в адресной строке браузера

**Expected Result:**
- URL остаётся `sell/index` (или `sell/index-v2`)
- В URL нет параметров `?r=sell/find`, `?name=...` и т.п.
- Атрибуты `enablePushState=false` и `enableReplaceState=false` предотвращают изменение URL

---

### TC-32-008 — Error Case: нет pushState в DevTools → Application → History

**Type:** Error Case  
**Priority:** High  
**Automated:** No

**Preconditions:**
- Пользователь авторизован
- Открыта страница `sell/index`
- DevTools открыты, вкладка Application → Session Storage / History

**Steps:**
1. Открыть DevTools → Network (для наблюдения за запросами)
2. Открыть модалку поиска
3. Выполнить 3 поиска подряд
4. Проверить DevTools → Application → Session Storage / History API calls

**Expected Result:**
- В DevTools Network виден только XHR/Fetch запрос к `sell/find`, не full-page navigation
- Кнопка «Назад» в браузере не создаёт лишних записей истории (нет вызовов `history.pushState`)
- `enablePushState=false` и `enableReplaceState=false` подтверждены наблюдением

---

## AC-5: Закрытие модалки работает корректно

### TC-32-009 — Happy Path: закрытие крестиком/Esc → location.replace

**Type:** Happy Path  
**Priority:** Critical  
**Automated:** No

**Preconditions:**
- Пользователь авторизован
- Открыта страница `sell/index`, открыта модалка поиска

**Steps:**
1. Открыть модалку поиска
2. Выполнить 1–2 поиска
3. Нажать крестик модалки (×) или клавишу Esc

**Expected Result:**
- Модалка закрывается
- Срабатывает `window.location.replace(sellHomeUrl())` — страница переходит на `sellHomeUrl()`
- Страница обновляется / переходит на нужный URL

---

### TC-32-010 — Happy Path: закрытие кликом вне модалки

**Type:** Happy Path  
**Priority:** High  
**Automated:** No

**Preconditions:**
- Пользователь авторизован
- Открыта модалка поиска `#sell-modal`

**Steps:**
1. Кликнуть за пределами модалки (на backdrop)

**Expected Result:**
- Модалка закрывается
- `window.location.replace(sellHomeUrl())` отрабатывает корректно

---

### TC-32-011 — Error Case: pjax-событие не вызывает redirect при открытой модалке

**Type:** Error Case  
**Priority:** High  
**Automated:** No

**Preconditions:**
- Пользователь авторизован
- Открыта модалка поиска
- Выполняется pjax-поиск

**Steps:**
1. Открыть модалку
2. Выполнить поиск (pjax-запрос)
3. Наблюдать за поведением страницы во время и после pjax-запроса

**Expected Result:**
- Во время pjax-запроса страница не редиректируется
- `window.location.replace(sellHomeUrl())` вызывается только на событие `hidden.bs.modal`, а не на `pjax:complete`
- Обработчик `.sellFind` снимается (`$(document).off('.sellFind')`) в `hidden.bs.modal` — нет утечки обработчиков

---

### TC-32-012 — Error Case: повторное открытие модалки не дублирует redirect

**Type:** Error Case  
**Priority:** Medium  
**Automated:** No

**Preconditions:**
- Пользователь авторизован
- Открыта страница `sell/index`

**Steps:**
1. Открыть модалку → закрыть крестиком (наблюдать redirect)
2. Вернуться на `sell/index` (если произошёл redirect)
3. Снова открыть модалку → выполнить поиск → закрыть крестиком

**Expected Result:**
- При каждом закрытии модалки `location.replace` вызывается ровно один раз
- Нет многократных переходов или бесконечного редиректа

---

## AC-6: Поведение одинаково на sell/index и sell/index-v2

### TC-32-013 — Happy Path: AC-1..AC-5 на sell/index-v2

**Type:** Happy Path  
**Priority:** High  
**Automated:** No

**Preconditions:**
- Пользователь авторизован
- Открыта страница `sell/index-v2`

**Steps:**
1. Повторить шаги TC-32-001 (3 поиска подряд) на `sell/index-v2`
2. Повторить шаги TC-32-003 (проверка фокуса) на `sell/index-v2`
3. Повторить шаги TC-32-005 (addSell) на `sell/index-v2`
4. Повторить шаги TC-32-007 (URL не меняется) на `sell/index-v2`
5. Повторить шаги TC-32-009 (закрытие крестиком) на `sell/index-v2`

**Expected Result:**
- Все результаты идентичны поведению на `sell/index`
- Модалка работает стабильно на обеих версиях страницы

---

## RBAC

### TC-32-RBAC-1 — RBAC: гость не видит модалку поиска

**Type:** RBAC  
**Priority:** High  
**Automated:** No

**Preconditions:**
- Пользователь НЕ авторизован (гость / logout)

**Steps:**
1. Открыть `sell/index` в браузере без авторизации
2. Попытаться перейти напрямую на `sell/find`

**Expected Result:**
- Без авторизации — редирект на страницу `login`
- AccessControl в `SellController` не даёт доступа гостю
- Модалка поиска недоступна

---

### TC-32-RBAC-2 — RBAC: кассир с ролью '@' имеет доступ к поиску

**Type:** RBAC  
**Priority:** High  
**Automated:** No

**Preconditions:**
- Пользователь авторизован с ролью '@' (кассир)

**Steps:**
1. Открыть `sell/index`
2. Открыть модалку поиска
3. Выполнить поиск товара

**Expected Result:**
- Поиск работает корректно
- GridView отображает результаты
- Нет ошибки 403 или редиректа

---

## Сводная таблица тест-кейсов

| TC ID | Название | AC | Priority | Type | Automated |
|-------|----------|----|----------|------|-----------|
| TC-32-001 | 3 поиска подряд — модалка не закрывается | AC-1 | Critical | Happy Path | No |
| TC-32-002 | Второй поиск при медленном сервере | AC-1 | High | Error Case | No |
| TC-32-003 | Фокус в #name после pjax | AC-2 | Medium | Happy Path | No |
| TC-32-004 | Фокус после нескольких поисков подряд | AC-2 | Medium | Edge Case | No |
| TC-32-005 | addSell после 3 поисков добавляет товар | AC-3 | Critical | Happy Path | No |
| TC-32-006 | addSell без предварительного поиска | AC-3 | Low | Error Case | No |
| TC-32-007 | URL не меняется при поиске | AC-4 | Critical | Happy Path | No |
| TC-32-008 | Нет pushState в DevTools History | AC-4 | High | Error Case | No |
| TC-32-009 | Крестик/Esc → location.replace | AC-5 | Critical | Happy Path | No |
| TC-32-010 | Закрытие кликом вне модалки | AC-5 | High | Happy Path | No |
| TC-32-011 | pjax-событие не вызывает redirect | AC-5 | High | Error Case | No |
| TC-32-012 | Повторное открытие — нет дублирования redirect | AC-5 | Medium | Error Case | No |
| TC-32-013 | AC-1..AC-5 на sell/index-v2 | AC-6 | High | Happy Path | No |
| TC-32-RBAC-1 | Гость не видит модалку поиска | AC-6/RBAC | High | RBAC | No |
| TC-32-RBAC-2 | Кассир '@' имеет доступ к поиску | AC-6/RBAC | High | RBAC | No |
