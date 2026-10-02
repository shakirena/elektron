# Architecture: Feature #32 — Fix закрытия модального окна sell/find при втором поиске

*Создано: 2026-09-30*
*Статус: analysis*
*Issue: https://github.com/shakirena/elektron/issues/32*
*Тип: Bug fix (View + JS слой)*

---

## Обзор архитектурного решения

Изменение изолировано в двух файлах:

- `views/sell/find.php` — переконфигурация pjax-настроек GridView и защита от накопления
  обработчиков `pjax:complete`.
- `web/js/main.js` — опциональный guard в обработчике `hidden.bs.modal` для `#sell-modal`
  (см. Root Cause Analysis — в текущей архитектуре обычно достаточно фикса №1).

БД, контроллеры, модели, сервисы, миграции не затрагиваются. Решение чисто клиентское.

---

## Root Cause Analysis

Согласно анализу issue #32 и техническому исследованию, есть три гипотезы о причине
закрытия модалки `#sell-modal` при повторном поиске в `views/sell/find.php`.

### Гипотеза 1 (наиболее вероятная): pjax pushState + timeout fallback

Файл: `views/sell/find.php`, строка 70.

```php
'pjax' => true,
```

`GridView` с `pjax => true` по умолчанию получает конфигурацию:

- `enablePushState = true` → каждый успешный pjax-запрос вызывает `history.pushState` и
  меняет URL хост-страницы `sell/index` на `sell/find?...`.
- `timeout = 1000` (ms) → если ответ сервера дольше 1 сек, pjax падает в fallback
  `window.location.href = url`.

Сценарий воспроизведения:

1. Пользователь открывает модалку → `views/sell/index.php:360` (или `web/js/main.js:360`)
   вызывает `.load('sell/find')` внутри `#sell-modal` через AJAX.
2. Внутри модалки монтируется GridView с pjax-обёрткой (id генерируется автоматически,
   обычно `w0-pjax`).
3. Первый поиск: pjax успешно перехватывает форму фильтров, ответ приходит быстро,
   `pushState` меняет URL на `/sell/find?...` — модалка ещё жива, но URL уже "чужой".
4. Второй поиск: любой из двух сценариев →
   - **(а) timeout fallback**: если запрос дольше `timeout=1000ms`, pjax делает
     `window.location.href = '/sell/find?...'` — начинается полная навигация → браузер
     инициирует unload → Bootstrap Modal получает событие `hidden.bs.modal` через
     нативный dismiss → срабатывает handler `web/js/main.js:98-101`
     `window.location.replace(sellHomeUrl())` → пользователь оказывается на sellHomeUrl.
   - **(б) навигационная рассинхронизация**: pushState-состояние ссылается на URL
     `sell/find`, но DOM-контейнер pjax живёт внутри `#modalContent`, а хост-страница —
     `sell/index`. При повторном pjax некоторые версии Yii2 pjax fall-back'ают в полный
     редирект при рассогласовании pushed URL и текущего DOM-скоупа.

**Оценка вероятности:** ВЫСОКАЯ. Симптом "закрывается на втором поиске, а не первом"
идеально ложится на `pushState → навигация → hidden.bs.modal`.

### Гипотеза 2 (сопутствующая, но не самостоятельная причина): накопление pjax:complete handlers

Файл: `views/sell/find.php`, строки 183-187.

```javascript
$(document).on('pjax:complete', function () {
    $("#name").focus();
});
```

Каждый раз, когда `#sell-modal` заново загружает `sell/find` через `.load()`, `registerJs`
регистрирует новый обработчик `pjax:complete` на `document`. Обработчики **не снимаются**
при закрытии модалки (нет `.off`).

**Оценка влияния:** сам по себе `$("#name").focus()` не закрывает модалку. Но при
повторном открытии модалки количество зарегистрированных на `document` глобальных
обработчиков растёт линейно, и в связке с другими pjax-виджетами на странице может
создать неожиданные побочные эффекты (лишние focus-события, race conditions с фильтрами).

**Действие:** namespaced-подход `.off('pjax:complete.sell-find').on('pjax:complete.sell-find', ...)`
устраняет накопление даже как потенциальный триггер.

### Гипотеза 3: ID collision между GridView в find.php и виджетами в index.php

