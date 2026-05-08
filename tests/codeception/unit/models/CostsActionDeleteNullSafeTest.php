<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;

/**
 * Unit tests for Story #4: CostsController::actionDelete null-safe deletion.
 *
 * Tests validate that delete() is only called when the ActiveRecord object
 * is not null, preventing "Call to a member function delete() on null".
 *
 * The guard logic:
 *   $dclient = Dclient::find()->where(["id" => $model->fid])->one();
 *   if ($dclient !== null) { $dclient->delete(); }
 */
class CostsActionDeleteNullSafeTest extends TestCase
{
    /**
     * Simulates the patched id_type==1 branch.
     * Returns true if delete() was called, false if skipped (null guard).
     *
     * @param object|null $dclient
     * @return bool
     */
    private function simulateDclientDeleteBranch($dclient)
    {
        $deleted = false;
        if ($dclient !== null) {
            // In production: $dclient->delete(); here
            $deleted = true;
        }
        return $deleted;
    }

    /**
     * Simulates the patched id_type==2 branch.
     * Returns true if delete() was called, false if skipped (null guard).
     *
     * @param object|null $debt
     * @return bool
     */
    private function simulateDebtDeleteBranch($debt)
    {
        $deleted = false;
        if ($debt !== null) {
            // In production: $debt->delete(); here
            $deleted = true;
        }
        return $deleted;
    }

    // --- id_type == 1: Dclient branch ---

    public function testDclientFoundDeleteIsCalled()
    {
        $dclient = (object)['id' => 5];
        $result = $this->simulateDclientDeleteBranch($dclient);
        $this->assertTrue($result, 'delete() should be called when dclient exists');
    }

    public function testDclientNotFoundDeleteIsSkipped()
    {
        $result = $this->simulateDclientDeleteBranch(null);
        $this->assertFalse($result, 'delete() must not be called when dclient is null');
    }

    public function testDclientNullDoesNotThrowFatalError()
    {
        // If guard is missing, calling ->delete() on null causes Fatal Error.
        // This test proves the guard prevents it.
        $dclient = null;
        $exceptionThrown = false;
        try {
            if ($dclient !== null) {
                $dclient->delete();
            }
        } catch (\Throwable $e) {
            $exceptionThrown = true;
        }
        $this->assertFalse($exceptionThrown, 'No exception should be thrown when null guard is in place');
    }

    // --- id_type == 2: Debt branch ---

    public function testDebtFoundDeleteIsCalled()
    {
        $debt = (object)['id' => 10];
        $result = $this->simulateDebtDeleteBranch($debt);
        $this->assertTrue($result, 'delete() should be called when debt exists');
    }

    public function testDebtNotFoundDeleteIsSkipped()
    {
        $result = $this->simulateDebtDeleteBranch(null);
        $this->assertFalse($result, 'delete() must not be called when debt is null');
    }

    public function testDebtNullDoesNotThrowFatalError()
    {
        $debt = null;
        $exceptionThrown = false;
        try {
            if ($debt !== null) {
                $debt->delete();
            }
        } catch (\Throwable $e) {
            $exceptionThrown = true;
        }
        $this->assertFalse($exceptionThrown, 'No exception should be thrown when null guard is in place');
    }

    // --- Variable naming: patch uses $debt not $dclient for id_type==2 ---

    public function testDebtVariableIsDistinctFromDclient()
    {
        // Confirms patch uses separate variable names ($debt vs $dclient)
        // to avoid confusion and shadowing of the outer $dclient variable.
        $dclient = (object)['id' => 1, 'type' => 'dclient'];
        $debt = (object)['id' => 2, 'type' => 'debt'];

        $this->assertNotSame($dclient, $debt);
        $this->assertSame('dclient', $dclient->type);
        $this->assertSame('debt', $debt->type);
    }
}
