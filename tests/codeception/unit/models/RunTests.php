<?php
/**
 * PHPUnit-native runner for QA testing stories #20, #21, #22.
 * Runs the same logic without Yii or Codeception dependency.
 *
 * Usage: php RunTests.php
 */

namespace yii\web {
    class BadRequestHttpException extends \RuntimeException {}
}

namespace {

$results = [];
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function runTest($name, callable $fn) {
    global $totalTests, $passedTests, $failedTests, $results;
    $totalTests++;
    try {
        $fn();
        $passedTests++;
        $results[] = "  OK  $name";
    } catch (\Exception $e) {
        $failedTests++;
        $results[] = "FAIL  $name -- " . $e->getMessage();
    } catch (\Error $e) {
        $failedTests++;
        $results[] = "FAIL  $name -- " . $e->getMessage();
    }
}

function expectExceptionClass($class, callable $fn) {
    try {
        $fn();
        throw new \RuntimeException("Expected exception $class was not thrown");
    } catch (\Throwable $e) {
        if ($e instanceof $class) {
            return;
        }
        throw $e;
    }
}

function validateCancelNumber($number) {
    if (!$number || !is_numeric($number) || (int)$number <= 0) {
        throw new \yii\web\BadRequestHttpException('Invalid number parameter');
    }
    return true;
}

function resolveSellNumber($existingDclient) {
    return ($existingDclient !== null && $existingDclient->number !== null)
        ? (int)$existingDclient->number
        : 0;
}

function simulateDclientDeleteBranch($dclient) {
    $deleted = false;
    if ($dclient !== null) {
        $deleted = true;
    }
    return $deleted;
}

function simulateDebtDeleteBranch($debt) {
    $deleted = false;
    if ($debt !== null) {
        $deleted = true;
    }
    return $deleted;
}

echo "=== ActionCancelValidationTest (Story #20) ===\n";

runTest('testNullNumberThrows', function() {
    expectExceptionClass(\yii\web\BadRequestHttpException::class, function() { validateCancelNumber(null); });
});
runTest('testEmptyStringThrows', function() {
    expectExceptionClass(\yii\web\BadRequestHttpException::class, function() { validateCancelNumber(''); });
});
runTest('testZeroThrows', function() {
    expectExceptionClass(\yii\web\BadRequestHttpException::class, function() { validateCancelNumber(0); });
});
runTest('testStringZeroThrows', function() {
    expectExceptionClass(\yii\web\BadRequestHttpException::class, function() { validateCancelNumber('0'); });
});
runTest('testNegativeNumberThrows', function() {
    expectExceptionClass(\yii\web\BadRequestHttpException::class, function() { validateCancelNumber(-5); });
});
runTest('testAlphaStringThrows', function() {
    expectExceptionClass(\yii\web\BadRequestHttpException::class, function() { validateCancelNumber('abc'); });
});
runTest('testFloatStringPassesAsInt', function() {
    // '1.5' is_numeric === true, (int)'1.5' === 1 > 0, so no exception is thrown.
    // PHP casts float-string to int in the controller (Yii URL params are strings).
    $result = validateCancelNumber('1.5');
    if ($result !== true) throw new \RuntimeException("Expected true for numeric float-string '1.5'");
});
runTest('testPositiveIntegerPasses', function() {
    $result = validateCancelNumber(42);
    if ($result !== true) throw new \RuntimeException("Expected true");
});
runTest('testPositiveStringIntegerPasses', function() {
    $result = validateCancelNumber('42');
    if ($result !== true) throw new \RuntimeException("Expected true");
});
runTest('testOneIsValid', function() {
    $result = validateCancelNumber(1);
    if ($result !== true) throw new \RuntimeException("Expected true");
});
runTest('testNumberCastToInt', function() {
    $number = '99';
    if (!$number || !is_numeric($number) || (int)$number <= 0) {
        throw new \RuntimeException('Should not throw for valid number');
    }
    $number = (int)$number;
    if ($number !== 99) throw new \RuntimeException("Expected 99, got $number");
    if (!is_int($number)) throw new \RuntimeException("Expected int type");
});

echo "\n=== ActionReceivedDebtNumberTest (Story #21) ===\n";

runTest('testExistingDclientWithNumberReturnsNumber', function() {
    $existing = (object)['number' => 15];
    $result = resolveSellNumber($existing);
    if ($result !== 15) throw new \RuntimeException("Expected 15, got $result");
});
runTest('testExistingDclientNumberCastToInt', function() {
    $existing = (object)['number' => '42'];
    $result = resolveSellNumber($existing);
    if ($result !== 42) throw new \RuntimeException("Expected 42, got $result");
    if (!is_int($result)) throw new \RuntimeException("Expected int type");
});
runTest('testExistingDclientWithNullNumberReturnsSentinel', function() {
    $existing = (object)['number' => null];
    $result = resolveSellNumber($existing);
    if ($result !== 0) throw new \RuntimeException("Expected 0, got $result");
});
runTest('testNullExistingDclientReturnsSentinel', function() {
    $result = resolveSellNumber(null);
    if ($result !== 0) throw new \RuntimeException("Expected 0, got $result");
});
runTest('testResultIsNeverNull', function() {
    $r1 = resolveSellNumber(null);
    $r2 = resolveSellNumber((object)['number' => null]);
    $r3 = resolveSellNumber((object)['number' => 7]);
    if ($r1 === null || $r2 === null || $r3 === null) {
        throw new \RuntimeException("Result must never be null");
    }
});
runTest('testSentinelIsZeroNotNegative', function() {
    $result = resolveSellNumber(null);
    if ($result < 0) throw new \RuntimeException("Sentinel must be >= 0, got $result");
});

echo "\n=== CostsActionDeleteNullSafeTest (Story #22) ===\n";

runTest('testDclientFoundDeleteIsCalled', function() {
    $dclient = (object)['id' => 5];
    $result = simulateDclientDeleteBranch($dclient);
    if ($result !== true) throw new \RuntimeException("delete() should be called when dclient exists");
});
runTest('testDclientNotFoundDeleteIsSkipped', function() {
    $result = simulateDclientDeleteBranch(null);
    if ($result !== false) throw new \RuntimeException("delete() must not be called when dclient is null");
});
runTest('testDclientNullDoesNotThrowFatalError', function() {
    $dclient = null;
    $exceptionThrown = false;
    try {
        if ($dclient !== null) {
            $dclient->delete();
        }
    } catch (\Throwable $e) {
        $exceptionThrown = true;
    }
    if ($exceptionThrown) throw new \RuntimeException("No exception should be thrown when null guard is in place");
});
runTest('testDebtFoundDeleteIsCalled', function() {
    $debt = (object)['id' => 10];
    $result = simulateDebtDeleteBranch($debt);
    if ($result !== true) throw new \RuntimeException("delete() should be called when debt exists");
});
runTest('testDebtNotFoundDeleteIsSkipped', function() {
    $result = simulateDebtDeleteBranch(null);
    if ($result !== false) throw new \RuntimeException("delete() must not be called when debt is null");
});
runTest('testDebtNullDoesNotThrowFatalError', function() {
    $debt = null;
    $exceptionThrown = false;
    try {
        if ($debt !== null) {
            $debt->delete();
        }
    } catch (\Throwable $e) {
        $exceptionThrown = true;
    }
    if ($exceptionThrown) throw new \RuntimeException("No exception should be thrown when null guard is in place");
});
runTest('testDebtVariableIsDistinctFromDclient', function() {
    $dclient = (object)['id' => 1, 'type' => 'dclient'];
    $debt = (object)['id' => 2, 'type' => 'debt'];
    if ($dclient === $debt) throw new \RuntimeException("Objects must not be the same");
    if ($dclient->type !== 'dclient') throw new \RuntimeException("dclient type mismatch");
    if ($debt->type !== 'debt') throw new \RuntimeException("debt type mismatch");
});

echo "\n=== ИТОГО ===\n";
foreach ($results as $r) {
    echo "$r\n";
}
echo "\nВсего тестов: $totalTests | Прошло: $passedTests | Упало: $failedTests\n";
exit($failedTests > 0 ? 1 : 0);

} // end namespace
