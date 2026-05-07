<?php

namespace app\models;

use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "postponed".
 *
 * @property integer $id
 * @property integer $id_product
 * @property integer $quantity
 * @property integer $id_store
 * @property integer $id_user
 * @property integer $id_sell
 * @property integer $date
 * @property integer $received
 *
 * @property Sell $idSell
 * @property Product $idProduct
 * @property Users $idUser
 * @property Store $idStore
 */
class Postponed extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'postponed';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_product', 'quantity', 'id_store', 'id_user', 'number', 'date'], 'required'],
            [['id_product', 'quantity', 'id_store', 'id_user', 'number',  'received','id_sell','id_master'], 'integer'],
            [['sum_master'], 'number'],
            [['date'], 'safe'],
            [['id_sell'], 'exist', 'skipOnError' => true, 'targetClass' => Sell::className(), 'targetAttribute' => ['id_sell' => 'id']],
            [['id_product'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['id_product' => 'id']],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['id_user' => 'id_user']],
            [['id_store'], 'exist', 'skipOnError' => true, 'targetClass' => Store::className(), 'targetAttribute' => ['id_store' => 'id']],
            [['id_master'], 'exist', 'skipOnError' => true, 'targetClass' => Master::className(), 'targetAttribute' => ['id_master' => 'id']],

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
            'id_store' => 'Id Store',
            'id_user' => 'Id User',
            'number' => 'Number',
            'date' => 'Date',
            'received' => 'Received',
            'id_master' => 'Usta',
            'sum_master' => 'Pul Usta'
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'id_product']);
    }
    public function getIdMaster()
    {
        return $this->hasOne(Master::className(), ['id' => 'id_master']);
    }
    public function getGetNumber()
    {


        return $this->number."   ".Html::button('<i class="glyphicon glyphicon-remove"></i>Təsdiqlə',["onclick"=> "receivedAdminPostponed($this->number)"]);
    }
    public function getGetNumberPrint()
    {


      if ($this->received!=0)  return $this->number."   ".Html::button('<i class="glyphicon glyphicon-remove"></i>Çap et',["onclick"=> "printPostponed($this->number,$this->received)"]);
    else return $this->number."  (Təsdiqlənməyib )";
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdUser()
    {
        return $this->hasOne(Users::className(), ['id_user' => 'id_user']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'id_store']);
    }


    public  function  getStatus()
    {

    }
    public function getIdType()
    {
        return $this->getIdProduct()->one()->id_type;

    }
    public function getProduct()
    {
        return   Product::findOne(['id'=>$this->id_product]);
    }
    public function getGetType()
    {
        $parent=TypeProduct::find()->where(["id" =>  $this->getIdType()])->one();
        if ($parent) $name2=$parent->name;

        $parent=TypeProduct::find()->where(["id" =>  $parent->id_parent])->one();
        if ($parent) $name1=$parent->name."/";

        $parent=TypeProduct::find()->where(["id" =>  $parent->id_parent])->one();
        if ($parent) $name1=$parent->name."/";

        $parent=TypeProduct::find()->where(["id" =>  $parent->id_parent])->one();
        if ($parent) $name1=$parent->name."/";

        $parent=TypeProduct::find()->where(["id" =>  $parent->id_parent])->one();
        if ($parent) $name1=$parent->name."/";


        $name=$name1.$name2;


        $name.="/".$this->getProduct()->name;
        return $name;
    }

    public function getSum($model,$column){
        $sum=0;
        $query =  Postponed::find()->select("sum(quantity) as quantity, sum(sum_master) as sum_master")
            ->joinWith('idProduct.idType')

            ->where($model->where)->one();


        return $query->$column;
    }
}
