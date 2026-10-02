<?php
/**
 * Standalone test runner для Print2TruncateTest
 * Feature #24: Обрезка длинных названий при печати чека 40x20мм
 * Stories #25 и #26
 *
 * Запускается без Codeception/PHPUnit — pure PHP assertions
 */

$passed = 0;
$failed = 0;
$errors = [];

function assert_equals($expected, $actual, $testName) {
    global $passed, $failed, $errors;
    if ($expected === $actual) {
        echo "  OK  $testName\n";
        $passed++;
    } else {
        echo " FAIL $testName\n";
        echo "       Expected: " . var_export($expected, true) . "\n";
        echo "       Actual:   " . var_export($actual, true) . "\n";
        $failed++;
        $errors[] = $testName;
    }
}

function assert_string_ends_with($suffix, $string, $testName) {
    global $passed, $failed, $errors;
    // Использовать mb_substr для корректного сравнения многобайтовых символов
    $suffixLen = mb_strlen($suffix, 'UTF-8');
    $stringLen = mb_strlen($string, 'UTF-8');
    $actual_end = mb_substr($string, $stringLen - $suffixLen, $suffixLen, 'UTF-8');
    if ($actual_end === $suffix) {
        echo "  OK  $testName\n";
        $passed++;
    } else {
        echo " FAIL $testName\n";
        echo "       Expected to end with: " . var_export($suffix, true) . "\n";
        echo "       Actual string: " . var_export($string, true) . "\n";
        $failed++;
        $errors[] = $testName;
    }
}

function assert_string_contains($needle, $haystack, $testName) {
    global $passed, $failed, $errors;
    if (strpos($haystack, $needle) !== false) {
        echo "  OK  $testName\n";
        $passed++;
    } else {
        echo " FAIL $testName\n";
        echo "       Expected to contain: " . var_export($needle, true) . "\n";
        $failed++;
        $errors[] = $testName;
    }
}

function assert_less_than_or_equal($expected_max, $actual, $testName) {
    global $passed, $failed, $errors;
    if ($actual <= $expected_max) {
        echo "  OK  $testName\n";
        $passed++;
    } else {
        echo " FAIL $testName\n";
        echo "       Expected <= $expected_max, got $actual\n";
        $failed++;
        $errors[] = $testName;
    }
}

function assert_not_false($value, $testName) {
    global $passed, $failed, $errors;
    if ($value !== false) {
        echo "  OK  $testName\n";
        $passed++;
    } else {
        echo " FAIL $testName\n";
        echo "       Expected non-false value\n";
        $failed++;
        $errors[] = $testName;
    }
}

/**
 * Хелпер-функция, имитирующая логику шаблона print2.php
 */
function truncateForPrint2($name, $maxChars = 28) {
    if (mb_strlen($name, 'UTF-8') > $maxChars) {
        return mb_substr($name, 0, $maxChars, 'UTF-8') . '…';
    }
    return $name;
}

$print2ViewPath = __DIR__ . '/../views/barcode/print2.php';
$maxChars = 28;

echo "===========================================\n";
echo "Print2TruncateTest — Feature #24\n";
echo "Stories #25 (длинные названия) + #26 (короткие названия)\n";
echo "===========================================\n\n";

echo "--- Story #26: короткие названия отображаются без изменений ---\n";

// testShortNameUnchanged
$name = 'Кабель HDMI 1м';
assert_equals($name, truncateForPrint2($name), 'testShortNameUnchanged');

// testExactlyMaxCharsUnchanged — ровно 28 символов
$name = str_repeat('А', $maxChars);
assert_equals($name, truncateForPrint2($name), 'testExactlyMaxCharsUnchanged');

// testEmptyNameUnchanged
assert_equals('', truncateForPrint2(''), 'testEmptyNameUnchanged');

echo "\n--- Story #25: длинные названия обрезаются с добавлением многоточия ---\n";

// testLongNameTruncated
$name   = 'Кабель USB Type-C 1.5м нейлон чёрный';
$result = truncateForPrint2($name);
assert_string_ends_with('…', $result, 'testLongNameTruncated: заканчивается на многоточие');
assert_equals($maxChars + 1, mb_strlen($result, 'UTF-8'), 'testLongNameTruncated: длина = maxChars + 1');

// testTruncatedLengthIsMaxChars
$name            = str_repeat('Б', 50);
$result          = truncateForPrint2($name);
$withoutEllipsis = mb_substr($result, 0, -1, 'UTF-8');
assert_equals($maxChars, mb_strlen($withoutEllipsis, 'UTF-8'), 'testTruncatedLengthIsMaxChars');

// testNameOneOverLimitTruncated — 29 символов, один сверх лимита
$name = str_repeat('А', $maxChars + 1);
$result = truncateForPrint2($name);
assert_string_ends_with('…', $result, 'testNameOneOverLimitTruncated');

// testCyrillicMultibyteHandled — 35 кириллических символов
$name = str_repeat('Ж', 35);
$result = truncateForPrint2($name);
assert_string_ends_with('…', $result, 'testCyrillicMultibyteHandled: многоточие');
$withoutEllipsis = mb_substr($result, 0, -1, 'UTF-8');
assert_equals($maxChars, mb_strlen($withoutEllipsis, 'UTF-8'), 'testCyrillicMultibyteHandled: длина обрезки');

echo "\n--- Проверки содержимого views/barcode/print2.php ---\n";

// testPrint2ViewUsesMbStrlen
$content = file_get_contents($print2ViewPath);
assert_not_false($content, 'testPrint2ViewReadable');
assert_string_contains("mb_strlen(\$productName, 'UTF-8')", $content, 'testPrint2ViewUsesMbStrlen');

// testPrint2ViewUsesMbSubstr
assert_string_contains("mb_substr(\$productName, 0, \$maxChars, 'UTF-8')", $content, 'testPrint2ViewUsesMbSubstr');

// testPrint2ViewHasCorrectMaxChars
assert_string_contains('$maxChars = 28', $content, 'testPrint2ViewHasCorrectMaxChars');

// testPrint2ViewAddsEllipsis
assert_string_contains("'…'", $content, 'testPrint2ViewAddsEllipsis');

// testPrint2ViewUsesHtmlEncode
assert_string_contains('Html::encode($displayName)', $content, 'testPrint2ViewUsesHtmlEncode');

echo "\n===========================================\n";
echo "Итоги:\n";
echo "  Passed: $passed\n";
echo "  Failed: $failed\n";

if ($failed > 0) {
    echo "\nFailed tests:\n";
    foreach ($errors as $e) {
        echo "  - $e\n";
    }
    echo "\nResult: FAIL\n";
    exit(1);
} else {
    echo "\nResult: PASS (все $passed тестов прошли)\n";
    exit(0);
}
