<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "bar_code".
 *
 * @property integer $id
 * @property integer $id_product
 * @property string $name
 *
 * @property Product $idProduct
 */
class Barcodep extends \yii\db\ActiveRecord
{
	public $count;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bar_code';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_product'], 'required'],
            [['id_product'], 'integer'],
            [['name'], 'string', 'max' => 50],
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
            'name' => 'Barkodu',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'id_product']);
    }
}
