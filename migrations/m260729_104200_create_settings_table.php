<?php

use yii\db\Migration;

/**
 * Universal key/value tənzimləmə cədvəli. Əvvəllər "Şirkət məlumatı" və
 * "Görünüş tənzimləmələri" config/*.php fayllarında saxlanılırdı — proqram
 * yenilənəndə (fayllar üzərinə yazılanda) bu məlumatlar itirdi. Bazada
 * saxlanıldıqda kod yeniləməsi onlara toxunmur.
 *
 * safeUp() cədvəli yaradandan sonra, əgər köhnə config faylları hələ də
 * mövcuddursa, onlardakı dəyərləri BİR DƏFƏLİK yeni cədvələ köçürür ki,
 * müştəri artıq doldurduğu məlumatları itirməsin.
 */
class m260729_104200_create_settings_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('settings', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->unique(),
            'value' => $this->text()->null(),
        ]);

        $companyConfigPath = Yii::getAlias('@app/config/company.php');
        if (is_file($companyConfigPath)) {
            $company = require($companyConfigPath);
            foreach ($company as $key => $val) {
                $this->insert('settings', ['name' => 'company.' . $key, 'value' => (string) $val]);
            }
        }

        $displayConfigPath = Yii::getAlias('@app/config/display_settings.php');
        if (is_file($displayConfigPath)) {
            $display = require($displayConfigPath);
            foreach ($display as $key => $val) {
                $this->insert('settings', ['name' => 'display.' . $key, 'value' => $val ? '1' : '0']);
            }
        }
    }

    public function safeDown()
    {
        $this->dropTable('settings');
    }
}
