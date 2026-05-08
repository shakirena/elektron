<?php
use yii\db\Migration;

class m260508_000001_add_article_number_to_product extends Migration
{
    public function safeUp()
    {
        $this->addColumn('product', 'article_number', $this->string(100)->null()->after('name'));
    }

    public function safeDown()
    {
        $this->dropColumn('product', 'article_number');
    }
}
