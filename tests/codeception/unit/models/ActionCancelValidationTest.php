<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;

/**
 * Unit tests for Story #2: actionCancel null number protection.
 *
 * Tests validate the guard logic that must be added to
 * SellController::actionCancel() to prevent mass-delete of dclient rows
 * when $number is null, zero, or non-numeric.
 */
class ActionCancelValidationTest extends TestCase
{
    /**
     * Helper that replicates the validation guard from actionCancel.
     * Returns true when the parameter is valid, false (or throws) otherwise.
     *
     * @param mixed $number
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    private function validateCancelNumber($number)
    {
        if (!$number || !is_numeric($number) || (int)$number <= 0) {
            throw new \yii\web\BadRequestHttpException('Invalid number parameter');
        }
        return true;
    }

    // --- invalid inputs: must throw BadRequestHttpException ---

    public function testNullNumberThrows()
    {
        $this->expectException(\yii\web\BadRequestHttpException::class);
        $this->validateCancelNumber(null);
    }

    public function testEmptyStringThrows()
    {
        $this->expectException(\yii\web\BadRequestHttpException::class);
        $this->validateCancelNumber('');
    }

    public function testZeroThrows()
    {
        $this->expectException(\yii\web\BadRequestHttpException::class);
        $this->validateCancelNumber(0);
    }

    public function testStringZeroThrows()
    {
        $this->expectException(\yii\web\BadRequestHttpException::class);
        $this->validateCancelNumber('0');
    }

    public function testNegativeNumberThrows()
    {
        $this->expectException(\yii\web\BadRequestHttpException::class);
        $this->validateCancelNumber(-5);
    }

    public function testAlphaStringThrows()
    {
        $this->expectException(\yii\web\BadRequestHttpException::class);
        $this->validateCancelNumber('abc');
    }

    public function testFloatStringThrows()
    {
        $this->expectException(\yii\web\BadRequestHttpException::class);
        $this->validateCancelNumber('1.5');
    }

    // --- valid inputs: must pass without exception ---

    public function testPositiveIntegerPasses()
    {
        $result = $this->validateCancelNumber(42);
        $this->assertTrue($result);
    }

    public function testPositiveStringIntegerPasses()
    {
        $result = $this->validateCancelNumber('42');
        $this->assertTrue($result);
    }

    public function testOneIsValid()
    {
        $result = $this->validateCancelNumber(1);
        $this->assertTrue($result);
    }

    /**
     * After validation, number must be cast to int (no SQL type confusion).
     */
    public function testNumberCastToInt()
    {
        $number = '99';
        if (!$number || !is_numeric($number) || (int)$number <= 0) {
            $this->fail('Should not throw for valid number');
        }
        $number = (int)$number;
        $this->assertSame(99, $number);
        $this->assertIsInt($number);
    }
}
