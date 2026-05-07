Создай E2E автотесты. Аргументы: $ARGUMENTS (номер issue или "all")

Запусти субагента **e2e-tester** со следующим заданием:

---

Создай E2E автотесты для: $ARGUMENTS

Если "all" — найди все issues с `kanban:done` у которых есть TC-документы:
```bash
ls docs/test-cases/
```

Выполни E2E Flow:

1. Читай TC-документ: `docs/test-cases/feature-{N}-{name}.md`
2. Определи Critical и High priority TC → обязательно автоматизировать
3. Создай Page Object: `e2e-tests/acceptance/{FeatureName}/{FeatureName}Page.php`
   - ТОЛЬКО `[data-testid='*']` селекторы
4. Создай Cest: `e2e-tests/acceptance/{FeatureName}/{FeatureName}Cest.php`
   - Для каждого Critical TC: Cest метод с `@group critical`
   - Для каждого High TC: Cest метод с `@group high`
5. Если в views нет `data-testid` — создай задачу для developer (не блокируй)
6. Обнови `docs/test-cases/traceability-tc.md` — поставь "E2E Automated: Yes ✅"

**Важно:** E2E тесты НЕ блокируют деплой — это отдельный поток.

Используй Codeception acceptance suite. Page Object pattern обязателен.
