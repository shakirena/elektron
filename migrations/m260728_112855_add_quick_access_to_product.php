<?php
use yii\db\Migration;

class m260728_112855_add_quick_access_to_product extends Migration
{
    public function safeUp()
    {
        $this->addColumn('product', 'quick_access', $this->boolean()->notNull()->defaultValue(0)->after('article_number'));
    }

    public function safeDown()
    {
        $this->dropColumn('product', 'quick_access');
    }
}
