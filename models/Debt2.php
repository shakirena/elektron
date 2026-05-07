<?php

namespace app\models;

use Yii;
use yii\bootstrap\Html;

/**
 * This is the model class for table "debt".
 *
 * @property integer $id
 * @property integer $id_user
 * @property double $debt
 * @property double $sum
 * @property string $datatime
 * @property integer $number
 * @property integer $id_contr
 * @property integer $discount
 * @property double $sum_usd
 *
 * @property Contractor $idContr
 * @property Users $idUser
 */
class Debt2 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'debt';
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
            [['id_user', 'datatime',  'id_contr'], 'required'],
            [['id_user', 'number', 'id_contr'], 'integer'],
            [['debt', 'sum', 'sum_usd','number','discount'], 'number'],
			[['note'], 'string', 'max' => 50],
            [['datatime'], 'safe'],
            [['id_contr'], 'exist', 'skipOnError' => true, 'targetClass' => Contractor::className(), 'targetAttribute' => ['id_contr' => 'id']],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['id_user' => 'id_user']],
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
            'debt' => 'Borc',
            'sum' => 'Cəmi',
            'datatime' => 'Gəbul tarixi',
            'number' => 'Sənədin nömrəsi',
            'id_contr' => 'Şirkət',
            'discount' => 'Discount',
            'sum_usd' => 'Sum Usd',

                    ];
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
    public function getIdUser()
    {
        return $this->hasOne(Users::className(), ['id_user' => 'id_user']);
    }

    public function getGetNumber()
    {


        return Html::a($this->number,'javascript:void(null);',['onclick'=>" window.open('../arrival/report1?number='+$this->number,'_blank')"]) ;
    }
	public function getSumDebt($model)
	{
		  $query = Debt::find()->select('sum(debt) as debt')
           
            ->where($model->where)->one();

        return $query->debt;
	}
}
