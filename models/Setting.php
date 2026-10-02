<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Universal key/value tənzimləmə anbarı (cədvəl: settings).
 * Fayl-əsaslı config-lərdən fərqli olaraq bazada saxlanılır, ona görə
 * proqram yenilənəndə (kod faylları üzərinə yazılanda) itmir.
 */
class Setting extends ActiveRecord
{
    public static function tableName()
    {
        return 'settings';
    }

    public static function get($name, $default = null)
    {
        $row = static::find()->where(['name' => $name])->one();
        return $row === null ? $default : $row->value;
    }

    public static function set($name, $value)
    {
        $row = static::find()->where(['name' => $name])->one();
        if ($row === null) {
            $row = new static();
            $row->name = $name;
        }
        $row->value = $value;
        return $row->save(false);
    }
}
