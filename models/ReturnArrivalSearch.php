<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ReturnArrival;

/**
 * ReturnArrivalSearch represents the model behind the search form about `app\models\ReturnArrival`.
 */
class ReturnArrivalSearch extends ReturnArrival
{
	public $date_start;
    public $date_end;
	 public $type;
    public $name_product;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'id_user', 'id_product', 'id_contr', 'id_store', 'received'], 'integer'],
            [['quantity', 'price', 'usd'], 'number'],
            [['date','date_start','date_end','type','name_product'], 'safe'],
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
        $query = ReturnArrival::find();
		$query->joinWith(['idProduct.idType']);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

		
        $dataProvider->sort->attributes['type'] = [
            'asc' => ['type_product.name' => SORT_ASC],
            'desc' => ['type_product.name' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
		
        // grid filtering conditions
        $query->andFilterWhere([
            'return_arrival.id' => $this->id,
            'id_user' => $this->id_user,
            'id_product' => $this->id_product,
            'id_contr' => $this->id_contr,
			'type_product.name' => $this->type,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'usd' => $this->usd,
            'id_store' => $this->id_store,
            
            'received' => $this->received,
        ]);
		 $query->andFilterWhere(['like', 'product.name', $this->name_product]);
        $query->andFilterWhere(['between','date',$this->date_start,$this->date_end]);
        return $dataProvider;
    }
}
