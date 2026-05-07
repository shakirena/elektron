<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "history".
 *
 * @property integer $id
 * @property integer $id_product
 * @property double $rest
 * @property string $date_create
 * @property string $date
 * @property double $pricesell
 * @property integer $id_store
 * @property integer $id_contr
 * @property double $price
 *
 * @property Product $idProduct
 * @property Contractor $idContr
 * @property Store $idStore
 */
class History extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_product', 'rest', 'date_create', 'date', 'id_store'], 'required'],
            [['id_product', 'id_store', 'id_contr'], 'integer'],
            [['rest', 'pricesell', 'price'], 'number'],
            [['date_create', 'date'], 'safe'],
            [['id_product'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['id_product' => 'id']],
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
            'id_product' => 'Id Product',
            'rest' => 'Rest',
            'date_create' => 'Date Create',
            'date' => 'Date',
            'pricesell' => 'Pricesell',
            'id_store' => 'Id Store',
            'id_contr' => 'Id Contr',
            'price' => 'Price',
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
	
	 public function getSumHistory($model,$column){
        $sum=0;
        $query = History::find()->select('sum(pricesell) as pricesell,sum(price*rest) as sum,sum(price) as price,sum(rest) as rest')
            ->joinWith('idProduct.idType')
            ->where($model->where)
			->one();
		
        return round($query->$column,2);
    }
}
