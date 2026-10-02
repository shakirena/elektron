<?php

use yii\db\Migration;
use app\models\AuditTriggers;

/**
 * Пересоздаёт триггеры audit_log в варианте, совместимом с MySQL 5.6.
 *
 * Первая версия m261002_150000 использовала JSON_OBJECT (есть только в MySQL 5.7.8+ / MariaDB).
 * В MySQL 5.6 такие триггеры создаются без ошибки, но падают при каждой записи
 * в arrival/sell/costs/debt/dclient/returnp/return_arrival — программа переставала работать.
 * Эта миграция нужна там, где m261002_150000 уже была применена в старом виде.
 */
class m261003_090000_audit_triggers_mysql56 extends Migration
{
    public function safeUp()
    {
        AuditTriggers::drop($this->db);
        AuditTriggers::create($this->db);
    }

    public function safeDown()
    {
        // Откат к версии с JSON_OBJECT не делаем — она ломает MySQL 5.6.
        return true;
    }
}
