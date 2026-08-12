<?php

use yii\db\Migration;

/**
 * Feature #27: Отчёт «Движение товара».
 *
 * Создаёт таблицу `sverka_log` — журнал применённых сверок.
 * Строки sverka удаляются в SverkaController::actionReceived() сразу после
 * применения, поэтому истории изменений остатков через сверку нет вообще.
 *
 * Эта таблица заполняется непосредственно перед `$model->delete()` в
 * actionReceived(). В единый отчёт «Движение товара» попадают только сверки,
 * применённые ПОСЛЕ внедрения этой миграции — ретроспективные данные
 * восстановить невозможно (см. issue #27, раздел "Вне scope").
 *
 * Индексы:
 *   - (id_product, datetime) — под основной UNION-запрос отчёта
 *   - (id_store) — под фильтр по складу
 */
class m260812_132707_create_sverka_log extends Migration
{
    public function safeUp()
    {
        $this->createTable('sverka_log', [
            'id'         => $this->primaryKey(),
            'id_product' => $this->integer()->notNull(),
            'id_store'   => $this->integer()->notNull(),
            'qty_before' => $this->decimal(15, 3)->notNull()->defaultValue(0),
            'qty_after'  => $this->decimal(15, 3)->notNull()->defaultValue(0),
            'delta'      => $this->decimal(15, 3)->notNull()->defaultValue(0),
            'id_user'    => $this->integer()->notNull(),
            'datetime'   => $this->dateTime()->notNull(),
        ]);

        $this->createIndex(
            'idx_sverka_log_product_dt',
            'sverka_log',
            ['id_product', 'datetime']
        );

        $this->createIndex(
            'idx_sverka_log_store',
            'sverka_log',
            'id_store'
        );
    }

    public function safeDown()
    {
        $this->dropTable('sverka_log');
    }
}
