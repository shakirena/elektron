<?php

namespace app\models;

use yii\base\Model;

/**
 * Форма управления видимостью полей на панели продажи (sell/index,
 * sell/index-v2), приходе (arrival/index) и в отчётах, которые
 * показываются кассирам/сотрудникам.
 * Хранится в таблице settings (name = 'display.*') — переживает обновления
 * программы, в отличие от прежнего config/display_settings.php.
 */
class DisplaySettingsForm extends Model
{
    public $show_discount = 1;
    public $show_store = 1;
    public $show_seller = 1;
    public $show_unit_price = 1;
    public $show_box_count = 1;
    public $show_shelf = 1;
    public $show_article_number = 1;
    public $show_sn = 1;

    const FIELDS = [
        'show_discount', 'show_store', 'show_seller',
        'show_unit_price', 'show_box_count', 'show_shelf', 'show_article_number',
        'show_sn',
    ];

    public function rules()
    {
        return [
            [self::FIELDS, 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'show_discount' => 'Güzəşt',
            'show_store' => 'Anbar',
            'show_seller' => 'Satıcı',
            'show_unit_price' => 'Ədədin qiyməti',
            'show_box_count' => 'Qutuda ədəd sayı',
            'show_shelf' => 'Polka',
            'show_article_number' => 'Artikul nömrəsi',
            'show_sn' => 'Qeyd (SN)',
        ];
    }

    /**
     * Загружает текущие значения из таблицы settings.
     */
    public function loadCurrent()
    {
        $data = self::rawConfig();
        foreach ($data as $attribute => $value) {
            $this->$attribute = $value ? 1 : 0;
        }
    }

    /**
     * Сохраняет значения обратно в таблицу settings.
     * @return bool
     */
    public function saveConfig()
    {
        if (!$this->validate()) {
            return false;
        }

        foreach (self::FIELDS as $field) {
            Setting::set('display.' . $field, ((bool) $this->$field) ? '1' : '0');
        }

        return true;
    }

    /**
     * @return array{show_discount:bool,show_store:bool,show_seller:bool,show_unit_price:bool,show_box_count:bool,show_shelf:bool,show_article_number:bool,show_sn:bool}
     */
    private static function rawConfig()
    {
        $result = [];
        foreach (self::FIELDS as $field) {
            $stored = Setting::get('display.' . $field, null);
            $result[$field] = $stored === null ? true : (bool) (int) $stored;
        }
        return $result;
    }

    /**
     * Читает текущие настройки без создания модели/формы.
     * Используется во views (sell/index, sell/index-v2, arrival/index,
     * отчёты) для скрытия полей.
     * @return array
     */
    public static function current()
    {
        return self::rawConfig();
    }
}
