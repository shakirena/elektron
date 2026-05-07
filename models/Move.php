<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "move".
 *
 * @property integer $id
 * @property integer $id_product
 * @property double $quantity
 * @property double $price
 * @property double $sum
 * @property integer $type
 * @property string $datetime
 *
 * @property Product $idProduct
 * @property TypeMove $type0
 */
class Move extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'move';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_product', 'quantity', 'price', 'sum', 'type', 'datetime'], 'required'],
            [['id_product', 'type'], 'integer'],
            [['quantity', 'price', 'sum'], 'number'],
            [['datetime'], 'safe'],
            [['id_product'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['id_product' => 'id']],
            [['type'], 'exist', 'skipOnError' => true, 'targetClass' => TypeMove::className(), 'targetAttribute' => ['type' => 'id']],
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
            'price' => 'Price',
            'sum' => 'Sum',
            'type' => 'Type',
            'datetime' => 'Datetime',
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
    public function getType0()
    {
        return $this->hasOne(TypeMove::className(), ['id' => 'type']);
    }
	
	 public function getNameBarcode()
    {
        $bar='';
        foreach (Barcodep::find()->where(["id_product" =>$this->getIdProduct()->one()->id])->all() as $barcode)
        {
            $bar.=$barcode->name.',';
        }
        $bar=rtrim($bar,',');
        return $bar;
    }
	
	
	   public function getSumArrival($model,$column){
        $sum=0;
		$model2=$model->where;
		
		foreach ($model2 as $key=>$where)
		{
			if ($where[1]=="`bar_code`.`name`") 
					{ 
			
						foreach (Barcodep::find()->andWhere(['like', 'name',"%$where[2]%", false])->all() as $barcode){

						$product[]=$barcode->id_product;
						
						}
						unset($model2[$key]);
						
					}
		}
        $query =  Move::find()->select("sum(quantity) as quantity,sum(sum) as sum")
            ->joinWith('idProduct')
            ->joinWith('type0')
            ->andWhere($model2
			)->andFilterWhere(["in","id_product",$product])->one();


        return $query->$column;
    }
}
