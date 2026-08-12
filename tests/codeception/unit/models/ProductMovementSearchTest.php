<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;

/**
 * Unit-тесты для Story #30: ProductMovementSearch — UNION по всем источникам.
 * Feature #27: Отчёт «Движение товара».
 *
 * Подход: чистые unit-тесты без обращения к БД.
 *
 * Тесты 1-2: проверяют guard-логику (пустой provider при отсутствии id_product).
 * Тесты 3-4: проверяют статические данные operationLabels().
 * Тесты 5-6: используют ReflectionMethod для доступа к protected-методам
 *             buildUnionSql() и buildBindings().
 */
class ProductMovementSearchTest extends TestCase
{
    // ------------------------------------------------------------------
    // Хелперы
    // ------------------------------------------------------------------

    /**
     * Возвращает абсолютный путь к модели ProductMovementSearch.php.
     *
     * @return string
     */
    private function modelFilePath()
    {
        // tests/codeception/unit/models/ → project root (4 уровня вверх)
        return dirname(__DIR__, 4) . '/models/ProductMovementSearch.php';
    }

    // ------------------------------------------------------------------
    // Тест 1: search() без id_product → пустой ArrayDataProvider
    // ------------------------------------------------------------------

    /**
     * AC: Если id_product не передан, search() возвращает ArrayDataProvider
     * с пустым allModels, не обращаясь к БД.
     */
    public function testEmptyDataProviderWhenIdProductMissing()
    {
        $model    = new \app\models\ProductMovementSearch();
        $provider = $model->search([]);

        $this->assertInstanceOf(
            \yii\data\ArrayDataProvider::class,
            $provider,
            'search() должен возвращать ArrayDataProvider'
        );
        $this->assertEmpty(
            $provider->allModels,
            'allModels должен быть пустым, когда id_product не задан'
        );
    }

    // ------------------------------------------------------------------
    // Тест 2: validate() без id_product → false
    // ------------------------------------------------------------------

    /**
     * AC: Атрибут id_product объявлен обязательным — validate() без него
     * возвращает false.
     */
    public function testRulesRequireIdProduct()
    {
        $model = new \app\models\ProductMovementSearch();
        // id_product не задан (null по умолчанию)
        $valid = $model->validate();

        $this->assertFalse(
            $valid,
            'validate() должен возвращать false, когда id_product не задан'
        );
        $this->assertNotEmpty(
            $model->getErrors('id_product'),
            'Должна быть ошибка валидации на атрибуте id_product'
        );
    }

    // ------------------------------------------------------------------
    // Тест 3: operationLabels() — ровно 6 типов
    // ------------------------------------------------------------------

    /**
     * AC: operationLabels() содержит ровно 6 ключей (по одному на каждый
     * источник UNION: arrival, sell, sell2, return_client, return_supplier,
     * sverka).
     */
    public function testOperationLabelsHasSixTypes()
    {
        $labels = \app\models\ProductMovementSearch::operationLabels();

        $this->assertIsArray($labels, 'operationLabels() должен возвращать массив');
        $this->assertCount(
            6,
            $labels,
            'operationLabels() должен содержать ровно 6 элементов'
        );
    }

    // ------------------------------------------------------------------
    // Тест 4: operationLabels() — русские значения
    // ------------------------------------------------------------------

    /**
     * AC: operationLabels() содержит читаемые русскоязычные метки
     * для каждого типа операции.
     */
    public function testOperationLabelsRussianValues()
    {
        $labels = \app\models\ProductMovementSearch::operationLabels();

        $this->assertArrayHasKey('arrival', $labels);
        $this->assertArrayHasKey('sell', $labels);
        $this->assertArrayHasKey('sell2', $labels);
        $this->assertArrayHasKey('return_client', $labels);
        $this->assertArrayHasKey('return_supplier', $labels);
        $this->assertArrayHasKey('sverka', $labels);

        $this->assertSame('Приход', $labels['arrival']);
        $this->assertSame('Продажа', $labels['sell']);
        $this->assertSame('Продажа (опт)', $labels['sell2']);
        $this->assertSame('Возврат от клиента', $labels['return_client']);
        $this->assertSame('Возврат поставщику', $labels['return_supplier']);
        $this->assertSame('Сверка', $labels['sverka']);
    }

    // ------------------------------------------------------------------
    // Тест 5: buildUnionSql() — содержит все 6 источников
    // ------------------------------------------------------------------

