<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "return_arrival".
 *
 * @property integer $id
 * @property integer $id_user
 * @property integer $id_product
 * @property integer $id_contr
 * @property double $quantity
 * @property double $price
 * @property double $usd
 * @property integer $id_store
 * @property string $date
 * @property integer $received
 *
 * @property Product $idProduct
 * @property Users $idUser
 * @property Contractor $idContr
 * @property Store $idStore
 */
class ReturnArrival2 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
	public $name_product;
    public static function tableName()
    {
        return 'return_arrival';
    }
	public static  function getDb() {
		return \Yii::$app->db2;
	}
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_user', 'id_product', 'id_contr', 'quantity', 'price', 'id_store', 'date', 'received'], 'required'],
            [['id_user', 'id_product', 'id_contr', 'id_store', 'received'], 'integer'],
            [['quantity', 'price', 'usd'], 'number'],
            [['date'], 'safe'],
            [['id_product'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['id_product' => 'id']],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['id_user' => 'id_user']],
            [['id_contr'], 'exist', 'skipOnError' => true, 'targetClass' => Contractor::className(), 'targetAttribute' => ['id_contr' => 'id']],
            [['id_store'], 'exist', 'skipOnError' => true, 'targetClass' => Store::className(), 'targetAttribute' => ['id_store' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_user' => 'Id User',
            'id_product' => 'Id Product',
            'id_contr' => 'Id Contr',
            'quantity' => 'Quantity',
            'price' => 'Price',
            'usd' => 'Usd',
            'id_store' => 'Id Store',
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
	public function getNameProduct()
	{
		return Product::find()->where(["id"=>$this->id_product])->one()->name;
	
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
    public function getIdContr()
    {
        return $this->hasOne(Contractor::className(), ['id' => 'id_contr']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'id_store']);
    }
	public function getSum()
    {
        return $this->price*$this->quantity;
    }
	public function getSumUsd()
    {
        return $this->usd*$this->quantity;
    }
	
	
	public function getSumArrival($model,$column){
        $sum=0;
        $query =  ReturnArrival::find()->select("sum(quantity) as quantity,sum(quantity*price) as price")
            ->joinWith('idProduct.idType')
            ->where($model->where)
			->one();


        return $query->$column;
    }
}
