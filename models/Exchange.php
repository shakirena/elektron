<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "exchange".
 *
 * @property integer $id
 * @property double $rates
 * @property string $date
 * @property integer $flag
 */
class Exchange extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'exchange';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rates', 'date', 'flag'], 'required'],
            [['rates'], 'number'],
            [['date'], 'safe'],
            [['flag'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rates' => 'Rates',
            'date' => 'Date',
            'flag' => 'Flag',
        ];
    }
}
