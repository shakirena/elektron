<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Снимок «Aylıq sverka» (таблица monthly_sverka).
 *
 * @property int $id
 * @property string $datetime
 * @property float $stok          Склад — только проведённые приходы
 * @property float $stok_draft    Непроведённые приходы (в итог не входят)
 * @property float $portfel       Долги клиентов — только положительные балансы
 * @property float $portfel_minus Переплаты клиентов (отрицательные балансы)
 * @property float $kassa         Все кассы
 * @property float $borc          Долг поставщикам
 * @property float $netice        stok + portfel + kassa − borc
 * @property int $is_manual
 * @property string $note
 * @property int $id_user
 * @property int $max_id_arrival
 * @property int $max_id_sell
 * @property int $max_id_costs
 * @property int $max_id_debt
 * @property int $max_id_dclient
 */
class MonthlySverka extends ActiveRecord
{
    public static function tableName()
    {
        return 'monthly_sverka';
    }

    public function rules()
    {
        return [
            [['datetime', 'stok', 'portfel', 'kassa', 'borc'], 'required'],
            [['datetime'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['stok', 'stok_draft', 'portfel', 'portfel_minus', 'kassa', 'borc', 'netice'], 'number'],
            [['is_manual'], 'boolean'],
            [['note'], 'string', 'max' => 255],
            [['id_user', 'max_id_arrival', 'max_id_sell', 'max_id_costs', 'max_id_debt', 'max_id_dclient'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'datetime' => 'Tarix',
            'stok' => 'Stok',
            'stok_draft' => 'Təsdiqlənməmiş mədaxil',
            'portfel' => 'Portfel',
            'portfel_minus' => 'Müştəri artıq ödənişləri',
            'kassa' => 'Kassa',
            'borc' => 'Borc (verəcək)',
            'netice' => 'Nəticə',
            'note' => 'Qeyd',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->netice = MonthlySverkaReport::netice($this->stok, $this->portfel, $this->kassa, $this->borc);
        return true;
    }

    /**
     * Короткая подпись для выпадающего списка.
     * @return string
     */
    public function getTitle()
    {
        return $this->datetime . ' — ' . number_format($this->netice, 2, '.', ' ')
            . ($this->is_manual ? ' (əl ilə)' : '')
            . ($this->note ? ' — ' . $this->note : '');
    }
}