- `views/sell/index.php:206`: `Pjax::begin(['id' => 'grid-arrival'])` — явный id.
- `views/sell/index.php:210`: `GridView::widget([...])` — БЕЗ `pjax => true`, id
  автогенерируется (обычно `w0`).
- `views/sell/find.php:62`: `GridView::widget([..., 'pjax' => true, ...])` — id
  автогенерируется. При `renderAjax` внутри `.load()` счётчик виджетов Yii2 сбрасывается,
  поэтому find.php получает `w0`, `w0-pjax`, `w0-filters`.

**Проверка:** `views/sell/index.php` содержит два `Pjax::begin` — `grid-arrival` (строка 206)
и `grid-update` (строка 367), оба с явными id. Внешние GridView внутри них без явного
`pjax => true`. Явных коллизий с `w0-*` из find.php не найдено.

**НО:** kartik\grid\GridView создаёт свои Pjax-обёртки поверх собственного id, и при
идентичных автогенерируемых id (`w0`) события pjax теоретически могут таргетировать
обёртку хост-страницы вместо модалочной. Присвоение явного `id => 'grid-find'` в
find.php устраняет любую двусмысленность.

**Оценка влияния:** НИЗКАЯ, но устранение стоит копейки.

### Вывод

Основная причина — Гипотеза 1 (pushState + timeout fallback → navigation →
`hidden.bs.modal` → `window.location.replace(sellHomeUrl())`). Гипотезы 2 и 3 —
сопутствующие риски и hardening. Все три устраняем в одном патче.

---

## Решение

### Изменение 1: views/sell/find.php — явные pjax-настройки GridView

**Что меняем:**

- Добавляем `'id' => 'grid-find'` на GridView.
- Добавляем `'pjaxSettings'` с ключевыми полями:
  - `'enablePushState' => false` — URL хост-страницы `sell/index` не должен меняться,
    когда пользователь ищет товар в модалке.
  - `'timeout' => 5000` — исключаем fallback в `window.location.href` при
    медленных запросах (типичный запрос find возвращается ~200-500ms, но при большом
    каталоге и slow-network 1000ms недостаточно).
  - `'options' => ['id' => 'grid-find-pjax']` — явный id pjax-контейнера, чтобы события
    гарантированно таргетировали GridView внутри модалки.
- На первом столбце (`name_product`) уже есть явный `'filterInputOptions' => ['id'=>'name', ...]`
  (строка 96). Дополнительно проверяем, что Select2-фильтры (`type`, `id_contr`) не
  генерируют коллизий с виджетами хост-страницы. Явно назначаем `id` для их
  filterInputOptions — `type-filter` и `id_contr-filter`. Это опционально, но
  желательно для устранения зависимости от порядка auto-id.

**Что НЕ меняем:**

- Схему фильтров, набор колонок, форматирование строк.
- `SellController::actionFind()` — контроллер по-прежнему отдаёт `renderAjax`.

### Изменение 2: views/sell/find.php — намespacing pjax:complete handler

**Что меняем:**

Заменяем блок registerJs (строки 181-196):

```javascript
$(document).on('pjax:complete', function () {
    $("#name").focus();
});
```

на:

```javascript
$(document).off('pjax:complete.sell-find')
           .on('pjax:complete.sell-find', function () {
               $("#name").focus();
           });
```

Namespaced-событие `.sell-find` позволяет снять только "свой" обработчик при повторной
регистрации, не трогая обработчики других виджетов.

Дополнительно рекомендуется в `web/js/main.js` при `hidden.bs.modal` для `#sell-modal`
вызывать `$(document).off('.sell-find')` — но это опционально (см. Изменение 3).

### Изменение 3 (опциональное): web/js/main.js — guard на `hidden.bs.modal`

**Контекст:** текущий код `web/js/main.js:98-101`:

```javascript
$("#sell-modal").on('hidden.bs.modal',function(){
    window.location.replace(sellHomeUrl())
});
```

Этот обработчик — принудительный редирект на "домашний" URL продажи. Он корректен
для явного закрытия модалки пользователем (клик по крестику, backdrop, Escape), НО он
же и есть финальное звено бага: когда pushState/pjax-fallback инициирует полную
навигацию, Bootstrap Modal триггерит `hidden.bs.modal`, и мы делаем `location.replace`
поверх уже начавшейся навигации.

