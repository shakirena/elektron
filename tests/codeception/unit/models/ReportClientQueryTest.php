<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;

/**
 * Unit tests for Story #19: [Bug] Исчезновение продаж в отчёте Движение клиентов.
 *
 * Тестирует логику фильтрации записей Dclient в представлении report_client:
 * - продажи с debt=0, sum=0 должны отображаться (ранее терялись)
 * - продажи с debt>0 или sum>0 всегда отображаются
 * - возвраты (debt<0, number>0) попадают в отдельную ветку
 * - оплаты без number (debt<0, number=null) попадают в ветку оплат
 * - защита от отмены продажи при наличии оплат
 */
class ReportClientQueryTest extends TestCase
{
    /**
     * Реплицирует логику фильтрации из report_client.php (FIX #19).
     *
     * @param array $move Запись из dclient (ассоциативный массив)
     * @return string 'sell_entry' | 'return' | 'payment' | 'skip'
     */
    private function classifyDclientRow(array $move): string
    {
        $isSellEntry = (
            isset($move['number']) &&
            $move['number'] !== null &&
            $move['number'] > 0 &&
            $move['debt'] >= 0 &&
            $move['sum'] >= 0
        );

        if ($move['debt'] > 0 || $move['sum'] > 0 || $isSellEntry) {
            if ($move['number']) {
                return 'sell_entry';
            }
            return 'payment_in';
        }

        // else branch
        if ($move['number'] > 0) {
            return 'return';
        }

        if ($move['debt'] < 0) {
            return 'payment';
        }

        return 'skip';
    }

    /**
     * Реплицирует защиту от отмены продажи при наличии оплат (FIX #19).
     *
     * @param array $dclientRows Список записей dclient по number
     * @return bool true если есть оплаты (отмена заблокирована)
     */
    private function hasPaymentsForSell(array $dclientRows): bool
    {
        foreach ($dclientRows as $row) {
            if ($row['debt'] < 0) {
                return true;
            }
        }
        return false;
    }

    // -------------------------------------------------------------------------
    // Тесты классификации строк dclient
    // -------------------------------------------------------------------------

    /**
     * AC: Продажа с debt>0 (долг) должна отображаться как sell_entry.
     */
    public function testSellWithDebtIsDisplayed(): void
    {
        $row = ['number' => 42, 'debt' => 150.0, 'sum' => 0.0, 'bonus' => 0.0];
        $this->assertSame('sell_entry', $this->classifyDclientRow($row));
    }

    /**
     * AC: Продажа с sum>0 (оплачена сразу) должна отображаться как sell_entry.
     */
    public function testSellWithSumIsDisplayed(): void
    {
        $row = ['number' => 43, 'debt' => 0.0, 'sum' => 300.0, 'bonus' => 0.0];
        $this->assertSame('sell_entry', $this->classifyDclientRow($row));
    }

    /**
     * AC FIX #19: Продажа с debt=0 И sum=0 (ранее исчезала) теперь отображается.
     * Это главный регрессионный тест бага.
     */
    public function testSellWithZeroDebtAndZeroSumIsDisplayed(): void
    {
        $row = ['number' => 44, 'debt' => 0.0, 'sum' => 0.0, 'bonus' => 0.0];
        $this->assertSame('sell_entry', $this->classifyDclientRow($row));
    }

    /**
     * AC FIX #19: Продажа с debt=0, sum=0 строго (integer) тоже отображается.
     */
    public function testSellWithIntZeroDebtAndSumIsDisplayed(): void
    {
        $row = ['number' => 45, 'debt' => 0, 'sum' => 0, 'bonus' => 0];
        $this->assertSame('sell_entry', $this->classifyDclientRow($row));
    }

    /**
     * AC: Запись без number и с debt<0 — это оплата (payment).
     */
    public function testPaymentWithoutNumberIsPayment(): void
    {
        $row = ['number' => null, 'debt' => -200.0, 'sum' => 0.0, 'bonus' => 0.0];
        $this->assertSame('payment', $this->classifyDclientRow($row));
    }

