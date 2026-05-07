<?php

namespace app\models;
use yii\bootstrap\Html;
use Yii;

/**
 * This is the model class for table "transfer".
 *
 * @property integer $id
 * @property integer $id_product
 * @property integer $quantity
 * @property integer $id_user
 * @property integer $whence
 * @property integer $whered
 * @property string $date
 * @property integer $received
 *
 * @property Product $idProduct
 * @property Store $whence0
 * @property Users $idUser
 * @property Store $whered0
 */
class Transfer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $name_product;
    public static function tableName()
    {
        return 'transfer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_product', 'quantity', 'id_user'], 'required'],
            [['id_product', 'id_user', 'whence', 'whered', 'received','number'], 'integer'],
            [['date', 'quantity'], 'safe'],
            [['id_product'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['id_product' => 'id']],
            [['whence'], 'exist', 'skipOnError' => true, 'targetClass' => Store::className(), 'targetAttribute' => ['whence' => 'id']],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['id_user' => 'id_user']],
            [['whered'], 'exist', 'skipOnError' => true, 'targetClass' => Store::className(), 'targetAttribute' => ['whered' => 'id']],
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
            'number' =>'Number',
            'quantity' => 'Quantity',
            'id_user' => 'Id User',
            'whence' => 'Whence',
            'whered' => 'Whered',
            'date' => 'Date',
            'received' => 'Received',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'id_product']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWhence0()
    {
        return $this->hasOne(Store::className(), ['id' => 'whence']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdUser()
    {
        return $this->hasOne(Users::className(), ['id_user' => 'id_user']);
    }
    public function getIdType()
    {
        return $this->getIdProduct()->one()->id_type;

    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWhered0()
    {
        return $this->hasOne(Store::className(), ['id' => 'whered']);
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
        $query =  $query = Transfer::find()->select("sum(quantity) as quantity")->andWhere("received=1")
            ->joinWith('idProduct.idType')
            ->where($model->where)->one();


        return $query->$column;
    }

    public function getGetRest($id,$id_product){
      return  Arrival::find()->select("sum(rest) as rest")->where(["id_product"=>$id_product,'id_store'=>$id])->one()->rest;

    }


 
	
	    public function getNumberGet()
    {

		$arrival=Arrival::find()
            ->where(['transfer' => $this->number])
          
            ->one();
			if ($arrival->quantity==$arrival->rest)
        return $this->number."   ".Html::button('<i class="glyphicon glyphicon-remove"></i>',["onclick"=> "cancelTransfer($this->number)",'class' => 'btn btn-primary btn-xs'])."   ".Html::button('<i class="glyphicon glyphicon-print"></i>',["onclick"=> "printTransfer($this->number)",'class' => 'btn btn-primary btn-xs']);
	else return $this->number."   ".Html::button('<i class="glyphicon glyphicon-print"></i>',["onclick"=> "printTransfer($this->number)",'class' => 'btn btn-primary btn-xs']);;
    }
}
