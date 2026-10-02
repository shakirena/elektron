<?php

/**
 * Standalone runner — ReturnpDatetimeTest, Story #28
 * Feature #27: Отчёт «Движение товара»
 *
 * Реплицирует 5 unit-тестов без Codeception/PHPUnit.
 * Проверяет, что SellController::actionReceivedReturn() использует
 * date("Y-m-d H:i:s") для returnp.data (а не date("Y-m-d")).
 *
 * Запуск: php tests/unit/models/RunReturnpDatetimeTests.php
 */

$projectRoot = dirname(__DIR__, 3);

/* --------------------------------------------------------------------------
 * Мини-фреймворк утверждений
 * ------------------------------------------------------------------------*/

$results    = [];
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function runTest(string $name, callable $fn): void
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

function assertMatchesPattern(string $pattern, string $value, string $msg): void
{
    if (!preg_match($pattern, $value)) {
        throw new \RuntimeException("$msg [pattern: $pattern, value: " . var_export($value, true) . "]");
    }
}

function assertNotMatchesPattern(string $pattern, string $value, string $msg): void
{
    if (preg_match($pattern, $value)) {
        throw new \RuntimeException("$msg [pattern: $pattern, value: " . var_export($value, true) . "]");
    }
}

function assertGt(int $expected, int $actual, string $msg): void
{
    if ($actual <= $expected) {
        throw new \RuntimeException("$msg [expected > $expected, got $actual]");
    }
}

function assertContains(string $needle, string $haystack, string $msg): void
{
    if (strpos($haystack, $needle) === false) {
        throw new \RuntimeException("$msg [needle not found: " . var_export($needle, true) . "]");
    }
}

function assertNotContains(string $needle, string $haystack, string $msg): void
{
    if (strpos($haystack, $needle) !== false) {
        throw new \RuntimeException("$msg [needle found unexpectedly: " . var_export($needle, true) . "]");
    }
}

/* --------------------------------------------------------------------------
 * Тесты
 * ------------------------------------------------------------------------*/

$sellControllerPath = $projectRoot . '/controllers/SellController.php';

echo "=== RunReturnpDatetimeTests — Story #28 ===\n";
echo "Проверка расширения returnp.data до DATETIME\n\n";

// 1. Новый формат валиден
runTest('testDatetimeFormatContainsTime', function () {
    $dt = date("Y-m-d H:i:s");
    assertMatchesPattern(
        '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
        $dt,
        'date("Y-m-d H:i:s") должен возвращать полный datetime'
    );
});

// 2. Старый формат не содержит времени (подтверждение корректности теста)
runTest('testDateOnlyFormatLacksTime', function () {
    $d = date("Y-m-d");
    assertMatchesPattern(
        '/^\d{4}-\d{2}-\d{2}$/',
        $d,
        'date("Y-m-d") должен возвращать только дату'
    );
    assertNotMatchesPattern(
        '/ \d{2}:\d{2}:\d{2}/',
        $d,
        'date("Y-m-d") не должен содержать времени'
    );
});

// 3. Новый формат длиннее старого
runTest('testDatetimeStringLongerThanDateOnly', function () {
    $dt = date("Y-m-d H:i:s");
    $d  = date("Y-m-d");
    assertGt(
        strlen($d),
        strlen($dt),
        'Полный datetime (19 симв.) длиннее даты (10 симв.)'
    );
    assertContains(' ', $dt, 'DATETIME должен содержать пробел между датой и временем');
});

// 4. SellController содержит новый формат для $returnp->data
runTest('testSellControllerUsesDatetimeFormat', function () use ($sellControllerPath) {
    if (!file_exists($sellControllerPath)) {
        throw new \RuntimeException("Файл не найден: $sellControllerPath");
    }
    $content = file_get_contents($sellControllerPath);
    assertContains(
        '$returnp->data=date("Y-m-d H:i:s")',
        $content,
        'actionReceivedReturn() должен писать date("Y-m-d H:i:s") в returnp.data'
    );
});

// 5. Старый формат для $returnp->data удалён
runTest('testSellControllerDoesNotUseDateOnlyForReturnp', function () use ($sellControllerPath) {
    $content = file_get_contents($sellControllerPath);
    assertNotContains(
        '$returnp->data=date("Y-m-d")',
        $content,
        'actionReceivedReturn() не должен использовать date("Y-m-d") для $returnp->data'
    );
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
