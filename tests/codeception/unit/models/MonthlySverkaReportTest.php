<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;
use app\models\MonthlySverkaReport;

require_once __DIR__ . '/../../../../models/MonthlySverkaReport.php';

/**
 * Unit tests для «Aylıq sverka»: чистые функции netice() и compare().
 * БД не нужна — Yii-приложение не создаётся.
 */
class MonthlySverkaReportTest extends TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    private function flows(array $override = [])
    {
        return $override + [
            'profit' => 0, 'returns_margin' => 0, 'expenses' => 0, 'other_income' => 0,
            'arrivals' => 0, 'cogs' => 0, 'supplier_returns' => 0, 'returns_cost' => 0,
        ];
    }

    private function state($stok, $portfel, $kassa, $borc)
    {
        return [
            'stok' => $stok, 'portfel' => $portfel, 'kassa' => $kassa, 'borc' => $borc,
            'netice' => MonthlySverkaReport::netice($stok, $portfel, $kassa, $borc),
        ];
    }

    public function testNeticeSubtractsBorc()
    {
        $this->assertEquals(529235.0, MonthlySverkaReport::netice(380950, 187686, 29417, 68818));
    }

    public function testNoDifferenceWhenChangeEqualsNetProfit()
    {
        $base = $this->state(1000, 500, 200, 300);   // 1400
        $now = $this->state(1100, 500, 250, 300);    // 1550
        $c = MonthlySverkaReport::compare($base, $now, $this->flows(['profit' => 170, 'expenses' => 20]));

        $this->assertEquals(150.0, $c['net_profit']);
        $this->assertEquals(1550.0, $c['expected']);
        $this->assertEquals(0.0, $c['diff']);
    }

    public function testShortageIsNegativeDiff()
    {
        $base = $this->state(1000, 0, 0, 0);
        $now = $this->state(900, 0, 0, 0);
        $c = MonthlySverkaReport::compare($base, $now, $this->flows());

        $this->assertEquals(-100.0, $c['diff']);
    }

    public function testReturnsMarginAndOtherIncomeAffectExpected()
    {
        $base = $this->state(0, 0, 1000, 0);
        $now = $this->state(0, 0, 1000, 0);
        $c = MonthlySverkaReport::compare($base, $now, $this->flows(['profit' => 100, 'returns_margin' => 30, 'other_income' => 50]));

        $this->assertEquals(120.0, $c['net_profit']);
        $this->assertEquals(-120.0, $c['diff']);
    }

    public function testStockCheckUsesArrivalsCogsAndReturns()
    {
        $base = $this->state(1000, 0, 0, 0);
        $now = $this->state(1150, 0, 0, 0);
        $c = MonthlySverkaReport::compare($base, $now, $this->flows([
            'arrivals' => 500, 'cogs' => 300, 'supplier_returns' => 100, 'returns_cost' => 40,
        ]));

        $this->assertEquals(140.0, $c['stock_expected_delta']);
        $this->assertEquals(150.0, $c['stock_actual_delta']);
        $this->assertEquals(10.0, $c['stock_diff']);
    }

    public function testComponentsDelta()
    {
        $c = MonthlySverkaReport::compare($this->state(10, 20, 30, 40), $this->state(15, 18, 30, 50), $this->flows());

        $this->assertEquals(5.0, $c['components']['stok']['delta']);
        $this->assertEquals(-2.0, $c['components']['portfel']['delta']);
        $this->assertEquals(0.0, $c['components']['kassa']['delta']);
        $this->assertEquals(10.0, $c['components']['borc']['delta']);
    }
}
