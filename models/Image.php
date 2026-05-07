<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "image".
 *
 * @property integer $id
 * @property string $path
 * @property string $note
 * @property integer $id_tre
 *
 * @property Treatment[] $treatments
 */
class Image extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'image';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['note'], 'string'],
            [['id_tre'], 'required'],
            [['id_tre'], 'integer'],
            [['path','thumb'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'path' => 'Path',
            'note' => 'Note',
            'id_tre' => 'Id Tre',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTreatments()
    {
        return $this->hasMany(Product::className(), ['id_image' => 'id']);
    }
}
