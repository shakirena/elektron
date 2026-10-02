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

    public function testNeticeSubtractsClientOverpayments()
    {
        // переплаты клиентов — расход: знак входного значения не важен
        $this->assertEquals(529235.0 - 15636.43, MonthlySverkaReport::netice(380950, 187686, 29417, 68818, -15636.43));
        $this->assertEquals(529235.0 - 15636.43, MonthlySverkaReport::netice(380950, 187686, 29417, 68818, 15636.43));
    }

    public function testOverpaymentGrowthIsNegativeComponentDelta()
    {
        $base = ['stok' => 0, 'portfel' => 0, 'portfel_minus' => -100, 'kassa' => 0, 'borc' => 0, 'netice' => MonthlySverkaReport::netice(0, 0, 0, 0, -100)];
        $now = ['stok' => 0, 'portfel' => 0, 'portfel_minus' => -150, 'kassa' => 50, 'borc' => 0, 'netice' => MonthlySverkaReport::netice(0, 0, 50, 0, -150)];
        $c = MonthlySverkaReport::compare($base, $now, $this->flows());

        // клиент переплатил 50: касса +50, переплаты −50 → итог не меняется, расхождения нет
        $this->assertEquals(-50.0, $c['components']['portfel_minus']['delta']);
        $this->assertEquals(0.0, $c['diff']);
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

    private function logRow(array $override = [])
    {
        return $override + [
            'tbl' => 'costs', 'row_id' => 1, 'action' => 'I', 'route' => 'costs/create',
            'doc_date' => '2026-10-05 10:00:00',
            'd_stok' => 0, 'd_portfel' => 0, 'd_kassa' => 0, 'd_borc' => 0, 'd_profit' => 0,
            'old_data' => null, 'new_data' => '{}',
        ];
    }

    const BASE = '2026-10-02 18:00:00';

    public function testNeticeEffectSubtractsBorc()
    {
        $this->assertEquals(-30.0, MonthlySverkaReport::neticeEffect($this->logRow(['d_stok' => 100, 'd_kassa' => 20, 'd_borc' => 150])));
    }

    public function testClassifyNormalSale()
    {
        $this->assertEquals(['Satış', false], MonthlySverkaReport::classify($this->logRow(['tbl' => 'sell', 'route' => 'sell/received', 'd_profit' => 5]), self::BASE));
    }

    public function testClassifyOutsideProgram()
    {
        $this->assertEquals(['Proqramdan kənar dəyişiklik', true], MonthlySverkaReport::classify($this->logRow(['route' => null, 'd_kassa' => 5]), self::BASE));
    }

    public function testClassifyDeleteWithEffect()
    {
        $row = $this->logRow(['tbl' => 'arrival', 'action' => 'D', 'route' => 'sell/received', 'd_stok' => -500]);
        $this->assertEquals(['Silinmə', true], MonthlySverkaReport::classify($row, self::BASE));
    }

    public function testClassifyBackdatedInsert()
    {
        $row = $this->logRow(['tbl' => 'debt', 'route' => 'arrival/received-return', 'doc_date' => '2026-07-29 13:16:33', 'd_borc' => -679.5]);
        $this->assertEquals(['Keçmiş tarixlə', true], MonthlySverkaReport::classify($row, self::BASE));
    }

    public function testClassifyCancelAndInventory()
    {
        $this->assertEquals(['Ləğv', true], MonthlySverkaReport::classify($this->logRow(['route' => 'arrival/cancel', 'd_borc' => -10]), self::BASE));
        $this->assertEquals(['İnventarizasiya', true], MonthlySverkaReport::classify($this->logRow(['route' => 'sverka/received', 'd_stok' => -10]), self::BASE));
    }

    public function testUnexplainedExpenseIsExplained()
    {
        $row = $this->logRow(['d_kassa' => -500, 'new_data' => '{"id_type": 60}']);
        $this->assertEquals(0.0, MonthlySverkaReport::unexplained($row, self::BASE, [60], []));
    }

    public function testUnexplainedClientPaymentNotInExpected()
    {
        $row = $this->logRow(['d_kassa' => 300, 'new_data' => '{"id_type": 1}']);
        $this->assertEquals(300.0, MonthlySverkaReport::unexplained($row, self::BASE, [60], []));
    }

    public function testUnexplainedSaleProfitSubtracted()
    {
        $row = $this->logRow(['tbl' => 'sell', 'route' => 'sell/received', 'd_profit' => 40, 'new_data' => '{}']);
        $this->assertEquals(-40.0, MonthlySverkaReport::unexplained($row, self::BASE, [], []));
    }

    public function testUnexplainedBackdatedCountsFullEffect()
    {
        $row = $this->logRow(['doc_date' => '2026-09-30 10:00:00', 'd_kassa' => -500, 'new_data' => '{"id_type": 60}']);
        $this->assertEquals(-500.0, MonthlySverkaReport::unexplained($row, self::BASE, [60], []));
    }

    public function testUnexplainedReturnMargin()
    {
        $row = $this->logRow(['tbl' => 'returnp', 'route' => 'sell/return-sell-received', 'new_data' => '{"id_product": 7, "price": 60, "quantity": 2}']);
        // ожидаемый итог уменьшается на маржу (60−45)×2 = 30 → у самой строки возврата влияния нет, необъяснено +30
        $this->assertEquals(30.0, MonthlySverkaReport::unexplained($row, self::BASE, [], [7 => 45.0]));
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
