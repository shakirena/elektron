<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;

/**
 * ProductMovementSearch — модель для отчёта «Движение товара».
 *
 * Объединяет строки из 6 источников (arrival, sell, sell2, returnp,
 * return_arrival, sverka_log) через UNION ALL и возвращает
 * ArrayDataProvider для отображения в GridView.
 *
 * Используется ArrayDataProvider (не ActiveDataProvider), т.к. UNION
 * не порождает AR-объекты одного класса (ADR-4, Feature #27).
 *
 * Feature #27, Story #30.
 */
class ProductMovementSearch extends Model
{
    /** @var int|null Обязательный фильтр по товару */
    public $id_product;

    /** @var int|null Фильтр по складу (необязательно) */
    public $id_store;

    /** @var string|null Дата от (включительно), формат Y-m-d */
    public $date_from;

    /** @var string|null Дата до (включительно), формат Y-m-d */
    public $date_to;

    /** @var string|null Тип операции: arrival|sell|sell2|return_client|return_supplier|sverka */
    public $operation_type;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_product'], 'required'],
            [['id_product', 'id_store'], 'integer'],
            [['date_from', 'date_to', 'operation_type'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_product'     => 'Товар',
            'id_store'       => 'Склад',
            'date_from'      => 'Дата от',
            'date_to'        => 'Дата до',
            'operation_type' => 'Тип операции',
        ];
    }

    /**
     * Выполняет UNION-запрос и возвращает ArrayDataProvider.
     *
     * Если id_product не передан, validate() вернёт false и метод
     * вернёт пустой провайдер без обращения к БД.
     *
     * @param array $params Данные формы ($_GET или $_POST).
     * @return ArrayDataProvider
     */
    public function search($params)
    {
        $this->load($params);

        if (!$this->validate()) {
            return new ArrayDataProvider([
                'allModels'  => [],
                'pagination' => ['pageSize' => 30],
                'sort'       => [
                    'attributes'   => ['event_datetime', 'operation_type', 'quantity'],
                    'defaultOrder' => ['event_datetime' => SORT_DESC],
                ],
            ]);
        }

        $sql      = $this->buildUnionSql();
        $bindings = $this->buildBindings();
        $rows     = Yii::$app->db->createCommand($sql)->bindValues($bindings)->queryAll();

        return new ArrayDataProvider([
            'allModels'  => $rows,
            'pagination' => ['pageSize' => 30],
            'sort'       => [
                'attributes'   => ['event_datetime', 'operation_type', 'quantity'],
                'defaultOrder' => ['event_datetime' => SORT_DESC],
            ],
        ]);
    }

    /**
     * Строит UNION ALL SQL из 6 источников движения товара.
     *
     * Все условия фильтрации применяются через bindValues() —
     * конкатенация пользовательского ввода в строку SQL отсутствует.
     *
     * Колонки результата: operation_type, source_id, id_product,
     * id_store, quantity, price, event_datetime, document_number,
     * counterparty_id, client_id.
     *
     * @return string
     */
    protected function buildUnionSql()
    {
        return <<<SQL
SELECT * FROM (
    SELECT 'arrival' AS operation_type,
        a.id AS source_id, a.id_product, a.id_store,
        a.quantity, a.price, a.datetime AS event_datetime,
        a.number AS document_number, a.id_contr AS counterparty_id, NULL AS client_id
    FROM arrival a
    WHERE a.received = 1 AND (a.id_contr IS NULL OR a.id_contr >= 1)

    UNION ALL

    SELECT 'sell',
        s.id, s.id_product, s.id_store,
        s.quantity, s.price, s.datetime,
        s.number, NULL, s.id_client
    FROM sell s
    WHERE s.sold = 1 AND s.returnp = 0

    UNION ALL

    SELECT 'sell2',
        s2.id, s2.id_product, s2.id_store,
        s2.quantity, s2.price, s2.datetime,
        s2.number, NULL, s2.id_client
    FROM sell2 s2
    WHERE s2.sold = 1 AND s2.returnp = 0

    UNION ALL

    SELECT 'return_client',
        r.id, r.id_product, r.id_store,
        r.quantity, r.price, r.data,
        r.number, NULL, r.id_client
    FROM returnp r

    UNION ALL

    SELECT 'return_supplier',
        ra.id, ra.id_product, ra.id_store,
        ra.quantity, ra.price, ra.date,
        NULL, ra.id_contr, NULL
    FROM return_arrival ra
    WHERE ra.received = 1

    UNION ALL

    SELECT 'sverka',
        sl.id, sl.id_product, sl.id_store,
        sl.delta AS quantity, NULL AS price, sl.datetime,
        NULL, NULL, NULL
    FROM sverka_log sl
) t
WHERE t.id_product = :id_product
  AND (:id_store IS NULL OR t.id_store = :id_store)
  AND (:date_from IS NULL OR t.event_datetime >= :date_from)
  AND (:date_to IS NULL OR t.event_datetime <= :date_to)
  AND (:operation_type IS NULL OR t.operation_type = :operation_type)
ORDER BY t.event_datetime DESC
SQL;
    }

    /**
     * Формирует массив параметров для bindValues().
     *
     * Необязательные фильтры передаются как NULL, что позволяет
     * условиям (:param IS NULL OR ...) работать как «без фильтра».
     *
     * @return array<string, int|string|null>
     */
    protected function buildBindings()
    {
        return [
            ':id_product'     => (int) $this->id_product,
            ':id_store'       => $this->id_store ? (int) $this->id_store : null,
            ':date_from'      => $this->date_from ?: null,
            ':date_to'        => $this->date_to ?: null,
            ':operation_type' => $this->operation_type ?: null,
        ];
    }

    /**
     * Возвращает читаемые метки типов операций для GridView/DropDown.
     *
     * @return array<string, string>
     */
    public static function operationLabels()
    {
        return [
            'arrival'         => 'Приход',
            'sell'            => 'Продажа',
            'sell2'           => 'Продажа (опт)',
            'return_client'   => 'Возврат от клиента',
            'return_supplier' => 'Возврат поставщику',
            'sverka'          => 'Сверка',
        ];
    }
}
