<?php

use yii\db\Migration;

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
 */
class m261002_150000_create_audit_log extends Migration
{
    /**
     * Таблица => [колонка даты документа, поля для old/new_data, [колонка d_* => выражение от строки X]]
     */
    private function tables()
    {
        return [
            'arrival' => ['datetime',
                ['id_product', 'id_store', 'quantity', 'rest', 'price', 'received', 'postponed', 'number', 'id_contr', 'datetime'],
                ['d_stok' => 'IF(X.received=1, X.price*X.rest, 0)']],
            'sell' => ['datetime',
                ['id_product', 'id_store', 'quantity', 'price', 'sum', 'price_ar', 'earnings', 'number', 'id_client', 'returnp', 'datetime'],
                ['d_profit' => 'X.earnings']],
            'costs' => ['datetime',
                ['id_type', 'sum', 'id_kassa', 'from_kassa', 'id_client', 'note', 'datetime'],
                ['d_kassa' => 'X.sum']],
            'debt' => ['datatime',
                ['id_contr', 'debt', 'number', 'note', 'datatime'],
                ['d_borc' => 'X.debt']],
            'dclient' => ['datetime',
                ['id_client', 'debt', 'sum', 'number', 'bonus', 'note', 'datetime'],
                ['d_portfel' => 'X.debt']],
            'returnp' => ['data',
                ['id_product', 'id_store', 'id_client', 'quantity', 'price', 'number', 'received', 'id_sell', 'data'],
                []],
            'return_arrival' => ['date',
                ['id_product', 'id_store', 'id_contr', 'quantity', 'price', 'received', 'date'],
                []],
        ];
    }

    public function safeUp()
    {
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

        foreach ($this->tables() as $table => list($dateCol, $fields, $effects)) {
            $json = function ($x) use ($fields) {
                $parts = [];
                foreach ($fields as $f) {
                    $parts[] = "'$f', $x.`$f`";
                }
                return 'JSON_OBJECT(' . implode(', ', $parts) . ')';
            };
            $eff = function ($x, $sign) use ($effects) {
                $cols = ['d_stok' => '0', 'd_portfel' => '0', 'd_kassa' => '0', 'd_borc' => '0', 'd_profit' => '0'];
                foreach ($effects as $col => $expr) {
                    $cols[$col] = $sign . 'COALESCE(' . str_replace('X.', "$x.", $expr) . ', 0)';
                }
                return $cols;
            };
            $insert = function ($rowId, $action, $docDate, array $d, $old, $new) use ($table) {
                return "INSERT INTO audit_log (created_at, user_id, route, tbl, row_id, action, doc_date, d_stok, d_portfel, d_kassa, d_borc, d_profit, old_data, new_data)
                        VALUES (NOW(), @app_user_id, @app_route, '$table', $rowId, '$action', $docDate,
                                {$d['d_stok']}, {$d['d_portfel']}, {$d['d_kassa']}, {$d['d_borc']}, {$d['d_profit']}, $old, $new)";
            };

            $this->execute("CREATE TRIGGER audit_{$table}_ai AFTER INSERT ON `$table` FOR EACH ROW "
                . $insert('NEW.id', 'I', "NEW.`$dateCol`", $eff('NEW', ''), 'NULL', $json('NEW')));

            $this->execute("CREATE TRIGGER audit_{$table}_ad AFTER DELETE ON `$table` FOR EACH ROW "
                . $insert('OLD.id', 'D', "OLD.`$dateCol`", $eff('OLD', '-'), $json('OLD'), 'NULL'));

            $changed = [];
            foreach ($fields as $f) {
                $changed[] = "NOT (OLD.`$f` <=> NEW.`$f`)";
            }
            $dNew = $eff('NEW', ''); $dOld = $eff('OLD', '');
            $dUpd = [];
            foreach ($dNew as $col => $expr) {
                $dUpd[$col] = "($expr) - ({$dOld[$col]})";
            }
            $this->execute("CREATE TRIGGER audit_{$table}_au AFTER UPDATE ON `$table` FOR EACH ROW BEGIN
                IF " . implode(' OR ', $changed) . " THEN
                    " . $insert('NEW.id', 'U', "NEW.`$dateCol`", $dUpd, $json('OLD'), $json('NEW')) . ";
                END IF;
            END");
        }
    }

    public function safeDown()
    {
        foreach (array_keys($this->tables()) as $table) {
            foreach (['ai', 'au', 'ad'] as $suffix) {
                $this->execute("DROP TRIGGER IF EXISTS audit_{$table}_{$suffix}");
            }
        }
        $this->dropTable('audit_log');
    }
}
