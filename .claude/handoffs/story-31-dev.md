# Handoff: Story #31 Dev Complete

**Story:** #31 — UI отчёта «Движение товара» — контроллер и GridView
**Feature:** #27
**Branch:** feature/27-product-movement-report
**Commit:** c0914af
**Status:** dev-complete → awaiting security review

---

## Изменённые файлы (scope для security review)

### Новые файлы
| Файл | Описание |
|------|----------|
| `controllers/ProductMovementController.php` | Контроллер с actionReport(), AccessControl |
| `views/product-movement/report.php` | GridView-отчёт с формой фильтров |

### Изменённые файлы
| Файл | Описание |
|------|----------|
| `views/layouts/admin.php` | +1 строка: пункт меню «Mal hərəkəti (tam)» |

---

## Build Verification

```
php -l controllers/ProductMovementController.php  → No syntax errors
php -l views/product-movement/report.php          → No syntax errors
php -l views/layouts/admin.php                    → No syntax errors
php -r "require vendor/autoload.php + controller" → Controller OK, Model OK
```

---

## Unit Tests

`tests/codeception/unit/models/ProductMovementSearchTest.php` (7 тест-кейсов, реализованы в story #30):
1. Пустой ArrayDataProvider без id_product
2. validate() false без id_product
3. operationLabels() — 6 типов
4. operationLabels() — русские значения
5. buildUnionSql() — все 6 источников + 5 UNION ALL + плейсхолдеры
6. buildBindings() — корректный маппинг параметров
7. buildBindings() — null для необязательных фильтров

---

## Точки для Security Review

1. **SQL Injection**: SQL строится в `ProductMovementSearch::buildUnionSql()` + параметры через `bindValues()` — конкатенация пользовательского ввода отсутствует. Контроллер не делает никакого raw SQL.

2. **RBAC/AccessControl**: `ProductMovementController::behaviors()` → `AccessControl` с `roles => ['@']`. Неавторизованный запрос → 403/redirect-to-login.

3. **XSS в GridView**: все пользовательские данные (name из БД, operation labels) обёрнуты в `Html::encode()`. Поле `format => 'raw'` используется только для заранее закодированного контента.

4. **Mass Assignment**: контроллер передаёт только `queryParams` в `ProductMovementSearch::search()`. `ProductMovementSearch` — `yii\base\Model` с явными `rules()`, ни одно поле не попадает в код без явного объявления.

5. **Меню**: добавлена только одна статическая строка в массив меню — нет пользовательского ввода.

---

## Rollback

```bash
git revert c0914af --no-edit
# или:
git checkout origin/master -- views/layouts/admin.php
git rm controllers/ProductMovementController.php
git rm -r views/product-movement/
```
