<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Feature #27: Отчёт «Движение товара».
 *
 * ActiveRecord для таблицы sverka_log (миграция m260812_132707_create_sverka_log).
 *
 * Заполняется из SverkaController::actionReceived() непосредственно перед
 * $model->delete() — сохраняет снимок изменения остатка через сверку.
 *
 * Читается из ProductMovementSearch как один из источников UNION-запроса.
 *
 * @property int    $id
 * @property int    $id_product
 * @property int    $id_store
 * @property float  $qty_before
 * @property float  $qty_after
 * @property float  $delta       qty_after - qty_before (со знаком)
 * @property int    $id_user
 * @property string $datetime
 *
 * @property Product $idProduct
 * @property Store   $idStore
 * @property Users   $idUser
 */
class SverkaLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'sverka_log';
    }

    public function rules()
    {
        return [
            [['id_product', 'id_store', 'id_user', 'datetime'], 'required'],
            [['id_product', 'id_store', 'id_user'], 'integer'],
            [['qty_before', 'qty_after', 'delta'], 'number'],
            [['datetime'], 'safe'],
            [['id_product'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['id_product' => 'id']],
            [['id_store'],   'exist', 'skipOnError' => true, 'targetClass' => Store::className(),   'targetAttribute' => ['id_store' => 'id']],
            [['id_user'],    'exist', 'skipOnError' => true, 'targetClass' => Users::className(),   'targetAttribute' => ['id_user' => 'id_user']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'id_product' => 'Товар',
            'id_store'   => 'Склад',
            'qty_before' => 'Кол-во до',
            'qty_after'  => 'Кол-во после',
            'delta'      => 'Изменение',
            'id_user'    => 'Пользователь',
            'datetime'   => 'Дата/время',
        ];
    }

    public function getIdProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'id_product']);
    }

    public function getIdStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'id_store']);
    }

    public function getIdUser()
    {
        return $this->hasOne(Users::className(), ['id_user' => 'id_user']);
    }

    /**
     * Хелпер для SverkaController::actionReceived() — создать запись лога
     * непосредственно перед $sverka->delete().
     *
     * @param int   $idProduct
     * @param int   $idStore
     * @param float $qtyBefore snapshot остатка ДО применения сверки
     * @param float $qtyAfter  значение из sverka.quantity (устанавливаемое)
     * @param int   $idUser    id пользователя, применившего сверку
     * @return bool результат save()
     */
    public static function logChange($idProduct, $idStore, $qtyBefore, $qtyAfter, $idUser)
    {
        $log = new self();
        $log->id_product = (int) $idProduct;
        $log->id_store   = (int) $idStore;
        $log->qty_before = (float) $qtyBefore;
        $log->qty_after  = (float) $qtyAfter;
        $log->delta      = (float) $qtyAfter - (float) $qtyBefore;
        $log->id_user    = (int) $idUser;
        $log->datetime   = date('Y-m-d H:i:s');
        return $log->save(false);
    }
}
