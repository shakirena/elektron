<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "kassa".
 *
 * @property integer $id
 * @property string $name
 * @property double $sum
 */
class Kassa extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'kassa';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['sum','pos'], 'number'],
            [['name'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'sum' => 'Sum',
        ];
    }
	
	public function kassaSum($id)
    {
       if ($id==1) {
        $prixod = Costs::find()->select("sum(sum) as sum")->where(['id_kassa' =>$id,'id_type' =>4])->one()->sum;
        $rasxod = Costs::find()->select("sum(sum) as sum")->where(['id_kassa' =>$id,'id_type' =>3])->one()->sum;
        $itog = round($prixod - $rasxod,2);
		}
		else $itog =round( Costs::find()->select("sum(sum) as sum")->where(['id_kassa' =>$id])->one()->sum, 2);
        return $itog;
    }
}
