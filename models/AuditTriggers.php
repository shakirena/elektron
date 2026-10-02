<?php

namespace app\models;

use yii\db\Connection;

/**
 * Триггеры журнала audit_log (см. миграции m261002_150000, m261003_090000).
 *
 * Совместимо с MySQL 5.6: без JSON-функций (JSON_OBJECT появился только в 5.7.8).
 * JSON для old_data/new_data собирается через CONCAT/REPLACE; спецсимволы —
 * через CHAR(n USING utf8), чтобы не зависеть от sql_mode NO_BACKSLASH_ESCAPES.
 * Все значения пишутся строками ("119"), PHP приводит их к числам сам.
 *
 * created_at = @app_now (время PHP, как у документов и снимков); NOW() — только для правок
 * вне программы. Часовые пояса PHP и MySQL могут отличаться (локально +3 и +4).
 */
class AuditTriggers
{
    /**
     * Условие «строка подтверждена» — черновики (непроведённый приход, непроданная строка)
     * в журнал не пишутся. Для UPDATE пишется, если строка подтверждена до ИЛИ после
     * изменения — так видны и проведение (0→1), и отмена (1→0).
     */
    public static function conditions()
    {
        return [
            'arrival' => 'X.received=1',
            'sell' => 'X.sold=1',
        ];
    }

    /**
     * Таблица => [колонка даты документа, поля для old/new_data, [колонка d_* => выражение от строки X]]
     */
    public static function tables()
    {
        return [
            'arrival' => ['datetime',
                ['id_product', 'id_store', 'quantity', 'rest', 'price', 'received', 'postponed', 'number', 'id_contr', 'datetime'],
                ['d_stok' => 'IF(X.received=1, X.price*X.rest, 0)']],
            'sell' => ['datetime',
                ['id_product', 'id_store', 'quantity', 'price', 'sum', 'price_ar', 'earnings', 'number', 'id_client', 'returnp', 'sold', 'datetime'],
                ['d_profit' => 'IF(X.sold=1, X.earnings, 0)']],
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

    /** Строковое значение поля в JSON-строке с экранированием \ " \n \r \t. */
    private static function jsonValue($expr)
    {
        $bs = 'CHAR(92 USING utf8)';
        $v = "REPLACE($expr, $bs, CONCAT($bs, $bs))";
        $v = "REPLACE($v, CHAR(34 USING utf8), CONCAT($bs, CHAR(34 USING utf8)))";
        $v = "REPLACE($v, CHAR(10 USING utf8), CONCAT($bs, 'n'))";
        $v = "REPLACE($v, CHAR(13 USING utf8), CONCAT($bs, 'r'))";
        $v = "REPLACE($v, CHAR(9 USING utf8), CONCAT($bs, 't'))";
        return "IF($expr IS NULL, 'null', CONCAT(CHAR(34 USING utf8), $v, CHAR(34 USING utf8)))";
    }

    /** JSON-объект из полей строки $x (NEW/OLD). */
    public static function jsonObject($x, array $fields)
    {
        $parts = [];
        foreach ($fields as $i => $f) {
            $parts[] = "'" . ($i ? ',' : '{') . "\"$f\":'";
            $parts[] = self::jsonValue("$x.`$f`");
        }
        $parts[] = "'}'";
        return 'CONCAT(' . implode(', ', $parts) . ')';
    }

    /** SQL всех триггеров: [имя => CREATE TRIGGER ...]. */
    public static function createStatements()
    {
        $result = [];
        foreach (self::tables() as $table => list($dateCol, $fields, $effects)) {
            $eff = function ($x, $sign) use ($effects) {
                $cols = ['d_stok' => '0', 'd_portfel' => '0', 'd_kassa' => '0', 'd_borc' => '0', 'd_profit' => '0'];
                foreach ($effects as $col => $expr) {
                    $cols[$col] = $sign . 'COALESCE(' . str_replace('X.', "$x.", $expr) . ', 0)';
                }
                return $cols;
            };
            $insert = function ($rowId, $action, $docDate, array $d, $old, $new) use ($table) {
                return "INSERT INTO audit_log (created_at, user_id, route, tbl, row_id, action, doc_date, d_stok, d_portfel, d_kassa, d_borc, d_profit, old_data, new_data)
                        VALUES (COALESCE(@app_now, NOW()), @app_user_id, @app_route, '$table', $rowId, '$action', $docDate,
                                {$d['d_stok']}, {$d['d_portfel']}, {$d['d_kassa']}, {$d['d_borc']}, {$d['d_profit']}, $old, $new)";
            };

            $cond = isset(self::conditions()[$table]) ? self::conditions()[$table] : '1=1';
            $condNew = str_replace('X.', 'NEW.', $cond);
            $condOld = str_replace('X.', 'OLD.', $cond);

            $result["audit_{$table}_ai"] = "CREATE TRIGGER audit_{$table}_ai AFTER INSERT ON `$table` FOR EACH ROW BEGIN
                IF $condNew THEN
                    " . $insert('NEW.id', 'I', "NEW.`$dateCol`", $eff('NEW', ''), 'NULL', self::jsonObject('NEW', $fields)) . ";
                END IF;
            END";

            $result["audit_{$table}_ad"] = "CREATE TRIGGER audit_{$table}_ad AFTER DELETE ON `$table` FOR EACH ROW BEGIN
                IF $condOld THEN
                    " . $insert('OLD.id', 'D', "OLD.`$dateCol`", $eff('OLD', '-'), self::jsonObject('OLD', $fields), 'NULL') . ";
                END IF;
            END";

            $changed = [];
            foreach ($fields as $f) {
                $changed[] = "NOT (OLD.`$f` <=> NEW.`$f`)";
            }
            $dNew = $eff('NEW', ''); $dOld = $eff('OLD', '');
            $dUpd = [];
            foreach ($dNew as $col => $expr) {
                $dUpd[$col] = "($expr) - ({$dOld[$col]})";
            }
            $result["audit_{$table}_au"] = "CREATE TRIGGER audit_{$table}_au AFTER UPDATE ON `$table` FOR EACH ROW BEGIN
                IF ($condOld OR $condNew) AND (" . implode(' OR ', $changed) . ") THEN
                    " . $insert('NEW.id', 'U', "NEW.`$dateCol`", $dUpd, self::jsonObject('OLD', $fields), self::jsonObject('NEW', $fields)) . ";
                END IF;
            END";
        }
        return $result;
    }

    public static function triggerNames()
    {
        $names = [];
        foreach (array_keys(self::tables()) as $table) {
            foreach (['ai', 'au', 'ad'] as $suffix) {
                $names[] = "audit_{$table}_{$suffix}";
            }
        }
        return $names;
    }

    public static function drop(Connection $db)
    {
        foreach (self::triggerNames() as $name) {
            $db->createCommand("DROP TRIGGER IF EXISTS `$name`")->execute();
        }
    }

    public static function create(Connection $db)
    {
        foreach (self::createStatements() as $sql) {
            $db->createCommand($sql)->execute();
        }
    }
}
