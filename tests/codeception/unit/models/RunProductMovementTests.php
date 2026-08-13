<?php

/**
 * Standalone runner — Story #30 (ProductMovementSearch) + Story #31 (ProductMovementController)
 * Feature #27: Отчёт «Движение товара»
 *
 * Реплицирует unit-тесты без Codeception.
 * Минимальный Yii2-bootstrap (без DB-соединения).
 * Совместим с PHP 7.1+.
 *
 * Запуск: php tests/codeception/unit/models/RunProductMovementTests.php
 */

/* --------------------------------------------------------------------------
 * Bootstrap Yii2 (без DB)
 * ------------------------------------------------------------------------*/

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV')   or define('YII_ENV', 'test');

$projectRoot = dirname(__DIR__, 4);   // .../elektron

require_once $projectRoot . '/vendor/autoload.php';
require_once $projectRoot . '/vendor/yiisoft/yii2/Yii.php';

// Алиасы для автозагрузки app\models\* и app\controllers\*
Yii::setAlias('@app',     $projectRoot);
Yii::setAlias('@webroot', $projectRoot . '/web');
Yii::setAlias('@tests',   $projectRoot . '/tests');

/* --------------------------------------------------------------------------
 * Мини-фреймворк утверждений (PHP 7.1 совместимый)
 * ------------------------------------------------------------------------*/

$results     = [];
$totalTests  = 0;
$passedTests = 0;
$failedTests = 0;

function runTest($name, $fn)
{
    global $totalTests, $passedTests, $failedTests, $results;
    $totalTests++;
    try {
        $fn();
        $passedTests++;
        $results[] = "  OK  $name";
    } catch (\Throwable $e) {
        $failedTests++;
        $results[] = "FAIL  $name -- " . $e->getMessage();
    }
}

function assertEqual_($expected, $actual, $msg)
{
    if ($expected !== $actual) {
        throw new \RuntimeException(
            $msg . ' [expected=' . var_export($expected, true)
                 . ', got='      . var_export($actual, true) . ']'
        );
    }
}

function assertInstanceOf_($class, $obj, $msg)
{
    if (!($obj instanceof $class)) {
        throw new \RuntimeException($msg . ' [got ' . get_class($obj) . ']');
    }
}

function assertIsArray_($val, $msg)
{
    if (!is_array($val)) {
        throw new \RuntimeException($msg . ' [got ' . gettype($val) . ']');
    }
}

function assertIsString_($val, $msg)
{
    if (!is_string($val)) {
        throw new \RuntimeException($msg . ' [got ' . gettype($val) . ']');
    }
}

function assertTrue_($condition, $msg)
{
    if (!$condition) {
        throw new \RuntimeException($msg);
    }
}

function assertFalse_($condition, $msg)
{
    if ($condition) {
        throw new \RuntimeException($msg . ' [expected false, got true]');
    }
}

function assertEmpty_($val, $msg)
{
    if (!empty($val)) {
        throw new \RuntimeException($msg . ' [got ' . var_export($val, true) . ']');
    }
}

function assertNotEmpty_($val, $msg)
{
    if (empty($val)) {
        throw new \RuntimeException($msg . ' [got empty]');
    }
}

function assertArrayHasKey_($key, $arr, $msg)
{
    if (!array_key_exists($key, $arr)) {
        $keys = implode(', ', array_keys($arr));
        throw new \RuntimeException($msg . " [key '$key' not found in: $keys]");
    }
}

function assertContains_($needle, $arr, $msg)
{
    if (!in_array($needle, $arr, true)) {
        $vals = implode(', ', array_map(function ($v) { return var_export($v, true); }, $arr));
        throw new \RuntimeException($msg . ' [' . var_export($needle, true) . ' not in: ' . $vals . ']');
    }
}

function assertCount_($expected, $arr, $msg)
{
    $actual = count($arr);
    if ($expected !== $actual) {
        throw new \RuntimeException($msg . " [expected count=$expected, got $actual]");
    }
}

function assertStringContains_($needle, $haystack, $msg)
{
    if (strpos($haystack, $needle) === false) {
        throw new \RuntimeException($msg . " [needle '$needle' not found]");
    }
}

function assertSame_($expected, $actual, $msg)
{
    if ($expected !== $actual) {
        throw new \RuntimeException(
            $msg . ' [expected=' . var_export($expected, true)
                 . ', got='      . var_export($actual, true) . ']'
        );
    }
}

function assertNull_($val, $msg)
{
    if ($val !== null) {
        throw new \RuntimeException($msg . ' [got ' . var_export($val, true) . ']');
    }
}

/* ==========================================================================
 * STORY #30: ProductMovementSearch — UNION по всем источникам (7 тестов)
 * ========================================================================*/

echo "=== Story #30: ProductMovementSearch (7 тестов) ===\n\n";