**Решение (опция A — предпочтительно):** Изменения 1 и 2 устраняют первопричину, и
`hidden.bs.modal` больше никогда не должен срабатывать из-за pjax. Guard не нужен.

**Решение (опция B — defensive):** добавить guard, различающий "пользовательский
dismiss" и "навигационный dismiss". Реализация:

```javascript
var __sellModalNavigating = false;

$(document).on('pjax:beforeSend', '#grid-find-pjax', function() {
    __sellModalNavigating = false; // pjax успешен → это НЕ навигация
});

$("#sell-modal").on('hidden.bs.modal', function(){
    if (__sellModalNavigating) return; // page unload/navigation — не редиректим
    window.location.replace(sellHomeUrl());
});

$(window).on('beforeunload', function() {
    __sellModalNavigating = true;
});
```

**Решение (опция C — снятие namespaced handler):** в `hidden.bs.modal` добавить
`$(document).off('.sell-find')` перед `window.location.replace`, чтобы обработчики
find.php снимались при закрытии модалки.

**Рекомендация:** применить опцию A (не трогать main.js) + опцию C как cleanup. Если
после патча симптом воспроизводится — вернуться к опции B.

---

## ERD

N/A. Схема БД не изменяется.

---

## API Contracts

### SellController::actionFind()

Без изменений.

- **Route:** `GET /sell/find`
- **Auth:** аутентифицированный пользователь (существующие фильтры контроллера).
- **Response:** `renderAjax('find', [...])` возвращает HTML-фрагмент с GridView внутри.
- **Модель:** параметры фильтров через `$searchModel->search(Yii::$app->request->queryParams)`.

Изменения касаются только View-слоя (`views/sell/find.php`) и клиентского JS
(`web/js/main.js`, опционально).

---

## ADR-1: enablePushState=false для pjax внутри модальных окон

**Статус:** Принято.

**Контекст:** GridView в `views/sell/find.php` встраивается в модальное окно `#sell-modal`
на странице `sell/index`. Модалка загружается через `.load('sell/find')` — то есть
URL хост-страницы остаётся `sell/index`, а внутри модалки содержимое приходит от
эндпойнта `sell/find`. С pjax `enablePushState=true` (default) при каждом поиске
URL меняется на `sell/find?...`, что:

1. Ломает семантику "модалка — оверлей над текущей страницей": пользователь видит
   URL `sell/index`, но после поиска URL уже `sell/find`, и обновление F5 приведёт
   на HTML-страницу find вне контекста sell/index.
2. Создаёт условия для pjax-fallback в `window.location.href` при timeout, что
   инициирует полную навигацию и срабатывание глобальных `hidden.bs.modal` handlers.

**Решение:** для pjax-виджетов, встраиваемых внутрь модалок, явно ставим
`enablePushState=false` в `pjaxSettings`. URL не меняется, навигационные fallback
исключены.

**Последствия:**

- (+) URL хост-страницы стабилен, F5 предсказуем.
- (+) `hidden.bs.modal` больше не срабатывает из-за pjax → модалка не закрывается.
- (+) Меньше связности между pjax и Bootstrap Modal lifecycle.
- (-) Пользователь не может "поделиться URL с фильтром" — но это и не требуется
      для сценария поиска товара в модалке продажи.
- (-) Кнопки "назад/вперёд" браузера не будут переключать состояния фильтра
      GridView в модалке — приемлемо, т.к. модалка эфемерна.

## ADR-2: timeout=5000 для pjax find

**Статус:** Принято.

**Контекст:** дефолтный `timeout=1000ms` в yii2 pjax слишком агрессивный для запросов
с большим каталогом товаров. При медленном ответе pjax падает в full-page navigation.

**Решение:** `timeout=5000ms`. Это буфер, покрывающий типичные ответы `sell/find` для
складов до ~50k товаров.

**Последствия:**

- (+) Устраняет случайные full-page fallback.
- (-) При реальной проблеме на сервере пользователь ждёт до 5с — приемлемо, т.к.
      альтернатива (случайное закрытие модалки) хуже.

---

## Code Stubs

Заглушки с ключевыми точками изменений размещены в scratchpad:

