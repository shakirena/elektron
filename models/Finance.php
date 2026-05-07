<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "finance".
 *
 * @property integer $id
 * @property integer $from_kassa
 * @property integer $to_kassa
 * @property double $sum
 * @property string $note
 * @property integer $id_user
 * @property string $datetime
 *
 * @property Kassa $fromKassa
 * @property Kassa $toKassa
 */
class Finance extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'finance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['from_kassa', 'to_kassa', 'sum', 'id_user', 'datetime'], 'required'],
            [['from_kassa', 'to_kassa', 'id_user'], 'integer'],
            [['sum'], 'number'],
            [['note'], 'string'],
            [['datetime'], 'safe'],
            [['from_kassa'], 'exist', 'skipOnError' => true, 'targetClass' => Kassa::className(), 'targetAttribute' => ['from_kassa' => 'id']],
            [['to_kassa'], 'exist', 'skipOnError' => true, 'targetClass' => Kassa::className(), 'targetAttribute' => ['to_kassa' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from_kassa' => 'From Kassa',
            'to_kassa' => 'To Kassa',
            'sum' => 'Sum',
            'note' => 'Note',
            'id_user' => 'Id User',
            'datetime' => 'Datetime',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromKassa()
    {
        return $this->hasOne(Kassa::className(), ['id' => 'from_kassa']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToKassa()
    {
        return $this->hasOne(Kassa::className(), ['id' => 'to_kassa']);
    }
}