    /**
     * AC: buildUnionSql() формирует UNION ALL из 6 таблиц:
     * arrival, sell, sell2, returnp, return_arrival, sverka_log.
     *
     * Используется ReflectionMethod для доступа к protected-методу.
     */
    public function testBuildUnionSqlContainsAllSources()
    {
        $model = new \app\models\ProductMovementSearch();

        $ref = new \ReflectionMethod($model, 'buildUnionSql');
        $ref->setAccessible(true);
        $sql = $ref->invoke($model);

        $this->assertIsString($sql, 'buildUnionSql() должен возвращать строку');

        $this->assertStringContainsString(
            'FROM arrival',
            $sql,
            'SQL должен содержать источник arrival'
        );
        $this->assertStringContainsString(
            'FROM sell s',
            $sql,
            'SQL должен содержать источник sell'
        );
        $this->assertStringContainsString(
            'FROM sell2',
            $sql,
            'SQL должен содержать источник sell2'
        );
        $this->assertStringContainsString(
            'FROM returnp',
            $sql,
            'SQL должен содержать источник returnp'
        );
        $this->assertStringContainsString(
            'FROM return_arrival',
            $sql,
            'SQL должен содержать источник return_arrival'
        );
        $this->assertStringContainsString(
            'FROM sverka_log',
            $sql,
            'SQL должен содержать источник sverka_log'
        );

        // Проверяем наличие UNION ALL (5 раз: между 6 блоками)
        $this->assertSame(
            5,
            substr_count($sql, 'UNION ALL'),
            'SQL должен содержать ровно 5 блоков UNION ALL'
        );

        // Проверяем плейсхолдеры — никакой конкатенации пользовательского ввода
        $this->assertStringContainsString(':id_product', $sql);
        $this->assertStringContainsString(':id_store', $sql);
        $this->assertStringContainsString(':date_from', $sql);
        $this->assertStringContainsString(':date_to', $sql);
        $this->assertStringContainsString(':operation_type', $sql);
    }

    // ------------------------------------------------------------------
    // Тест 6: buildBindings() — корректный маппинг параметров
    // ------------------------------------------------------------------

    /**
     * AC: buildBindings() правильно маппирует атрибуты модели
     * на именованные плейсхолдеры SQL.
     *
     * Используется ReflectionMethod для доступа к protected-методу.
     */
    public function testBuildBindingsMapping()
    {
        $model = new \app\models\ProductMovementSearch();
        $model->id_product     = 42;
        $model->id_store       = 3;
        $model->date_from      = '2026-01-01';
        $model->date_to        = '2026-12-31';
        $model->operation_type = 'sell';

        $ref = new \ReflectionMethod($model, 'buildBindings');
        $ref->setAccessible(true);
        $bindings = $ref->invoke($model);

        $this->assertIsArray($bindings, 'buildBindings() должен возвращать массив');

        // Все 5 плейсхолдеров должны присутствовать
        $this->assertArrayHasKey(':id_product', $bindings);
        $this->assertArrayHasKey(':id_store', $bindings);
        $this->assertArrayHasKey(':date_from', $bindings);
        $this->assertArrayHasKey(':date_to', $bindings);
        $this->assertArrayHasKey(':operation_type', $bindings);

        // Значения корректно маппируются
        $this->assertSame(42, $bindings[':id_product'], ':id_product должен быть (int)42');
        $this->assertSame(3, $bindings[':id_store'], ':id_store должен быть (int)3');
        $this->assertSame('2026-01-01', $bindings[':date_from']);
        $this->assertSame('2026-12-31', $bindings[':date_to']);
        $this->assertSame('sell', $bindings[':operation_type']);
    }

    // ------------------------------------------------------------------
    // Дополнительный тест: buildBindings() с пустыми необязательными полями
    // ------------------------------------------------------------------

    /**
     * AC: Необязательные фильтры (id_store, date_from, date_to, operation_type)
     * передаются как NULL когда не заданы — чтобы условие
     * (:param IS NULL OR ...) работало как «без фильтра».
     */
    public function testBuildBindingsNullableFiltersAreNull()
    {
        $model             = new \app\models\ProductMovementSearch();
        $model->id_product = 7;
        // остальные атрибуты не заданы (null)

        $ref = new \ReflectionMethod($model, 'buildBindings');
        $ref->setAccessible(true);
        $bindings = $ref->invoke($model);

        $this->assertNull($bindings[':id_store'], ':id_store должен быть NULL когда не задан');
        $this->assertNull($bindings[':date_from'], ':date_from должен быть NULL когда не задан');
        $this->assertNull($bindings[':date_to'], ':date_to должен быть NULL когда не задан');
        $this->assertNull($bindings[':operation_type'], ':operation_type должен быть NULL когда не задан');
    }
}
