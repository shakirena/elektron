<?php

namespace app\models;

use Yii;
use yii\bootstrap\Html;

/**
 * This is the model class for table "balance".
 *
 * @property integer $id
 * @property integer $id_user
 * @property string $datetime
 * @property double $sum
 * @property double $current_sum
 * @property integer $id_type
 * @property string $note
 *
 * @property Users $idUser
 * @property TypeBalance $idType
 */
class Balance extends \yii\db\ActiveRecord
{
public $sum1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'balance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_user', 'datetime', 'id_type','id_store'], 'required'],
            [['id_user', 'id_type','number','id_client'], 'integer'],
            [['datetime'], 'safe'],
            [['sum', 'current_sum','usd'], 'number'],
            [['note','user_name'], 'string', 'max' => 50],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['id_user' => 'id_user']],
            [['id_type'], 'exist', 'skipOnError' => true, 'targetClass' => TypeBalance::className(), 'targetAttribute' => ['id_type' => 'id']],
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
            'id_user' => 'Kassir',
            'datetime' => 'Tarix',
            'sum' => 'Sum',
            'current_sum' => 'Current Sum',
            'id_type' => 'Type',
            'note' => 'Açıqlaması',
            'user_name' => 'Kimə verilir',
            'type'=>'Type',
            'id_store' =>'Filial'

        ];
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
    public function getIdType()
    {
        return $this->hasOne(TypeBalance::className(), ['id' => 'id_type']);
    }
    public function getIdStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'id_store']);
    }
    public function getNameType()
    {
       $sum= Balance::find()->select("sum(sum) as sum1")->where(['datetime'=>$this->datetime, 'id_type'=>$this->id_type])->groupBy("id_type")->one()->sum1;
        return $this->getIdType()->one()->name." (Məbləğ  ".round($sum,2).")";

    }
    public function getType()
    {
       if($this->getIdType()->one()->type) return "Məxaric";
        else return "Mədaxil";
        //return $this->hasOne(TypeBalance::className(), ['id' => 'id_type']);
    }

    public function getNoteString()
    {
        if ($this->id_type==1) return Html::a("$this->note","../sell/report1?number=$this->number");
        if ($this->id_type==2) return Html::a("$this->note","../sell/dialog1?number=$this->number");
        if ($this->id_type==15 && $this->number) return Html::a("$this->number","../sell/report1?number=$this->number");
        return $this->note;
    }
}
