<?php

namespace app\models;

use Yii;

/**
 * Расчёты для страницы «Aylıq sverka».
 *
 * Формула: Nəticə = Stok + Portfel + Kassa − Borc − müştəri artıq ödənişləri
 * (переплаты клиентов — деньги клиентов у нас, считаются как обязательство/расход).
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

    /** Штатные операции (маршруты), которые двигают склад, деньги и долги. */
    const NORMAL_ROUTES = [
        'sell/received' => 'Satış',
        'postponed/receive' => 'Satış', 'postponed/receive1' => 'Satış', 'postponed/postponed-print' => 'Satış', 'postponed/master' => 'Satış',
        'arrival/received' => 'Mədaxil',
        'arrival/received-return' => 'Şirkətə vozvrat', 'arrival/received-return2' => 'Şirkətə vozvrat',
        'sell/return-sell-received' => 'Müştəri vozvratı', 'sell/received-return' => 'Müştəri vozvratı',
        'sell/return-received' => 'Müştəri vozvratı', 'returnp/return-received' => 'Müştəri vozvratı',
        'transfer/received' => 'Transfer', 'transfer/received-posponed' => 'Transfer',
        'costs/create' => 'Kassa', 'costs/prixod' => 'Kassa', 'costs/transfer' => 'Kassa',
        'debt/create' => 'Şirkət borcu', 'debt/debt-add' => 'Şirkət borcu', 'debt/received-debt' => 'Şirkət borcu',
        'dclient/create' => 'Müştəri borcu', 'sell/dclient-add' => 'Müştəri borcu', 'sell/received-debt' => 'Müştəri borcu',
    ];

    const ACTION_LABELS = ['I' => 'əlavə', 'U' => 'dəyişiklik', 'D' => 'silinmə'];

    /**
     * @param float $portfelMinus переплаты клиентов — отрицательное число (сумма минусовых балансов)
     */
    public static function netice($stok, $portfel, $kassa, $borc, $portfelMinus = 0)
    {
        return round((float) $stok + (float) $portfel + (float) $kassa - (float) $borc - abs((float) $portfelMinus), 2);
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

        $balances = $db->createCommand(
            'SELECT t.id_client, c.fio, t.b FROM (SELECT id_client, SUM(debt) b FROM dclient GROUP BY id_client) t
             LEFT JOIN client c ON c.id_client=t.id_client'
        )->queryAll();
        $portfel = 0; $portfelMinus = 0; $minusClients = [];
        foreach ($balances as $row) {
            $b = round((float) $row['b'], 2);
            if ($b > 0) {
                $portfel += $b;
            } elseif ($b < 0) {
                $portfelMinus += $b;
                $minusClients[] = ['id_client' => (int) $row['id_client'], 'fio' => $row['fio'], 'balance' => $b];
            }
        }
        usort($minusClients, function ($a, $b) { return $a['balance'] <=> $b['balance']; });

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
            'minus_clients' => $minusClients,
            'kassa' => round($kassa, 2),
            'kassa_list' => $kassaList,
            'borc' => round($borc, 2),
            'netice' => self::netice($stok, $portfel, $kassa, $borc, $portfelMinus),
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

        // Деньги от продаж, которые должны прийти в кассу сразу: анонимные продажи + оплата клиентов в момент продажи.
        // Сравниваются с внесённой выручкой (Satış, Günlik satışdan) — разница = недостача/излишек кассы.
        $cashSales = (float) $db->createCommand('SELECT COALESCE(SUM(sum),0) FROM sell WHERE (id_client=1 OR id_client IS NULL) AND datetime>=:since AND datetime<=:until', $p)->queryScalar()
            + (float) $db->createCommand('SELECT COALESCE(SUM(sum),0) FROM dclient WHERE number IS NOT NULL AND sum>0 AND datetime>=:since AND datetime<=:until', $p)->queryScalar();
        $cashEntered = (float) $db->createCommand('SELECT COALESCE(SUM(sum),0) FROM costs WHERE id_type IN (3,56) AND datetime>=:since AND datetime<=:until', $p)->queryScalar();

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
            'cash_sales' => round($cashSales, 2),
            'cash_entered' => round($cashEntered, 2),
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
        foreach (['stok', 'portfel', 'portfel_minus', 'kassa', 'borc'] as $key) {
            $components[$key] = [
                'base' => isset($base[$key]) ? (float) $base[$key] : 0.0,
                'now' => isset($now[$key]) ? (float) $now[$key] : 0.0,
                'delta' => round((isset($now[$key]) ? $now[$key] : 0) - (isset($base[$key]) ? $base[$key] : 0), 2),
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
     * Категория записи журнала и нужно ли обратить на неё внимание. Чистая функция.
     * @param array $row строка audit_log (route, action, doc_date, d_*)
     * @param string $baseDatetime время снимка
     * @return array [category, attention]
     */
    public static function classify(array $row, $baseDatetime)
    {
        $route = (string) $row['route'];
        $hasEffect = self::neticeEffect($row) != 0 || (float) $row['d_profit'] != 0;

        if ($route === '') {
            return ['Proqramdan kənar dəyişiklik', true];
        }
        if (preg_match('~/cancel$~', $route)) {
            return ['Ləğv', true];
        }
        if (preg_match('~/delete~', $route) || ($row['action'] === 'D' && $hasEffect)) {
            return ['Silinmə', true];
        }
        if (strpos($route, 'sverka/') === 0) {
            return ['İnventarizasiya', true];
        }
        if (preg_match('~/update-~', $route) && $hasEffect) {
            return ['Qiymət / qalıq redaktəsi', true];
        }
        if ($row['action'] === 'I' && $hasEffect && $row['doc_date'] !== null && $row['doc_date'] < $baseDatetime) {
            return ['Keçmiş tarixlə', true];
        }
        if (isset(self::NORMAL_ROUTES[$route])) {
            return [self::NORMAL_ROUTES[$route], false];
        }
        return ['Naməlum əməliyyat (' . $route . ')', $hasEffect];
    }

    /** Влияние строки журнала на Nəticə = Stok + Portfel + Kassa − Borc. */
    public static function neticeEffect(array $row)
    {
        return round((float) $row['d_stok'] + (float) $row['d_portfel'] + (float) $row['d_kassa'] - (float) $row['d_borc'], 2);
    }

    /**
     * Часть изменения итога, не объяснённая прибылью/расходами. Чистая функция.
     * Записи с датой документа раньше снимка в ожидаемый итог не попадают —
     * у них необъяснённым считается всё изменение.
     *
     * @param array $row строка audit_log; old_data/new_data — JSON
     * @param string $baseDatetime
     * @param int[] $expectedCostTypes id_type costs, которые входят в ожидаемый итог (расходы и прочие поступления)
     * @param float[] $returnCost себестоимость единицы по id_product (для возвратов клиентов)
     */
    public static function unexplained(array $row, $baseDatetime, array $expectedCostTypes, array $returnCost)
    {
        $effect = self::neticeEffect($row);
        $inPeriod = $row['doc_date'] === null || $row['doc_date'] >= $baseDatetime;
        if (!$inPeriod) {
            return $effect;
        }
        $data = json_decode($row['action'] === 'D' ? $row['old_data'] : $row['new_data'], true) ?: [];
        $expected = (float) $row['d_profit'];

        if ($row['tbl'] === 'costs' && isset($data['id_type']) && in_array((int) $data['id_type'], $expectedCostTypes, true)) {
            $expected += (float) $row['d_kassa'];
        }
        if ($row['tbl'] === 'returnp' && $row['action'] !== 'U' && isset($data['id_product'])) {
            $cost = isset($returnCost[$data['id_product']]) ? $returnCost[$data['id_product']] : 0;
            $margin = ((float) $data['price'] - $cost) * (float) $data['quantity'];
            $expected += $row['action'] === 'I' ? -$margin : $margin;
        }
        return round($effect - $expected, 2);
    }

    /**
     * Разбор журнала с момента снимка: что объясняет расхождение.
     * @return array|null null — журнал ещё пуст
     */
    public static function audit(MonthlySverka $base)
    {
        $db = Yii::$app->db;
        if (!$db->getTableSchema('audit_log')) {
            return null;
        }
        $rows = $db->createCommand(
            'SELECT a.*, u.fio FROM audit_log a LEFT JOIN users u ON u.id_user=a.user_id
             WHERE a.created_at >= :since ORDER BY a.id', [':since' => $base->datetime]
        )->queryAll();
        $logStart = $db->createCommand('SELECT MIN(created_at) FROM audit_log')->queryScalar();

        $expectedCostTypes = [];
        foreach ($db->createCommand('SELECT id, type FROM type_costs')->queryAll() as $t) {
            $id = (int) $t['id'];
            if (((int) $t['type'] === 0 && !in_array($id, self::NOT_EXPENSE_TYPES, true))
                || ((int) $t['type'] === 1 && !in_array($id, self::NOT_OTHER_INCOME_TYPES, true))) {
                $expectedCostTypes[] = $id;
            }
        }
        $returnCost = [];
        foreach ($db->createCommand(
            'SELECT a.id_product, a.price FROM arrival a
             JOIN (SELECT id_product, MAX(datetime) dt FROM arrival WHERE received=1 GROUP BY id_product) m
               ON m.id_product=a.id_product AND m.dt=a.datetime WHERE a.received=1'
        )->queryAll() as $r) {
            $returnCost[$r['id_product']] = (float) $r['price'];
        }

        $normalU = 0; $totalU = 0; $groups = []; $events = [];
        foreach ($rows as $row) {
            $effect = self::neticeEffect($row);
            if ($effect == 0 && (float) $row['d_profit'] == 0) {
                continue;
            }
            list($category, $attention) = self::classify($row, $base->datetime);
            $u = self::unexplained($row, $base->datetime, $expectedCostTypes, $returnCost);
            $totalU += $u;
            if (!$attention) {
                $normalU += $u;
                continue;
            }
            if (!isset($groups[$category])) {
                $groups[$category] = ['count' => 0, 'effect' => 0, 'unexplained' => 0];
            }
            $groups[$category]['count']++;
            $groups[$category]['effect'] += $effect;
            $groups[$category]['unexplained'] += $u;
            $row['category'] = $category;
            $row['effect'] = $effect;
            $row['unexplained'] = $u;
            $events[] = $row;
        }
        uasort($groups, function ($a, $b) { return abs($b['unexplained']) <=> abs($a['unexplained']); });

        return [
            'log_start' => $logStart,
            'covers_period' => $logStart !== null && $logStart <= $base->datetime,
            'normal_unexplained' => round($normalU, 2),
            'total_unexplained' => round($totalU, 2),
            'groups' => $groups,
            'events' => array_slice($events, -300),
            'events_total' => count($events),
        ];
    }

    /**
     * Короткое описание строки журнала для таблицы.
     */
    public static function describe(array $row)
    {
        $data = json_decode($row['action'] === 'D' ? $row['old_data'] : $row['new_data'], true) ?: [];
        $old = json_decode((string) $row['old_data'], true) ?: [];
        $parts = [$row['tbl'] . ' #' . $row['row_id'], self::ACTION_LABELS[$row['action']]];
        foreach (['id_product' => 'mal', 'id_contr' => 'şirkət', 'id_client' => 'müştəri', 'number' => '№', 'note' => ''] as $k => $label) {
            if (isset($data[$k]) && $data[$k] !== '' && $data[$k] !== null) {
                $parts[] = trim($label . ' ' . $data[$k]);
            }
        }
        if ($row['action'] === 'U') {
            foreach ($data as $k => $v) {
                if (array_key_exists($k, $old) && $old[$k] != $v) {
                    $parts[] = "$k: {$old[$k]} → $v";
                }
            }
        }
        return implode(', ', $parts);
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
