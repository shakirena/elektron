<?php

use yii\db\Migration;
use app\models\AuditTriggers;

/**
 * audit_log: в журнал пишутся только подтверждённые строки —
 * arrival с received=1 и sell с sold=1 (до или после изменения).
 * Черновики прихода/продажи в итог не входят и только засоряли журнал.
 * Заодно удаляет уже записанные строки черновиков прихода.
 */
class m261003_100000_audit_only_confirmed extends Migration
{
    public function safeUp()
    {
        AuditTriggers::drop($this->db);
        AuditTriggers::create($this->db);

        // Строки, где приход не был проведён ни до, ни после изменения.
        // Оба формата JSON: старый (JSON_OBJECT: "received": 0) и новый ("received":"0").
        $draft = function ($col) {
            return "($col IS NULL OR $col LIKE '%\"received\": 0,%' OR $col LIKE '%\"received\":\"0\"%')";
        };
        $this->execute("DELETE FROM audit_log WHERE tbl='arrival' AND " . $draft('old_data') . ' AND ' . $draft('new_data'));
    }

    public function safeDown()
    {
        return true;
    }
}
