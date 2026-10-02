<?php

namespace app\models;

use yii\base\Model;

/**
 * Форма редактирования реквизитов компании, которые подставляются во все
 * чеки: sell/print, print2, print_paym, print_return.
 * Хранится в таблице settings (name = 'company.*') — переживает обновления
 * программы, в отличие от прежнего config/company.php.
 */
class CompanySettingsForm extends Model
{
    public $name;
    public $instagram;
    public $whatsapp;
    public $phone;
    public $mobile;

    const FIELDS = ['name', 'instagram', 'whatsapp', 'phone', 'mobile'];

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'instagram', 'whatsapp', 'phone', 'mobile'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Şirkətin adı',
            'instagram' => 'Instagram',
            'whatsapp' => 'WhatsApp',
            'phone' => 'Telefon',
            'mobile' => 'Mobil',
        ];
    }

    /**
     * Загружает текущие значения из таблицы settings.
     */
    public function loadCurrent()
    {
        foreach (self::FIELDS as $field) {
            $this->$field = Setting::get('company.' . $field, '');
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
            Setting::set('company.' . $field, (string) $this->$field);
        }

        return true;
    }
}
