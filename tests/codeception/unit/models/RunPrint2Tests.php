<?php
/**
 * PHPUnit-native runner для тестирования логики обрезки print2.php
 * Stories #25, #26 — Feature #24: Обрезка длинных названий товаров при печати чека 40x20мм
 *
 * Usage: php RunPrint2Tests.php
 */

$results = [];
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$maxChars = 28;

$viewContent = file_get_contents(dirname(__DIR__, 4) . '/views/barcode/print2.php');

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

function truncateForPrint2($name, $max) {
    if (mb_strlen($name, 'UTF-8') > $max) {
        return mb_substr($name, 0, $max, 'UTF-8') . '…';
    }
    return $name;
}

echo "=== Print2TruncateTest (Stories #25, #26) — Feature #24 ===\n\n";

// ------------------------------------------------------------------
// Story #26: короткие названия отображаются без изменений
// ------------------------------------------------------------------
echo "--- Story #26: короткие названия ---\n";

runTest('testShortNameUnchanged', function() {
    global $maxChars;
    $name = 'Кабель HDMI 1м';
    $result = truncateForPrint2($name, $maxChars);
    if ($result !== $name) {
        throw new \RuntimeException("Expected unchanged, got: $result");
    }
});

runTest('testExactlyMaxCharsUnchanged', function() {
    global $maxChars;
    $name = str_repeat('А', $maxChars); // ровно 28 символов
    $result = truncateForPrint2($name, $maxChars);
    if ($result !== $name) {
        throw new \RuntimeException("Expected unchanged at exactly 28 chars");
    }
});

runTest('testEmptyNameUnchanged', function() {
    global $maxChars;
    $result = truncateForPrint2('', $maxChars);
    if ($result !== '') {
        throw new \RuntimeException("Empty string should be unchanged, got: $result");
    }
});

// ------------------------------------------------------------------
// Story #25: длинные названия обрезаются с добавлением многоточия
// ------------------------------------------------------------------
echo "--- Story #25: длинные названия ---\n";

runTest('testLongNameTruncated', function() {
    global $maxChars;
    $name = 'Кабель USB Type-C 1.5м нейлон чёрный';
    $result = truncateForPrint2($name, $maxChars);
    $ellipsis = '…';
    if (mb_substr($result, -1, null, 'UTF-8') !== $ellipsis) {
        throw new \RuntimeException("Expected ellipsis at end, got: $result");
    }
    $resultLen = mb_strlen($result, 'UTF-8');
    if ($resultLen !== $maxChars + 1) {
        throw new \RuntimeException("Expected length " . ($maxChars + 1) . ", got: $resultLen");
    }
});

runTest('testTruncatedLengthIsMaxChars', function() {
    global $maxChars;
    $name = str_repeat('Б', 50);
    $result = truncateForPrint2($name, $maxChars);
    $withoutEllipsis = mb_substr($result, 0, -1, 'UTF-8');
    $len = mb_strlen($withoutEllipsis, 'UTF-8');
    if ($len !== $maxChars) {
        throw new \RuntimeException("Expected $maxChars chars before ellipsis, got: $len");
    }
});

runTest('testNameOneOverLimitTruncated', function() {
    global $maxChars;
    $name = str_repeat('А', $maxChars + 1); // 29 символов
    $result = truncateForPrint2($name, $maxChars);
    $ellipsis = '…';
    if (mb_substr($result, -1, null, 'UTF-8') !== $ellipsis) {
        throw new \RuntimeException("Expected truncation for 29 chars");
    }
});

runTest('testCyrillicMultibyteHandled', function() {
    global $maxChars;
    $name = str_repeat('Ж', 35);
    $result = truncateForPrint2($name, $maxChars);
    $ellipsis = '…';
    if (mb_substr($result, -1, null, 'UTF-8') !== $ellipsis) {
        throw new \RuntimeException("Expected ellipsis for cyrillic 35 chars");
    }
    $withoutEllipsis = mb_substr($result, 0, -1, 'UTF-8');
    $len = mb_strlen($withoutEllipsis, 'UTF-8');
    if ($len !== $maxChars) {
        throw new \RuntimeException("Expected $maxChars cyrillic chars, got: $len");
    }
});

// ------------------------------------------------------------------
// Проверки содержимого файла views/barcode/print2.php
// ------------------------------------------------------------------
echo "--- Проверки содержимого print2.php ---\n";

runTest('testPrint2ViewUsesMbStrlen', function() {
    global $viewContent;
    if ($viewContent === false) {
        throw new \RuntimeException("Не удалось прочитать views/barcode/print2.php");
    }
    if (strpos($viewContent, "mb_strlen(\$productName, 'UTF-8')") === false) {
        throw new \RuntimeException("print2.php должен использовать mb_strlen");
    }
});

runTest('testPrint2ViewUsesMbSubstr', function() {
    global $viewContent;
    if (strpos($viewContent, "mb_substr(\$productName, 0, \$maxChars, 'UTF-8')") === false) {
        throw new \RuntimeException("print2.php должен использовать mb_substr");
    }
});

runTest('testPrint2ViewHasCorrectMaxChars', function() {
    global $viewContent;
    if (strpos($viewContent, '$maxChars = 28') === false) {
        throw new \RuntimeException("print2.php должен задавать \$maxChars = 28");
    }
});

runTest('testPrint2ViewAddsEllipsis', function() {
    global $viewContent;
    if (strpos($viewContent, "'…'") === false) {
        throw new \RuntimeException("print2.php должен добавлять символ многоточия '…'");
    }
});

runTest('testPrint2ViewUsesHtmlEncode', function() {
    global $viewContent;
    if (strpos($viewContent, 'Html::encode($displayName)') === false) {
        throw new \RuntimeException("print2.php должен использовать Html::encode(\$displayName)");
    }
});

echo "\n=== ИТОГО ===\n";
foreach ($results as $r) {
    echo "$r\n";
}
echo "\nВсего тестов: $totalTests | Прошло: $passedTests | Упало: $failedTests\n";
exit($failedTests > 0 ? 1 : 0);
