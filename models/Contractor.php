<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contractor".
 *
 * @property integer $id
 * @property string $name
 * @property string $telephone
 * @property string $specification
 *
 * @property Debt[] $debts
 * @property Finance[] $finances
 */
class Contractor extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contractor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['specification'], 'string'],
            [['name', 'telephone'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Şerkətin adı',
            'telephone' => 'Telefon',
            'specification' => 'Qeyd',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDebts()
    {
        return $this->hasMany(Debt::className(), ['id_contr' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFinances()
    {
        return $this->hasMany(Finance::className(), ['id_contr' => 'id']);
    }
}