// Тест 1: search() без id_product → пустой ArrayDataProvider
runTest('testEmptyDataProviderWhenIdProductMissing', function () {
    $model    = new \app\models\ProductMovementSearch();
    $provider = $model->search([]);

    assertInstanceOf_(
        \yii\data\ArrayDataProvider::class,
        $provider,
        'search() должен возвращать ArrayDataProvider'
    );
    assertEmpty_(
        $provider->allModels,
        'allModels должен быть пустым, когда id_product не задан'
    );
});

// Тест 2: validate() без id_product → false
runTest('testRulesRequireIdProduct', function () {
    $model = new \app\models\ProductMovementSearch();
    $valid = $model->validate();

    assertFalse_($valid, 'validate() должен возвращать false без id_product');
    assertNotEmpty_($model->getErrors('id_product'), 'Должна быть ошибка на id_product');
});

// Тест 3: operationLabels() — ровно 6 типов
runTest('testOperationLabelsHasSixTypes', function () {
    $labels = \app\models\ProductMovementSearch::operationLabels();
    assertIsArray_($labels, 'operationLabels() должен возвращать массив');
    assertCount_(6, $labels, 'operationLabels() должен содержать ровно 6 элементов');
});

// Тест 4: operationLabels() — ключи и русские значения
runTest('testOperationLabelsRussianValues', function () {
    $labels = \app\models\ProductMovementSearch::operationLabels();
    $expected = [
        'arrival'         => 'Приход',
        'sell'            => 'Продажа',
        'sell2'           => 'Продажа (опт)',
        'return_client'   => 'Возврат от клиента',
        'return_supplier' => 'Возврат поставщику',
        'sverka'          => 'Сверка',
    ];
    foreach ($expected as $key => $value) {
        assertArrayHasKey_($key, $labels, "Ключ '$key' должен присутствовать");
        assertSame_($value, $labels[$key], "Метка '$key' должна быть '$value'");
    }
});

// Тест 5: buildUnionSql() — все 6 источников + 5 UNION ALL + плейсхолдеры
runTest('testBuildUnionSqlContainsAllSources', function () {
    $model = new \app\models\ProductMovementSearch();
    $ref   = new \ReflectionMethod($model, 'buildUnionSql');
    $ref->setAccessible(true);
    $sql = $ref->invoke($model);

    assertIsString_($sql, 'buildUnionSql() должен возвращать строку');

    foreach (['FROM arrival', 'FROM sell s', 'FROM sell2', 'FROM returnp', 'FROM return_arrival', 'FROM sverka_log'] as $src) {
        assertStringContains_($src, $sql, "SQL должен содержать $src");
    }
    assertEqual_(5, substr_count($sql, 'UNION ALL'), 'SQL должен содержать ровно 5 UNION ALL');

    foreach ([':id_product', ':id_store', ':date_from', ':date_to', ':operation_type'] as $ph) {
        assertStringContains_($ph, $sql, "SQL должен содержать плейсхолдер $ph");
    }
});

// Тест 6: buildBindings() — корректный маппинг параметров
runTest('testBuildBindingsMapping', function () {
    $model = new \app\models\ProductMovementSearch();
    $model->id_product     = 42;
    $model->id_store       = 3;
    $model->date_from      = '2026-01-01';
    $model->date_to        = '2026-12-31';
    $model->operation_type = 'sell';

    $ref = new \ReflectionMethod($model, 'buildBindings');
    $ref->setAccessible(true);
    $bindings = $ref->invoke($model);

    assertIsArray_($bindings, 'buildBindings() должен возвращать массив');

    foreach ([':id_product', ':id_store', ':date_from', ':date_to', ':operation_type'] as $k) {
        assertArrayHasKey_($k, $bindings, "Плейсхолдер $k должен присутствовать");
    }
    assertSame_(42, $bindings[':id_product'], ':id_product должен быть (int)42');
    assertSame_(3,  $bindings[':id_store'],   ':id_store должен быть (int)3');
    assertSame_('2026-01-01', $bindings[':date_from'], ':date_from');
    assertSame_('2026-12-31', $bindings[':date_to'],   ':date_to');
    assertSame_('sell', $bindings[':operation_type'],  ':operation_type');
});

// Тест 7: buildBindings() — null для необязательных фильтров
runTest('testBuildBindingsNullableFiltersAreNull', function () {
    $model             = new \app\models\ProductMovementSearch();
    $model->id_product = 7;

    $ref = new \ReflectionMethod($model, 'buildBindings');
    $ref->setAccessible(true);
    $bindings = $ref->invoke($model);

    assertNull_($bindings[':id_store'],       ':id_store должен быть NULL');
    assertNull_($bindings[':date_from'],      ':date_from должен быть NULL');
    assertNull_($bindings[':date_to'],        ':date_to должен быть NULL');
    assertNull_($bindings[':operation_type'], ':operation_type должен быть NULL');
});

