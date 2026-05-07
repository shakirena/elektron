<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "bonus".
 *
 * @property integer $id
 * @property string $name
 * @property double $par1
 * @property double $par2
 * @property double $par3
 * @property integer $status
 */
class Bonus extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bonus';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'status'], 'required'],
            [['par1', 'par2', 'par3'], 'number'],
            [['status'], 'integer'],
            [['name'], 'string', 'max' => 200],
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
            'par1' => 'Par1',
            'par2' => 'Par2',
            'par3' => 'Par3',
            'status' => 'Status',
        ];
    }
}
