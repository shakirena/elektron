<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "type_costs".
 *
 * @property integer $id
 * @property string $name
 */
class TypeCosts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'type_costs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','type'], 'required'],
            [['name'], 'string', 'max' => 100],
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
			 'type' => 'Name',
        ];
    }
}
