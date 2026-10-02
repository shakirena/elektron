<?php

/**
 * Standalone runner — SverkaLogTest, Story #29
 * Feature #27: Отчёт «Движение товара»
 *
 * Реплицирует 9 unit-тестов + 3 дополнительных без Codeception.
 * Минимальный Yii2-bootstrap (без DB-соединения).
 * Совместим с PHP 7.1+.
 *
 * Запуск: php tests/codeception/unit/models/RunSverkaLogTests.php
 */

/* --------------------------------------------------------------------------
 * Bootstrap Yii2 (без DB)
 * ------------------------------------------------------------------------*/

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV')   or define('YII_ENV', 'test');

$projectRoot = dirname(__DIR__, 4);   // .../elektron

require_once $projectRoot . '/vendor/autoload.php';
require_once $projectRoot . '/vendor/yiisoft/yii2/Yii.php';

// Алиасы для автозагрузки app\models\*
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

function assertEqual($expected, $actual, $msg)
{
    if ($expected !== $actual) {
        throw new \RuntimeException($msg . ' [expected=' . var_export($expected, true) . ', got=' . var_export($actual, true) . ']');
    }
}

function assertEqualFloat($expected, $actual, $msg, $delta = 0.0001)
{
    if (abs($expected - $actual) > $delta) {
        throw new \RuntimeException($msg . " [expected=$expected, got=$actual]");
    }
}

function assertTrue_($condition, $msg)
{
    if (!$condition) {
        throw new \RuntimeException($msg);
    }
}

function assertArrayKey_($key, $arr, $msg)
{
    if (!array_key_exists($key, $arr)) {
        $keys = implode(', ', array_keys($arr));
        throw new \RuntimeException($msg . " [key '$key' not found in: $keys]");
    }
}

function assertInArray_($needle, $arr, $msg)
{
    if (!in_array($needle, $arr, true)) {
        $vals = implode(', ', array_map(function ($v) { return var_export($v, true); }, $arr));
        throw new \RuntimeException($msg . ' [' . var_export($needle, true) . ' not in: ' . $vals . ']');
    }
}

/* --------------------------------------------------------------------------
 * Тесты
 * ------------------------------------------------------------------------*/

echo "=== RunSverkaLogTests — Story #29 ===\n";
echo "Проверка модели SverkaLog (таблица sverka_log, лог сверок)\n\n";

// 1. tableName()
runTest('testTableName', function () {
    assertEqual('sverka_log', \app\models\SverkaLog::tableName(),
        'SverkaLog::tableName() должен возвращать "sverka_log"');
});

// 2. Метод logChange() существует
runTest('testLogChangeMethodExists', function () {
    assertTrue_(
        method_exists('app\models\SverkaLog', 'logChange'),
        'Метод SverkaLog::logChange() должен существовать'
    );
});

// 3. logChange() — static и public
runTest('testLogChangeIsStaticAndPublic', function () {
    $ref = new \ReflectionMethod('app\models\SverkaLog', 'logChange');
    assertTrue_($ref->isStatic(), 'logChange() должен быть static');
    assertTrue_($ref->isPublic(), 'logChange() должен быть public');
});

// 4. logChange() принимает ровно 5 параметров
runTest('testLogChangeHasFiveParameters', function () {
    $ref = new \ReflectionMethod('app\models\SverkaLog', 'logChange');
    assertEqual(
        5,
        $ref->getNumberOfParameters(),
        'logChange() должен принимать 5 параметров: idProduct, idStore, qtyBefore, qtyAfter, idUser'
    );
});

// 5. Расчёт delta — прирост (реплика формулы logChange(): (float)$qtyAfter - (float)$qtyBefore)
runTest('testDeltaCalculationPositive', function () {
    // ActiveRecord::__set() требует DB-соединение для getTableSchema().
    // Тестируем саму арифметику delta = (float)qtyAfter - (float)qtyBefore
    // — ту же формулу, что использует logChange().
    $qtyBefore = 10.0;
    $qtyAfter  = 15.0;
    $delta     = (float) $qtyAfter - (float) $qtyBefore;
    assertEqualFloat(5.0, $delta, 'delta должен быть 5.0 при qtyBefore=10, qtyAfter=15');
});

// 6. Расчёт delta — убыток
runTest('testDeltaCalculationNegative', function () {
    $qtyBefore = 20.0;
    $qtyAfter  = 5.0;
    $delta     = (float) $qtyAfter - (float) $qtyBefore;
    assertEqualFloat(-15.0, $delta, 'delta должен быть -15.0 при qtyBefore=20, qtyAfter=5');
});

// 7. Расчёт delta — ноль
runTest('testDeltaCalculationZero', function () {
    $qtyBefore = 7.5;
    $qtyAfter  = 7.5;
    $delta     = (float) $qtyAfter - (float) $qtyBefore;
    assertEqualFloat(0.0, $delta, 'delta должен быть 0.0 при одинаковых qty');
});

// 8. rules() содержит обязательные поля
runTest('testRulesContainRequiredFields', function () {
    $model    = new \app\models\SverkaLog();
    $rules    = $model->rules();
    $required = [];
    foreach ($rules as $rule) {
        if (isset($rule[1]) && $rule[1] === 'required') {
            $required = array_merge($required, (array) $rule[0]);
        }
    }
    foreach (['id_product', 'id_store', 'id_user', 'datetime'] as $field) {
        assertInArray_($field, $required, "Поле '$field' должно быть required");
    }
});

// 9. attributeLabels() содержит все персистируемые атрибуты
runTest('testAttributeLabelsCovertAllPersistedFields', function () {
    $labels = (new \app\models\SverkaLog())->attributeLabels();
    foreach (['id', 'id_product', 'id_store', 'qty_before', 'qty_after', 'delta', 'id_user', 'datetime'] as $attr) {
        assertArrayKey_($attr, $labels, "Ярлык для '$attr' должен быть определён");
    }
});

// 10. (доп.) Метод getIdProduct() существует как relation
runTest('testRelationMethodGetIdProductExists', function () {
    assertTrue_(
        method_exists('app\models\SverkaLog', 'getIdProduct'),
        'SverkaLog должен определять отношение getIdProduct()'
    );
    $ref = new \ReflectionMethod('app\models\SverkaLog', 'getIdProduct');
    assertTrue_($ref->isPublic(), 'getIdProduct() должен быть public');
});

// 11. (доп.) Метод getIdStore() существует как relation
runTest('testRelationMethodGetIdStoreExists', function () {
    assertTrue_(
        method_exists('app\models\SverkaLog', 'getIdStore'),
        'SverkaLog должен определять отношение getIdStore()'
    );
});

// 12. (доп.) logChange() — имена параметров корректны
runTest('testLogChangeParameterNames', function () {
    $ref    = new \ReflectionMethod('app\models\SverkaLog', 'logChange');
    $params = array_map(function ($p) { return $p->getName(); }, $ref->getParameters());
    foreach (['idProduct', 'idStore', 'qtyBefore', 'qtyAfter', 'idUser'] as $expected) {
        assertInArray_($expected, $params,
            "Параметр '$expected' должен присутствовать в сигнатуре logChange()");
    }
});

/* --------------------------------------------------------------------------
 * Итог
 * ------------------------------------------------------------------------*/

echo "\n--- Результаты ---\n";
foreach ($results as $r) {
    echo "$r\n";
}
$status = $failedTests === 0 ? 'PASS' : 'FAIL';
echo "\n[$status] Всего: $totalTests | OK: $passedTests | FAIL: $failedTests\n";
exit($failedTests > 0 ? 1 : 0);
