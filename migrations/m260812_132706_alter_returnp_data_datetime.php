<?php

use yii\db\Migration;

/**
 * Feature #27: Отчёт «Движение товара».
 *
 * Расширяет `returnp.data` с DATE до DATETIME, чтобы в единый хронологический
 * отчёт возвраты от клиентов попадали с точностью до секунды.
 *
 * MySQL безопасно преобразует существующие значения `YYYY-MM-DD` в
 * `YYYY-MM-DD 00:00:00` без потери данных. Старые записи после миграции
 * будут отображаться с временем 00:00:00 — это документированное поведение
 * (см. issue #27, раздел "Вне scope").
 *
 * После применения этой миграции нужно обновить SellController::actionReceivedReturn():
 *   $returnp->data = date("Y-m-d H:i:s");   // было: date("Y-m-d")
 */
class m260812_132706_alter_returnp_data_datetime extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('returnp', 'data', $this->dateTime()->null());
    }

    public function safeDown()
    {
        // Обрежет время для записей, созданных после миграции — data loss.
        // Приемлемо только для явного отката в dev-окружении.
        $this->alterColumn('returnp', 'data', $this->date()->null());
    }
}
