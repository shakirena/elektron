<?php

namespace tests\unit\models;

/**
 * Unit tests for Story #28 (Feature #27: Отчёт «Движение товара»).
 *
 * Проверяет, что после расширения `returnp.data` с DATE до DATETIME
 * SellController::actionReceivedReturn() пишет значение в формате
 * "Y-m-d H:i:s" (полный datetime), а не "Y-m-d" (только дата).
 *
 * Тест не подключается к БД. Он проверяет:
 *   1. Формат "Y-m-d H:i:s" валиден и содержит секцию времени.
 *   2. Старый формат "Y-m-d" не содержит времени (подтверждение
 *      корректности исправления).
 *   3. В controllers/SellController.php после исправления
 *      присутствует именно date("Y-m-d H:i:s"), а не date("Y-m-d").
 */
class ReturnpDatetimeTest extends \Codeception\Test\Unit
{
    /**
     * Абсолютный путь к SellController.php от директории теста
     * (tests/unit/models -> корень проекта -> controllers/…).
     *
     * @return string
     */
    private function sellControllerPath()
    {
        return dirname(__DIR__, 3) . '/controllers/SellController.php';
    }

    // ------------------------------------------------------------------
    // Формат date() — подтверждение нового и опровержение старого
    // ------------------------------------------------------------------

    public function testDatetimeFormatContainsTime()
    {
        $dt = date("Y-m-d H:i:s");
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $dt,
            'date("Y-m-d H:i:s") должен возвращать полный datetime'
        );
    }

    public function testDateOnlyFormatLacksTime()
    {
        $d = date("Y-m-d");
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}$/',
            $d,
            'date("Y-m-d") должен возвращать только дату'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/ \d{2}:\d{2}:\d{2}/',
            $d,
            'date("Y-m-d") не должен содержать время — подтверждаем что старый формат неверен'
        );
    }

    public function testDatetimeStringLongerThanDateOnly()
    {
        $dt = date("Y-m-d H:i:s");
        $d  = date("Y-m-d");
        $this->assertGreaterThan(
            strlen($d),
            strlen($dt),
            'Полный datetime длиннее даты (10+ символов, содержит пробел и время)'
        );
        $this->assertStringContainsString(' ', $dt, 'DATETIME должен содержать пробел между датой и временем');
    }

    // ------------------------------------------------------------------
    // Проверка исходного кода SellController.php
    // ------------------------------------------------------------------

    public function testSellControllerUsesDatetimeFormat()
    {
        $path = $this->sellControllerPath();
        $this->assertFileExists($path, 'SellController.php должен существовать');

        $content = file_get_contents($path);
        $this->assertNotFalse($content, 'SellController.php должен быть читаемым');

        $this->assertStringContainsString(
            '$returnp->data=date("Y-m-d H:i:s")',
            $content,
            'actionReceivedReturn() должен писать date("Y-m-d H:i:s") в returnp.data'
        );
    }

    public function testSellControllerDoesNotUseDateOnlyForReturnp()
    {
        $path    = $this->sellControllerPath();
        $content = file_get_contents($path);
        $this->assertNotFalse($content, 'SellController.php должен быть читаемым');

        $this->assertStringNotContainsString(
            '$returnp->data=date("Y-m-d")',
            $content,
            'В actionReceivedReturn() не должно остаться старого формата date("Y-m-d")'
        );
    }
}
