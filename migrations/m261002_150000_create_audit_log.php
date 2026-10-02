<?php

use yii\db\Migration;
use app\models\AuditTriggers;

/**
 * Журнал изменений (audit_log) для разбора расхождений «Aylıq sverka».
 *
 * Пишется триггерами MySQL, а не поведением ActiveRecord: в коде много
 * updateAll()/deleteAll() и правок через phpMyAdmin, которые AR-события не видят.
 *
 * Пользователь и маршрут передаются из приложения переменными сессии MySQL
 * @app_user_id / @app_route (config/web.php, on beforeAction). Пустой route —
 * изменение сделано не через программу.
 *
 * d_* — влияние строки на компоненты итога Nəticə = Stok + Portfel + Kassa − Borc
 * (и на прибыль): для INSERT — новое значение, для DELETE — минус старое,
 * для UPDATE — разница.
 *
 * Триггеры строит app\models\AuditTriggers (совместимо с MySQL 5.6 — без JSON-функций).
 */
class m261002_150000_create_audit_log extends Migration
{
    public function safeUp()
    {
        if ($this->db->getTableSchema('audit_log', true) === null) {
            $this->createTable('audit_log', [
                'id'         => $this->bigPrimaryKey(),
                'created_at' => $this->dateTime()->notNull(),
                'user_id'    => $this->integer(),
                'route'      => $this->string(100),
                'tbl'        => $this->string(20)->notNull(),
                'row_id'     => $this->integer()->notNull(),
                'action'     => $this->char(1)->notNull(),
                'doc_date'   => $this->dateTime(),
                'd_stok'     => $this->decimal(15, 2)->notNull()->defaultValue(0),
                'd_portfel'  => $this->decimal(15, 2)->notNull()->defaultValue(0),
                'd_kassa'    => $this->decimal(15, 2)->notNull()->defaultValue(0),
                'd_borc'     => $this->decimal(15, 2)->notNull()->defaultValue(0),
                'd_profit'   => $this->decimal(15, 2)->notNull()->defaultValue(0),
                'old_data'   => $this->text(),
                'new_data'   => $this->text(),
            ]);
            $this->createIndex('idx_audit_log_created', 'audit_log', 'created_at');
            $this->createIndex('idx_audit_log_row', 'audit_log', ['tbl', 'row_id']);
        }

        AuditTriggers::drop($this->db);
        AuditTriggers::create($this->db);
    }

    public function safeDown()
    {
        AuditTriggers::drop($this->db);
        $this->dropTable('audit_log');
    }
}
