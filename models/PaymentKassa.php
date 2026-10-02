<?php

namespace app\models;

use Yii;

/**
 * Касса оплаты для отчётов move/report-client и move/report1.
 *
 * Связи в costs:
 *  - оплата долга клиентом:   costs.id_type=1, costs.fid = dclient.id
 *  - оплата поставщику:       costs.id_type=2, costs.fid = debt.id
 *  - оплата при продаже:      costs.id_type=3, costs.fid = номер чека (карта/POS; kassa 1 — оплата бонусами).
 *    Наличная часть в costs при продаже не пишется (вносится потом «Günlik satışdan»).
 *
 * Все запросы — пачкой на весь отчёт, без запроса на каждую строку.
 */
class PaymentKassa
{
    const BONUS_KASSA = 1;

    private static $names;

    public static function names()
    {
        if (self::$names === null) {
            self::$names = [];
            foreach (Yii::$app->db->createCommand('SELECT id, name FROM kassa')->queryAll() as $k) {
                self::$names[(int) $k['id']] = $k['name'];
            }
        }
        return self::$names;
    }

    /**
     * @param int[] $fids
     * @return array [fid => [[id_kassa, sum], ...]]
     */
    private static function costsByFid(array $fids, $type)
    {
        $fids = array_values(array_unique(array_filter(array_map('intval', $fids))));
        if (!$fids) {
            return [];
        }
        $rows = (new \yii\db\Query())->select(['fid', 'id_kassa', 'sum'])->from('costs')
            ->where(['id_type' => $type, 'fid' => $fids])->all();
        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r['fid']][] = [(int) $r['id_kassa'], (float) $r['sum']];
        }
        return $map;
    }

    private static function name($idKassa)
    {
        $names = self::names();
        return isset($names[$idKassa]) ? $names[$idKassa] : ('Kassa #' . $idKassa);
    }

    /**
     * Кассы для строк отчёта по поставщику (Debt[]): [debt.id => 'Əsas kassa'].
     */
    public static function forDebts($rows)
    {
        $ids = [];
        foreach ($rows as $r) {
            if (empty($r['number']) && $r['debt'] < 0) $ids[] = $r['id'];
        }
        $result = [];
        foreach (self::costsByFid($ids, 2) as $fid => $list) {
            $result[$fid] = implode(', ', array_unique(array_map(function ($c) { return self::name($c[0]); }, $list)));
        }
        return $result;
    }

    /**
     * Кассы для строк отчёта по клиенту (Dclient[]).
     * @return array ['payment' => [dclient.id => 'Əsas kassa'], 'sale' => [number => [[name, sum], ...]]]
     */
    public static function forDclients($rows)
    {
        $paymentIds = []; $numbers = [];
        foreach ($rows as $r) {
            if (empty($r['number'])) {
                $paymentIds[] = $r['id'];
            } else {
                $numbers[] = $r['number'];
            }
        }
        $payment = [];
        foreach (self::costsByFid($paymentIds, 1) as $fid => $list) {
            $payment[$fid] = implode(', ', array_unique(array_map(function ($c) { return self::name($c[0]); }, $list)));
        }
        $sale = [];
        foreach (self::costsByFid($numbers, 3) as $fid => $list) {
            foreach ($list as $c) {
                if ($c[0] !== self::BONUS_KASSA && $c[1] > 0) {
                    $sale[$fid][] = [self::name($c[0]), $c[1]];
                }
            }
        }
        return ['payment' => $payment, 'sale' => $sale];
    }

    /**
     * Текст для строки продажи: «POS Kapital 50, Nağd 30».
     * @param float $paid сколько оплачено при продаже (dclient.sum)
     * @param float $bonusPaid оплачено бонусами (dclient.bonus)
     */
    public static function describeSale(array $map, $number, $paid, $bonusPaid = 0)
    {
        $parts = []; $card = 0;
        if (isset($map['sale'][$number])) {
            foreach ($map['sale'][$number] as list($name, $sum)) {
                $parts[] = $name . ' ' . round($sum, 2);
                $card += $sum;
            }
        }
        $cash = round((float) $paid - $card - (float) $bonusPaid, 2);
        if ($cash > 0) {
            $parts[] = 'Nağd ' . $cash;
        }
        return implode(', ', $parts);
    }

    public static function describePayment(array $map, $id)
    {
        return isset($map['payment'][$id]) ? $map['payment'][$id] : '';
    }
}
