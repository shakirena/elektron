<?php

use yii\db\Migration;
use app\models\AuditTriggers;

/**
 * audit_log.created_at берётся из @app_now (время PHP), а не NOW() MySQL.
 * Часовые пояса PHP и MySQL могут отличаться — тогда записи до снимка попадали
 * в разбор после него. Пересоздаёт триггеры.
 */
class m261003_120000_audit_app_time extends Migration
{
    public function safeUp()
    {
        AuditTriggers::drop($this->db);
        AuditTriggers::create($this->db);
    }

    public function safeDown()
    {
        return true;
    }
}
