<?php

use yii\db\Migration;

/**
 * Aylıq sverka: снимки «Stok + Portfel + Kassa − Borc» на момент закрытия месяца.
 *
 * Остатки (arrival.rest, баланс касс, долги) в БД хранятся только текущие —
 * восстановить их на прошлую дату нельзя. Поэтому на конец месяца сохраняется
 * снимок, и следующий месяц сравнивается с ним.
 *
 * max_id_* — максимальные id таблиц на момент снимка: по ним видно записи,
 * внесённые после снимка, но датированные задним числом.
 */
class m261002_120000_create_monthly_sverka extends Migration
{
    public function safeUp()
    {
        $this->createTable('monthly_sverka', [
            'id'             => $this->primaryKey(),
            'datetime'       => $this->dateTime()->notNull(),
            'stok'           => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'stok_draft'     => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'portfel'        => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'portfel_minus'  => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'kassa'          => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'borc'           => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'netice'         => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'is_manual'      => $this->boolean()->notNull()->defaultValue(0),
            'note'           => $this->string(255),
            'id_user'        => $this->integer()->notNull(),
            'max_id_arrival' => $this->integer(),
            'max_id_sell'    => $this->integer(),
            'max_id_costs'   => $this->integer(),
            'max_id_debt'    => $this->integer(),
            'max_id_dclient' => $this->integer(),
        ]);

        $this->createIndex('idx_monthly_sverka_datetime', 'monthly_sverka', 'datetime');
    }

    public function safeDown()
    {
        $this->dropTable('monthly_sverka');
    }
}
