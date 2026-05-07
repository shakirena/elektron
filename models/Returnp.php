<?php

namespace app\models;
use yii\helpers\Html;
use Yii;

/**
 * This is the model class for table "returnp".
 *
 * @property integer $id
 * @property integer $id_product
 * @property integer $id_user
 * @property integer $id_client
 * @property string $reason
 * @property string $data
 * @property double $quantity
 *
 * @property Client $idClient
 * @property Users $idUser
 * @property Product $idProduct
 */
class Returnp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'returnp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_product', 'id_user', 'id_client', 'number'], 'required'],
            [['id_product', 'id_user', 'id_client','number','received','id_sell','id_store'], 'integer'],
            [['data'], 'safe'],
            [['quantity','money','price'], 'number'],
            [['reason'], 'string', 'max' => 20],
            [['id_client'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['id_client' => 'id_client']],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['id_user' => 'id_user']],
            [['id_product'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['id_product' => 'id']],
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
            'id_product' => 'Adı',
            'id_user' => 'Satıcı',
            'id_client' => 'Müştəri',
            'reason' => 'Reason',
            'data' => 'Tarix',
            'quantity' => 'Sayı',
            'id_store' => 'Filial',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdClient()
    {
        return $this->hasOne(Client::className(), ['id_client' => 'id_client']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdUser()
    {
        return $this->hasOne(Users::className(), ['id_user' => 'id_user']);
    }
    public function getIdStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'id_store']);
    }
	public function getNameProduct()
	{
		return Product::find()->where(["id"=>$this->id_product])->one()->name;
	
	}
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'id_product']);
    }

    public function getSumNumber($number){
            $model=Returnp::find()->select("sum(quantity) as quantity")->where(["number"=>$number])->one()->quantity;

        return $model;
    }

    public function getSumReturn($model){
        $sum=0;
        $query =  Returnp::find()->select("sum(quantity) as quantity")
            ->joinWith('idProduct.idType')
            ->where($model->where)->one();


        return $query->quantity;
    }
	public function getSumSum($model)
	{
		$sum=0;
		foreach( Returnp::find()
            ->joinWith('idProduct.idType')
            ->where($model->where)->all() as $return)
			{
					$sum=$sum+$return->quantity*$return->price;
		
			}	
		return $sum;
	}
	public function getSumValue()
	{
		return $this->quantity*$this->price;
	}
    public function getGetNumber()
    {


        return $this->number."   ".Html::button('<i class="glyphicon glyphicon-ok"></i>Təsdiqlə',["onclick"=> "receivedReturn($this->number)"]);
    }

	public function getPriceAr()
    {
        return  Arrival::find()
            ->where(['id_product'=> $this->id_product])->orderBy("id DESC")->one()->price;

    }
	
	public function getPriceArSum()
    {
        return  Arrival::find()
            ->where(['id_product'=> $this->id_product])->orderBy("id DESC")->one()->price * $this->quantity;

    }
	public function getEarning()
    {
        return round( ($this->price - Arrival::find()
            ->where(['id_product'=> $this->id_product])->orderBy("id DESC")->one()->price)* $this->quantity,2);

    }
	
	
	  public function getSumVal($model){
        $sum=0;

      	foreach (   Returnp::find()
            ->joinWith('idProduct')->joinWith('idClient')
            ->where($model->where)
			->all() as $query )
		{
			$sum = $sum + Arrival::find()
            ->where(['id_product'=> $query->id_product])->orderBy("id DESC")->one()->price * $query->quantity;
			
			
		}
        return $sum;
    }
	
	 public function getSumEarning($model){
        $sum=0;

      	foreach (   Returnp::find()
            ->joinWith('idProduct')->joinWith('idClient')
            ->where($model->where)
			->all() as $query )
		{
			$sum = $sum + ($query->price - Arrival::find()
            ->where(['id_product'=> $query->id_product])->orderBy("id DESC")->one()->price) * $query->quantity;
			
			
		}
        return $sum;
    }
}