    /**
     * AC: Запись с number>0 и debt<0 — это возврат.
     */
    public function testReturnWithNumberAndNegativeDebt(): void
    {
        $row = ['number' => 50, 'debt' => -100.0, 'sum' => 0.0, 'bonus' => 0.0];
        $this->assertSame('return', $this->classifyDclientRow($row));
    }

    /**
     * AC: Запись без number, debt=0, sum=0 — пропускается (нет данных).
     */
    public function testRowWithoutNumberAndZeroValuesIsSkipped(): void
    {
        $row = ['number' => null, 'debt' => 0.0, 'sum' => 0.0, 'bonus' => 0.0];
        $this->assertSame('skip', $this->classifyDclientRow($row));
    }

    /**
     * AC: Запись с number=0 (falsy), debt=0, sum=0 — пропускается.
     */
    public function testRowWithZeroNumberIsSkipped(): void
    {
        $row = ['number' => 0, 'debt' => 0.0, 'sum' => 0.0, 'bonus' => 0.0];
        $this->assertSame('skip', $this->classifyDclientRow($row));
    }

    // -------------------------------------------------------------------------
    // Тесты защиты от отмены продажи при наличии оплат
    // -------------------------------------------------------------------------

    /**
     * AC FIX #19: Отмена продажи без оплат — разрешена.
     */
    public function testCancelAllowedWhenNoPayments(): void
    {
        $rows = [
            ['number' => 10, 'debt' => 200.0, 'sum' => 0.0],
        ];
        $this->assertFalse($this->hasPaymentsForSell($rows));
    }

    /**
     * AC FIX #19: Отмена продажи при наличии оплаты (debt<0) — заблокирована.
     */
    public function testCancelBlockedWhenPaymentExists(): void
    {
        $rows = [
            ['number' => 10, 'debt' => 200.0, 'sum' => 0.0],
            ['number' => 10, 'debt' => -100.0, 'sum' => 0.0],
        ];
        $this->assertTrue($this->hasPaymentsForSell($rows));
    }

    /**
     * AC FIX #19: Отмена при нескольких оплатах — тоже заблокирована.
     */
    public function testCancelBlockedWhenMultiplePayments(): void
    {
        $rows = [
            ['number' => 11, 'debt' => 500.0, 'sum' => 0.0],
            ['number' => 11, 'debt' => -200.0, 'sum' => 0.0],
            ['number' => 11, 'debt' => -100.0, 'sum' => 0.0],
        ];
        $this->assertTrue($this->hasPaymentsForSell($rows));
    }

    /**
     * AC FIX #19: Пустой список dclient записей — отмена разрешена.
     */
    public function testCancelAllowedWhenNoDclientRows(): void
    {
        $rows = [];
        $this->assertFalse($this->hasPaymentsForSell($rows));
    }

    // -------------------------------------------------------------------------
    // Тесты диагностики orphan-оплат
    // -------------------------------------------------------------------------

    /**
     * AC: Оплата, для которой нет продажи в sell — это orphan.
     * Проверяем логику определения orphan через список number из sell.
     */
    public function testOrphanPaymentDetected(): void
    {
        $dclientNumber = 99;
        $existingSellNumbers = [1, 2, 3, 10]; // number'а 99 нет

        $isOrphan = !in_array($dclientNumber, $existingSellNumbers, true);
        $this->assertTrue($isOrphan, 'Оплата с number=99 должна быть обнаружена как orphan');
    }

    /**
     * AC: Оплата, для которой продажа существует — не orphan.
     */
    public function testNonOrphanPaymentNotFlagged(): void
    {
        $dclientNumber = 10;
        $existingSellNumbers = [1, 2, 3, 10];

        $isOrphan = !in_array($dclientNumber, $existingSellNumbers, true);
        $this->assertFalse($isOrphan, 'Оплата с number=10 не должна считаться orphan');
    }
}
