<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "client".
 *
 * @property integer $id_client
 * @property string $fio
 * @property string $phone
 * @property string $adress
 * @property string $mobile
 * @property string $note
 * @property string $email
 *
 * @property Dclient[] $dclients
 * @property Finance[] $finances
 * @property Returnp[] $returnps
 * @property Sell[] $sells
 */
class Client extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'client';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fio'], 'required'],
            [['fio','phone'], 'string', 'max' => 150],
            [['mobile', 'note', 'email'], 'string', 'max' => 50],
            [['adress','barcode'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_client' => 'Kod',
            'fio' => 'SAA',
            'phone' => 'Telefon',
            'adress' => 'Ünvan  ',
            'mobile' => 'Mobil',
            'note' => 'Note',
            'email' => 'E-mail',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDclients()
    {
        return $this->hasMany(Dclient::className(), ['id_client' => 'id_client']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFinances()
    {
        return $this->hasMany(Finance::className(), ['id_client' => 'id_client']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReturnps()
    {
        return $this->hasMany(Returnp::className(), ['id_client' => 'id_client']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSells()
    {
        return $this->hasMany(Sell::className(), ['id_client' => 'id_client']);
    }
}
