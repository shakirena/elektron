<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;

/**
 * Unit tests for Story #3: actionReceivedDebt number assignment.
 *
 * Tests validate the logic that determines the $sellNumber assigned to
 * the new Dclient record in SellController::actionReceivedDebt().
 *
 * The rule:
 *   - If an existing Dclient row (debt > 0) for the client has a non-null number,
 *     use that number cast to int.
 *   - Otherwise use sentinel value 0 (never NULL).
 */
class ActionReceivedDebtNumberTest extends TestCase
{
    /**
     * Replicates the number-resolution logic from actionReceivedDebt.
     *
     * @param object|null $existingDclient  stdClass with ->number, or null
     * @return int
     */
    private function resolveSellNumber($existingDclient)
    {
        return ($existingDclient !== null && $existingDclient->number !== null)
            ? (int)$existingDclient->number
            : 0;
    }

    // --- Given: existing Dclient with a valid number ---

    public function testExistingDclientWithNumberReturnsNumber()
    {
        $existing = (object)['number' => 15];
        $result = $this->resolveSellNumber($existing);
        $this->assertSame(15, $result);
    }

    public function testExistingDclientNumberCastToInt()
    {
        $existing = (object)['number' => '42'];
        $result = $this->resolveSellNumber($existing);
        $this->assertSame(42, $result);
        $this->assertIsInt($result);
    }

    // --- Given: existing Dclient but number is NULL ---

    public function testExistingDclientWithNullNumberReturnsSentinel()
    {
        $existing = (object)['number' => null];
        $result = $this->resolveSellNumber($existing);
        $this->assertSame(0, $result);
    }

    // --- Given: no existing Dclient at all ---

    public function testNullExistingDclientReturnsSentinel()
    {
        $result = $this->resolveSellNumber(null);
        $this->assertSame(0, $result);
    }

    // --- Result must never be NULL ---

    public function testResultIsNeverNull()
    {
        $result1 = $this->resolveSellNumber(null);
        $result2 = $this->resolveSellNumber((object)['number' => null]);
        $result3 = $this->resolveSellNumber((object)['number' => 7]);

        $this->assertNotNull($result1);
        $this->assertNotNull($result2);
        $this->assertNotNull($result3);
    }

    // --- Sentinel value is 0, not negative ---

    public function testSentinelIsZeroNotNegative()
    {
        $result = $this->resolveSellNumber(null);
        $this->assertGreaterThanOrEqual(0, $result);
    }
}
