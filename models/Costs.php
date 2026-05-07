<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "costs".
 *
 * @property integer $id
 * @property integer $id_type
 * @property double $sum
 * @property string $note
 * @property string $datetime
 *
 * @property TypeCosts $idType
 */
class Costs extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'costs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'sum', 'datetime','id_user'], 'required'],
            [['id_type','id_client','id_kassa','id_user','from_kassa', 'fid'], 'integer'],
            [['sum'], 'number'],
            [['note'], 'string'],
            [['datetime'], 'safe'],
            [['id_type'], 'exist', 'skipOnError' => true, 'targetClass' => TypeCosts::className(), 'targetAttribute' => ['id_type' => 'id']],
			[['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['id_user' => 'id_user']],
			[['id_kassa'], 'exist', 'skipOnError' => true, 'targetClass' => Kassa::className(), 'targetAttribute' => ['id_kassa' => 'id']],
			[['from_kassa'], 'exist', 'skipOnError' => true, 'targetClass' => Kassa::className(), 'targetAttribute' => ['id_kassa' => 'id']],
            [['id_client'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['id_client' => 'id_client']],
       
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_type' => 'Type',
            'sum' => 'Məbləğ',
            'note' => 'Qeyd',
            'datetime' => 'Tarix',
			'id_kassa' => 'Kassa adı',
			'from_kassa' => 'Kassadan'
			
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdType()
    {
        return $this->hasOne(TypeCosts::className(), ['id' => 'id_type']);
    }
	public function getIdKassa()
    {
        return $this->hasOne(Kassa::className(), ['id' => 'id_kassa']);
    }
	 public function getFromKassa()
    {
        return $this->hasOne(Kassa::className(), ['id' => 'from_kassa']);
    }
	 public function getIdUser()
    {
        return $this->hasOne(Users::className(), ['id_user' => 'id_user']);
    }
	 public function getGetSum()
    {
		if ($this->sum<0) return -$this->sum;
        return $this->sum;
    }
	public function getTypeName()
	{
	 if ($this->fid) 
		{
			if ($this->sum>0)	
				{
					$name = Client::find()->where(["id_client" => Dclient::find()->where(["id" =>$this->fid])->one()->id_client])->one()->fio;
					$type= $this->getIdType()->one()->name." ( $name )";
									
				}
			else 
			{
			
					$name = Contractor::find()->where(["id" => Debt::find()->where(["id" =>$this->fid])->one()->id_contr])->one()->name;
					$type=  $this->getIdType()->one()->name." ( $name )";
			
			}
		}
		else $type = $this->getIdType()->one()->name;
		return $type;
	
	
	}
	 public function getSum($model){
        $sum=0;
        $query =  Costs::find()->select("sum(abs(sum)) as sum")
         ->joinWith(["idType"])
            ->where($model->where)->andWhere("id_type!=4")->one();


        return $query->sum;
    }
}
