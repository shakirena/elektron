<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\History;

/**
 * HistorySearch represents the model behind the search form about `app\models\History`.
 */
class HistorySearch extends History
{
    /**
     * @inheritdoc
     */
	 
	public $type_name;
	public $date_start;
    public $date_end;
    public function rules()
    {
        return [
            [['id','id_store','id_contr'], 'integer'],
            [['rest','price','pricesell'], 'number'],
            [['date_create','date_start','date_end', 'date', 'id_product','type_name'], 'safe'],
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
        $query = History::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

         $query->joinWith(['idProduct.idType']);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'rest' => $this->rest,
            'date_create' => $this->date_create,
			'product.id_type' => $this->type_name,
			'id_store' => $this->id_store,
            'id_contr' => $this->id_contr,
        ])
        ->andFilterWhere(['between','date',$this->date_start,$this->date_end])
		  ->andFilterWhere(['like', 'product.name', $this->id_product]);

        return $dataProvider;
    }
}
