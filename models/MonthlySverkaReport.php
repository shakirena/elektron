<?php

namespace app\models;

use Yii;

/**
 * Расчёты для страницы «Aylıq sverka».
 *
 * Формула: Nəticə = Stok + Portfel + Kassa − Borc.
 * Ожидаемый Nəticə = Nəticə снимка + mənfəət − mənfəət vozvratlar − xərclər + digər mədaxil.
 * Разница между фактическим и ожидаемым — то, что не объясняется прибылью.
 *
 * Stok считается ТОЛЬКО по проведённым приходам (received=1): у непроведённых
 * нет долга поставщику, и раньше они раздували итог (см. разбор за сентябрь 2026).
 */
class MonthlySverkaReport
{
    /** Типы costs: оплата поставщику и возврат денег клиенту — не расход (их эффект уже в Borc / в прибыли по возвратам). */
    const NOT_EXPENSE_TYPES = [2, 57];
    /** Типы costs с type=1, которые являются выручкой/оплатой клиента, а не сторонним поступлением. */
    const NOT_OTHER_INCOME_TYPES = [1, 3, 56];

    public static function netice($stok, $portfel, $kassa, $borc)
    {
        return round((float) $stok + (float) $portfel + (float) $kassa - (float) $borc, 2);
    }

    /**
     * Текущее состояние: компоненты итога и разбивка по кассам.
     * @return array
     */
    public static function current()
    {
        $db = Yii::$app->db;

        $stok = (float) $db->createCommand('SELECT COALESCE(SUM(price*rest),0) FROM arrival WHERE received=1')->queryScalar();
        $stokDraft = (float) $db->createCommand('SELECT COALESCE(SUM(price*rest),0) FROM arrival WHERE received=0')->queryScalar();

        $balances = $db->createCommand('SELECT SUM(debt) b FROM dclient GROUP BY id_client')->queryColumn();
        $portfel = 0; $portfelMinus = 0;
        foreach ($balances as $b) {
            if ($b > 0) $portfel += $b; else $portfelMinus += $b;
        }

        $kassaList = $db->createCommand(
            'SELECT COALESCE(k.name, \'—\') name, SUM(c.sum) sum FROM costs c LEFT JOIN kassa k ON k.id=c.id_kassa GROUP BY c.id_kassa, k.name ORDER BY c.id_kassa'
        )->queryAll();
        $kassa = 0;
        foreach ($kassaList as $row) $kassa += $row['sum'];

        $borc = (float) $db->createCommand('SELECT COALESCE(SUM(debt),0) FROM debt')->queryScalar();

        return [
            'stok' => round($stok, 2),
            'stok_draft' => round($stokDraft, 2),
            'portfel' => round($portfel, 2),
            'portfel_minus' => round($portfelMinus, 2),
            'kassa' => round($kassa, 2),
            'kassa_list' => $kassaList,
            'borc' => round($borc, 2),
            'netice' => self::netice($stok, $portfel, $kassa, $borc),
            'max_ids' => [
                'max_id_arrival' => (int) $db->createCommand('SELECT MAX(id) FROM arrival')->queryScalar(),
                'max_id_sell' => (int) $db->createCommand('SELECT MAX(id) FROM sell')->queryScalar(),
                'max_id_costs' => (int) $db->createCommand('SELECT MAX(id) FROM costs')->queryScalar(),
                'max_id_debt' => (int) $db->createCommand('SELECT MAX(id) FROM debt')->queryScalar(),
                'max_id_dclient' => (int) $db->createCommand('SELECT MAX(id) FROM dclient')->queryScalar(),
            ],
        ];
    }

