<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sverka".
 *
 * @property integer $id
 * @property integer $id_product
 * @property double $quantity
 *
 * @property Product $idProduct
 */
class Sverka extends \yii\db\ActiveRecord
{
    public $sum_fakt;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sverka';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'id_product','id_store', 'quantity'], 'required'],
            [[ 'id_product','id_store'], 'integer'],
            [['quantity'], 'number'],
            [['id_product'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['id_product' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_product' => 'Id Product',
            'quantity' => 'Quantity',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'id_product']);
    }

    public function getQuantitySklad()
    {
        $model=Arrival::find()
            ->select("sum(rest) as rest")
            ->where(["id_store" =>  Yii::$app->session->get("sverka"),'id_product' => $this->id_product])
            ->one();


        return $model->rest;
    }


    public function getDifference()
    {
        return $this->quantity-$this->getQuantitySklad();
    }

    public function getBarcode()
    {
        $model=Product::find()->where(["id"=> $this->id_product])->one();
        return $model->bar_code;
    }
    public function getPriceSell()
    {
        $model=Arrival::find()->where(["id_product"=> $this->id_product])->orderBy("datetime DESC")->one();
        return $model;
    }

    public function getSum()
    {

        return $this->quantity*$this->getPriceSell()->pricesell;
    }

    public function getPriceSellSum()
    {
        return $this->getPriceSell()->price*$this->quantity;

    }
    public function getIdType()
    {
        return $this->getIdProduct()->one()->id_type;

    }
    public function getProduct()
    {
        return   Product::findOne(['id'=>$this->id_product]);
    }
 
}