- `C:\Users\User\AppData\Local\Temp\claude\D--OSPanel-domains-elektron\3b9c10b7-6413-4277-a11f-9a004701d5f4\scratchpad\find.php.stub`
- `C:\Users\User\AppData\Local\Temp\claude\D--OSPanel-domains-elektron\3b9c10b7-6413-4277-a11f-9a004701d5f4\scratchpad\find.php.stub_js`

---

## Verification Plan

### Ручное воспроизведение (before)

1. Открыть `/sell/index` под user с ролью != 1.
2. Кликнуть кнопку "Axtarış" (id=`sell_dialog`).
3. Ввести в поле имени `#name` первую строку поиска (например "гвозд") → Enter.
4. GridView отфильтрован, модалка ещё открыта — OK.
5. Изменить текст в `#name` на другое значение → Enter (второй поиск).
6. **Bug:** модалка закрывается, происходит редирект на `sellHomeUrl()`.

### Проверка фикса (after)

1. Применить изменения из `find.php.stub` (в файл `views/sell/find.php`).
2. Перезагрузить `/sell/index`.
3. Повторить шаги 1-5 из "before". Ожидается: модалка остаётся открытой, GridView
   переотрисован с новыми результатами, URL хост-страницы остаётся `/sell/index`.
4. Повторить поиск 5+ раз подряд — модалка не должна закрываться.
5. Явное закрытие модалки (крестик/Escape/backdrop) → должен произойти
   `window.location.replace(sellHomeUrl())` (регресс: не сломать pjax-независимый
   dismiss).
6. Проверить DevTools Network: при каждом поиске должен быть один pjax XHR к
   `/sell/find`, без full-page navigation.
7. Проверить DevTools Console: не должно быть ошибок JS и накопления warnings.

### Автотесты

Ручной bug fix в View-слое; автотесты Codeception acceptance-suite могут быть
добавлены позже (не входит в scope этого arch-документа).

### Regression check

- Проверить, что `hidden.bs.modal` для `#sell-modal` по-прежнему делает
  `window.location.replace(sellHomeUrl())` при явном закрытии модалки
  (см. `web/js/main.js:98-101`).
- Проверить, что pjax других виджетов на странице (`grid-arrival`, `grid-update`)
  не задет — они не использовали namespaced handler `.sell-find`, поэтому namespacing
  в find.php их не касается.

---

## Зависимости между Stories

Этот баг-фикс — одна атомарная задача, декомпозиция на sub-stories не требуется.

---

## Security Considerations

- [x] Изменения не затрагивают auth/RBAC.
- [x] Никаких новых пользовательских input'ов — только конфигурация существующего
      GridView.
- [x] `enablePushState=false` не влияет на CSRF/XSS-поверхность (pjax по-прежнему
      использует стандартную Yii2 CSRF-защиту).
- [x] `timeout=5000` не создаёт DoS-риска — это клиентский timeout, сервер продолжает
      работать по своим лимитам.

---

## Риски

| Риск | Вероятность | Митигация |
|------|-------------|-----------|
| Гипотеза 1 не единственная причина, и фикс не устранит симптом | low | Verification Plan шаг 6 — DevTools; при воспроизведении — применить опцию B из Изменения 3 |
| `enablePushState=false` сломает какой-то workflow, где полагаются на URL | very low | Модалка эфемерна, workflow "поделиться URL фильтра" не поддерживается |
| Явный `id => 'grid-find'` конфликтует с уже существующим DOM-элементом | very low | grep по проекту — id `grid-find` не используется |
| Namespaced `.off('pjax:complete.sell-find')` случайно снимет чужой handler | none | Namespace уникальный |

---

## Изменяемые файлы

- `D:\OSPanel\domains\elektron\views\sell\find.php` (строки 62-177 и 181-196)
- `D:\OSPanel\domains\elektron\web\js\main.js` (строки 98-101 — опционально, только
  если после применения фикса симптом воспроизводится)

## Не изменяемые файлы (проверены на связанность)

- `D:\OSPanel\domains\elektron\controllers\SellController.php` (actionFind)
- `D:\OSPanel\domains\elektron\views\sell\index.php` (хост-страница модалки)
- Модели, миграции, БД
