<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;

/**
 * Unit-тесты для логики обрезки названий товаров в шаблоне print2.php
 * Feature #24: Обрезка длинных названий при печати чека 40x20мм
 * Story #25: Обрезка длинного названия с добавлением многоточия
 * Story #26: Корректное отображение коротких названий без обрезки
 *
 * Подход: нет подключения к БД.
 * Тестирует логику truncate через inline-хелпер,
 * а также наличие корректного кода в views/barcode/print2.php.
 */
class Print2TruncateTest extends TestCase
{
    /** @var int */
    private $maxChars = 28;

    /**
     * Путь к файлу шаблона print2.php (от директории тестов 4 уровня вверх)
     *
     * @return string
     */
    private function print2ViewPath()
    {
        return dirname(__DIR__, 4) . '/views/barcode/print2.php';
    }

    /**
     * Хелпер-функция, имитирующая логику шаблона print2.php
     *
     * @param string $name
     * @return string
     */
    private function truncateForPrint2($name)
    {
        if (mb_strlen($name, 'UTF-8') > $this->maxChars) {
            return mb_substr($name, 0, $this->maxChars, 'UTF-8') . '…';
        }
        return $name;
    }

    // ------------------------------------------------------------------
    // Story #26: короткие названия отображаются без изменений
    // ------------------------------------------------------------------

    /**
     * Story #26: короткое название отображается без изменений
     */
    public function testShortNameUnchanged()
    {
        $name = 'Кабель HDMI 1м';
        $this->assertEquals($name, $this->truncateForPrint2($name));
    }

    /**
     * Story #26: название ровно 28 символов — без обрезки
     */
    public function testExactlyMaxCharsUnchanged()
    {
        $name = str_repeat('А', $this->maxChars); // ровно 28 символов
        $this->assertEquals($name, $this->truncateForPrint2($name));
    }

    /**
     * Story #26: пустое название — без изменений
     */
    public function testEmptyNameUnchanged()
    {
        $this->assertEquals('', $this->truncateForPrint2(''));
    }

    // ------------------------------------------------------------------
    // Story #25: длинные названия обрезаются с добавлением многоточия
    // ------------------------------------------------------------------

    /**
     * Story #25: длинное название обрезается с добавлением многоточия
     */
    public function testLongNameTruncated()
    {
        $name   = 'Кабель USB Type-C 1.5м нейлон чёрный';
        $result = $this->truncateForPrint2($name);
        $this->assertStringEndsWith('…', $result);
        // mb_strlen результата = maxChars + 1 (символ многоточия)
        $this->assertEquals($this->maxChars + 1, mb_strlen($result, 'UTF-8'));
    }

    /**
     * Story #25: длина обрезанной части ровно maxChars символов
     */
    public function testTruncatedLengthIsMaxChars()
    {
        $name            = str_repeat('Б', 50);
        $result          = $this->truncateForPrint2($name);
        $withoutEllipsis = mb_substr($result, 0, -1, 'UTF-8');
        $this->assertEquals($this->maxChars, mb_strlen($withoutEllipsis, 'UTF-8'));
    }

    /**
     * Story #25: название длиной 29 символов — обрезается (один символ сверх лимита)
     */
    public function testNameOneOverLimitTruncated()
    {
        $name = str_repeat('А', $this->maxChars + 1); // 29 символов
        $result = $this->truncateForPrint2($name);
        $this->assertStringEndsWith('…', $result);
    }

    /**
     * Story #25: кириллица обрабатывается корректно (многобайтовые символы)
     */
    public function testCyrillicMultibyteHandled()
    {
        $name = str_repeat('Ж', 35); // 35 кириллических символов
        $result = $this->truncateForPrint2($name);
        $this->assertStringEndsWith('…', $result);
        $withoutEllipsis = mb_substr($result, 0, -1, 'UTF-8');
        $this->assertEquals($this->maxChars, mb_strlen($withoutEllipsis, 'UTF-8'));
    }

    // ------------------------------------------------------------------
    // Проверки содержимого файла views/barcode/print2.php
    // ------------------------------------------------------------------

    /**
     * Файл print2.php использует mb_strlen для проверки длины
     */
    public function testPrint2ViewUsesMbStrlen()
    {
        $content = file_get_contents($this->print2ViewPath());
        $this->assertNotFalse($content, 'views/barcode/print2.php должен быть читаемым');
        $this->assertStringContainsString(
            "mb_strlen(\$productName, 'UTF-8')",
            $content,
            'print2.php должен использовать mb_strlen для проверки длины названия'
        );
    }

    /**
     * Файл print2.php использует mb_substr для обрезки
     */
    public function testPrint2ViewUsesMbSubstr()
    {
        $content = file_get_contents($this->print2ViewPath());
        $this->assertNotFalse($content, 'views/barcode/print2.php должен быть читаемым');
        $this->assertStringContainsString(
            "mb_substr(\$productName, 0, \$maxChars, 'UTF-8')",
            $content,
            'print2.php должен использовать mb_substr для обрезки названия'
        );
    }

    /**
     * Файл print2.php задаёт maxChars = 28
     */
    public function testPrint2ViewHasCorrectMaxChars()
    {
        $content = file_get_contents($this->print2ViewPath());
        $this->assertNotFalse($content, 'views/barcode/print2.php должен быть читаемым');
        $this->assertStringContainsString(
            '$maxChars = 28',
            $content,
            'print2.php должен задавать $maxChars = 28'
        );
    }

    /**
     * Файл print2.php добавляет многоточие при обрезке
     */
    public function testPrint2ViewAddsEllipsis()
    {
        $content = file_get_contents($this->print2ViewPath());
        $this->assertNotFalse($content, 'views/barcode/print2.php должен быть читаемым');
        $this->assertStringContainsString(
            "'…'",
            $content,
            "print2.php должен добавлять символ многоточия '…' при обрезке"
        );
    }

    /**
     * Файл print2.php использует Html::encode для безопасного вывода
     */
    public function testPrint2ViewUsesHtmlEncode()
    {
        $content = file_get_contents($this->print2ViewPath());
        $this->assertNotFalse($content, 'views/barcode/print2.php должен быть читаемым');
        $this->assertStringContainsString(
            'Html::encode($displayName)',
            $content,
            'print2.php должен использовать Html::encode($displayName) для вывода'
        );
    }
}