    /**
     * Движения за период [$since, $until] по датам документов (обе границы включительно).
     * @param string $since Y-m-d H:i:s
     * @param string|null $until Y-m-d H:i:s включительно, по умолчанию — без ограничения (до сейчас)
     * @return array
     */
    public static function flows($since, $until = null)
    {
        $db = Yii::$app->db;
        $p = [':since' => $since, ':until' => $until ?: '9999-12-31 23:59:59'];

        $sales = $db->createCommand(
            'SELECT COALESCE(SUM(sum),0) revenue, COALESCE(SUM(earnings),0) profit, COALESCE(SUM(price_ar*quantity),0) cogs FROM sell WHERE datetime>=:since AND datetime<=:until', $p
        )->queryOne();

        // Возвраты клиентов: себестоимость — по последней проведённой партии (так же, как в отчёте Satışlar).
        $returns = $db->createCommand(
            'SELECT COALESCE(SUM(r.price*r.quantity),0) sum,
                    COALESCE(SUM(r.quantity*COALESCE((SELECT a.price FROM arrival a WHERE a.id_product=r.id_product AND a.received=1 ORDER BY a.datetime DESC LIMIT 1),0)),0) cost
             FROM returnp r WHERE r.data>=:since AND r.data<=:until', $p
        )->queryOne();

        $costRows = $db->createCommand(
            'SELECT c.id_type, t.name, t.type, SUM(c.sum) sum, COUNT(*) n FROM costs c JOIN type_costs t ON t.id=c.id_type
             WHERE c.datetime>=:since AND c.datetime<=:until GROUP BY c.id_type, t.name, t.type ORDER BY c.id_type', $p
        )->queryAll();
        $expenses = 0; $expenseList = []; $otherIncome = 0; $incomeList = [];
        foreach ($costRows as $row) {
            $id = (int) $row['id_type'];
            if ((int) $row['type'] === 0 && !in_array($id, self::NOT_EXPENSE_TYPES, true)) {
                $expenses += -$row['sum'];
                $expenseList[] = $row;
            } elseif ((int) $row['type'] === 1 && !in_array($id, self::NOT_OTHER_INCOME_TYPES, true)) {
                $otherIncome += $row['sum'];
                $incomeList[] = $row;
            }
        }

        // Сами записи расходов/прочих поступлений — чтобы по примечанию было видно, за какой месяц расход
        $itemRows = $db->createCommand(
            'SELECT c.datetime, c.id_type, t.name, t.type, c.sum, c.note, k.name kassa FROM costs c
             JOIN type_costs t ON t.id=c.id_type LEFT JOIN kassa k ON k.id=c.id_kassa
             WHERE c.datetime>=:since AND c.datetime<=:until ORDER BY c.datetime', $p
        )->queryAll();
        $expenseItems = []; $incomeItems = [];
        foreach ($itemRows as $row) {
            $id = (int) $row['id_type'];
            if ((int) $row['type'] === 0 && !in_array($id, self::NOT_EXPENSE_TYPES, true)) {
                $expenseItems[] = $row;
            } elseif ((int) $row['type'] === 1 && !in_array($id, self::NOT_OTHER_INCOME_TYPES, true)) {
                $incomeItems[] = $row;
            }
        }

        $arrivals = (float) $db->createCommand('SELECT COALESCE(SUM(price*quantity),0) FROM arrival WHERE received=1 AND datetime>=:since AND datetime<=:until', $p)->queryScalar();
        $supplierReturns = (float) $db->createCommand('SELECT COALESCE(SUM(price*quantity),0) FROM return_arrival WHERE date>=:since AND date<=:until', $p)->queryScalar();

        return [
            'revenue' => round($sales['revenue'], 2),
            'profit' => round($sales['profit'], 2),
            'cogs' => round($sales['cogs'], 2),
            'returns_sum' => round($returns['sum'], 2),
            'returns_cost' => round($returns['cost'], 2),
            'returns_margin' => round($returns['sum'] - $returns['cost'], 2),
            'expenses' => round($expenses, 2),
            'expense_list' => $expenseList,
            'other_income' => round($otherIncome, 2),
            'income_list' => $incomeList,
            'expense_items' => $expenseItems,
            'income_items' => $incomeItems,
            'arrivals' => round($arrivals, 2),
            'supplier_returns' => round($supplierReturns, 2),
        ];
    }

    /**
     * Сравнение снимка с текущим состоянием. Чистая функция — без БД.
     * @param array $base    stok, portfel, kassa, borc, netice снимка
     * @param array $now     то же на сейчас
     * @param array $flows   результат flows()
     * @return array
     */
    public static function compare(array $base, array $now, array $flows)
    {
        $netProfit = $flows['profit'] - $flows['returns_margin'] - $flows['expenses'] + $flows['other_income'];
        $expected = round($base['netice'] + $netProfit, 2);

        $stockExpectedDelta = $flows['arrivals'] - $flows['cogs'] - $flows['supplier_returns'] + $flows['returns_cost'];
        $stockActualDelta = $now['stok'] - $base['stok'];

        $components = [];
        foreach (['stok', 'portfel', 'kassa', 'borc'] as $key) {
            $components[$key] = [
                'base' => (float) $base[$key],
                'now' => (float) $now[$key],
                'delta' => round($now[$key] - $base[$key], 2),
            ];
        }

        return [
            'net_profit' => round($netProfit, 2),
            'expected' => $expected,
            'actual' => (float) $now['netice'],
            'diff' => round($now['netice'] - $expected, 2),
            'components' => $components,
            'stock_expected_delta' => round($stockExpectedDelta, 2),
            'stock_actual_delta' => round($stockActualDelta, 2),
            'stock_diff' => round($stockActualDelta - $stockExpectedDelta, 2),
        ];
    }

    /**
     * Записи, внесённые после снимка, но датированные раньше него («задним числом»).
     * Работает только для снимков с сохранёнными max_id_*.
     * @return array список [label, count, sum]
     */
    public static function backdated(MonthlySverka $base)
    {
        if (!$base->max_id_costs) {
            return [];
        }
        $db = Yii::$app->db;
        $checks = [
            ['Kassa (xərc / mədaxil)', 'costs', 'sum', 'datetime', $base->max_id_costs],
            ['Şirkət borcu', 'debt', 'debt', 'datatime', $base->max_id_debt],
            ['Müştəri borcu', 'dclient', 'debt', 'datetime', $base->max_id_dclient],
            ['Mədaxil (təsdiqlənmiş)', 'arrival', 'price*quantity', 'datetime', $base->max_id_arrival],
            ['Satış', 'sell', 'sum', 'datetime', $base->max_id_sell],
        ];
        $result = [];
        foreach ($checks as list($label, $table, $expr, $dateCol, $maxId)) {
            $extra = $table === 'arrival' ? ' AND received=1' : '';
            $row = $db->createCommand(
                "SELECT COUNT(*) n, COALESCE(SUM($expr),0) s FROM $table WHERE id>:maxId AND $dateCol<:dt$extra",
                [':maxId' => (int) $maxId, ':dt' => $base->datetime]
            )->queryOne();
            if ($row['n'] > 0) {
                $result[] = ['label' => $label, 'count' => (int) $row['n'], 'sum' => round($row['s'], 2)];
            }
        }
        return $result;
    }
}