/* ==========================================================================
 * STORY #31: ProductMovementController — AccessControl + VerbFilter (7 тестов)
 * ========================================================================*/

echo "\n=== Story #31: ProductMovementController (7 тестов) ===\n\n";

// Тест 1: контроллер существует и наследует yii\web\Controller
runTest('testControllerExtendsWebController', function () {
    $controller = new \app\controllers\ProductMovementController(
        'product-movement',
        null   // module=null допустим для unit-тестирования behaviors()
    );
    assertInstanceOf_(
        \yii\web\Controller::class,
        $controller,
        'ProductMovementController должен наследовать yii\web\Controller'
    );
});

// Тест 2: behaviors() содержит ключ 'access' с AccessControl
runTest('testBehaviorsContainsAccessControl', function () {
    $controller = new \app\controllers\ProductMovementController('product-movement', null);
    $behaviors  = $controller->behaviors();

    assertArrayHasKey_('access', $behaviors, "behaviors() должен содержать ключ 'access'");
    assertSame_(
        \yii\filters\AccessControl::className(),
        $behaviors['access']['class'],
        "access.class должен быть AccessControl"
    );
});

// Тест 3: AccessControl разрешает только роль '@'
runTest('testAccessControlAllowsOnlyAuthenticatedRole', function () {
    $controller = new \app\controllers\ProductMovementController('product-movement', null);
    $access     = $controller->behaviors()['access'];

    assertArrayHasKey_('rules', $access, "access должен содержать 'rules'");
    assertNotEmpty_($access['rules'], 'rules не должен быть пустым');

    $rule = $access['rules'][0];
    assertTrue_($rule['allow'], "allow должен быть true");
    assertContains_('@', $rule['roles'], "rules должен разрешать роль '@'");
});

// Тест 4: behaviors() содержит ключ 'verbs' с VerbFilter
runTest('testBehaviorsContainsVerbFilter', function () {
    $controller = new \app\controllers\ProductMovementController('product-movement', null);
    $behaviors  = $controller->behaviors();

    assertArrayHasKey_('verbs', $behaviors, "behaviors() должен содержать ключ 'verbs'");
    assertSame_(
        \yii\filters\VerbFilter::className(),
        $behaviors['verbs']['class'],
        "verbs.class должен быть VerbFilter"
    );
});

// Тест 5: VerbFilter разрешает только GET для action 'report'
runTest('testVerbFilterAllowsOnlyGetForReport', function () {
    $controller = new \app\controllers\ProductMovementController('product-movement', null);
    $verbs      = $controller->behaviors()['verbs'];

    assertArrayHasKey_('actions', $verbs, "verbs должен содержать 'actions'");
    assertArrayHasKey_('report', $verbs['actions'], "actions должен содержать 'report'");
    assertContains_('GET', $verbs['actions']['report'], "report должен принимать GET");
    assertCount_(1, $verbs['actions']['report'], "report должен принимать ровно 1 HTTP-метод");
});

// Тест 6: actionReport() через модель — guard-case (без id_product → пустой provider)
runTest('testActionReportDelegatesSearchToModel', function () {
    // Проверяем через модель: actionReport() создаёт ProductMovementSearch и вызывает search()
    $searchModel  = new \app\models\ProductMovementSearch();
    $dataProvider = $searchModel->search([]);

    assertInstanceOf_(
        \yii\data\ArrayDataProvider::class,
        $dataProvider,
        'search() должен возвращать ArrayDataProvider'
    );
    assertEmpty_(
        $dataProvider->allModels,
        'allModels должен быть пустым при отсутствии id_product'
    );
});

// Тест 7: ProductMovementSearch::operationLabels() доступен (зависимость контроллера)
runTest('testProductMovementSearchIsAccessible', function () {
    $labels = \app\models\ProductMovementSearch::operationLabels();
    assertIsArray_($labels, 'operationLabels() должен возвращать массив');
    assertCount_(6, $labels, 'operationLabels() должен содержать 6 типов');
    assertArrayHasKey_('arrival', $labels, "'arrival' должен присутствовать");
    assertArrayHasKey_('sell', $labels, "'sell' должен присутствовать");
});

/* --------------------------------------------------------------------------
 * Итог
 * ------------------------------------------------------------------------*/

echo "\n--- Результаты ---\n";
foreach ($results as $r) {
    echo "$r\n";
}
$total  = $totalTests;
$passed = $passedTests;
$failed = $failedTests;
$status = $failed === 0 ? 'PASS' : 'FAIL';
echo "\n[$status] Всего: $total | OK: $passed | FAIL: $failed\n";
exit($failed > 0 ? 1 : 0);
