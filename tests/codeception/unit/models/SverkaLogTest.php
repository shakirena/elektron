<?php

namespace tests\codeception\unit\models;

use app\models\SverkaLog;
use yii\codeception\TestCase;

/**
 * Unit-тесты для модели SverkaLog.
 * Feature #27: Отчёт «Движение товара».
 * Story #29: Таблица sverka_log — лог истории сверок.
 *
 * Подход: без подключения к БД.
 *   - Проверяется публичный контракт модели (tableName, наличие метода logChange).
 *   - Проверяется формула delta = qty_after - qty_before на объекте модели
 *     без вызова save() — чистая арифметика, DB не нужна.
 */
class SverkaLogTest extends TestCase
{
    public function testTableName()
    {
        $this->assertEquals('sverka_log', SverkaLog::tableName());
    }

    public function testLogChangeMethodExists()
    {
        $this->assertTrue(
            method_exists(SverkaLog::class, 'logChange'),
            'Метод SverkaLog::logChange() должен существовать'
        );
    }

    public function testLogChangeIsStatic()
    {
        $ref = new \ReflectionMethod(SverkaLog::class, 'logChange');
        $this->assertTrue($ref->isStatic(), 'logChange() должен быть static');
        $this->assertTrue($ref->isPublic(), 'logChange() должен быть public');
    }

    public function testLogChangeHasFiveParameters()
    {
        $ref = new \ReflectionMethod(SverkaLog::class, 'logChange');
        $this->assertEquals(
            5,
            $ref->getNumberOfParameters(),
            'logChange() должен принимать 5 параметров: idProduct, idStore, qtyBefore, qtyAfter, idUser'
        );
    }

    public function testDeltaCalculationPositive()
    {
        $log = new SverkaLog();
        $log->qty_before = 10.0;
        $log->qty_after = 15.0;
        $log->delta = $log->qty_after - $log->qty_before;
        $this->assertEquals(5.0, $log->delta);
    }

    public function testDeltaCalculationNegative()
    {
        $log = new SverkaLog();
        $log->qty_before = 20.0;
        $log->qty_after = 5.0;
        $log->delta = $log->qty_after - $log->qty_before;
        $this->assertEquals(-15.0, $log->delta);
    }

    public function testDeltaCalculationZero()
    {
        $log = new SverkaLog();
        $log->qty_before = 7.5;
        $log->qty_after = 7.5;
        $log->delta = $log->qty_after - $log->qty_before;
        $this->assertEquals(0.0, $log->delta);
    }

    public function testRulesContainRequiredFields()
    {
        $model = new SverkaLog();
        $rules = $model->rules();
        $required = [];
        foreach ($rules as $rule) {
            if (isset($rule[1]) && $rule[1] === 'required') {
                $required = array_merge($required, (array) $rule[0]);
            }
        }
        $this->assertContains('id_product', $required);
        $this->assertContains('id_store', $required);
        $this->assertContains('id_user', $required);
        $this->assertContains('datetime', $required);
    }

    public function testAttributeLabelsCovertAllPersistedFields()
    {
        $labels = (new SverkaLog())->attributeLabels();
        foreach (['id', 'id_product', 'id_store', 'qty_before', 'qty_after', 'delta', 'id_user', 'datetime'] as $attr) {
            $this->assertArrayHasKey($attr, $labels, "Ярлык для {$attr} должен быть определён");
        }
    }
}
