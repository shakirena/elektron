<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Move;

/**
 * MoveSearch represents the model behind the search form about `app\models\Move`.
 */
class MoveSearch extends Move
{
    /**
     * @inheritdoc
     */
	public $date_start;
    public $date_end;
	public $barcode;
    public function rules()
    {
        return [
            [['id', 'id_num'], 'integer'],
            [['quantity', 'price', 'sum'], 'number'],
            [['datetime','date_start','date_end','id_product',  'type','barcode'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Move::find()->orderBy("datetime ASC");

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

		$query->joinWith(['idProduct']);
		$query->joinWith(['type0']);
		$query->joinWith(['idProduct.barcodes']);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
		if ( $this->date_start)  $this->date_start=$this->date_start." 00:00:00";
        if ($this->date_end)  $this->date_end=$this->date_end." 23:59:59";
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'sum' => $this->sum,
            'type' => $this->type,
           
            'id_num' => $this->id_num,
        ])
		->andFilterWhere(['like', '`bar_code`.`name`', $this->barcode])
		->andFilterWhere(['like', 'product.name', $this->id_product]);

        $query->andFilterWhere(['between','datetime',$this->date_start,$this->date_end]);
        return $dataProvider;
    }
}
