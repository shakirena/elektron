<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Arrival;

/**
 * ArrivalSearch represents the model behind the search form about `app\models\Arrival`.
 */
class RestSearch extends Arrival
{
    public $date_start;
    public $date_end;
    public $type;
    public $type_name;
    public $sumsell;
	public $barcode;
    public $name_product;
    public $article_number;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'id_user', 'id_store', 'received', 'id_contr', 'number', 'discount', 'returnp', 'rest','postponed'], 'integer'],
            [['quantity', 'price', 'usd', 'pricesell', 'sum'], 'number'],
            [['datetime','date_start','date_end', 'id_product','type','sumsell','name_product','type_name','barcode','article_number'], 'safe'],
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
        //$query1=Arrival::find()->
        $query = Arrival::find()->select('arrival.id as id,sum(rest) as rest,sum(price*rest) as sum_azn,sum(usd*rest) as sum_usd,arrival.id_product,id_store,price,usd,pack,price_top,polka,id_contr,trade_price')
            ->where(['received' => '1', 'postponed'=>0])->groupBy(['arrival.id_product', 'id_store'])->orderBy("id_type,arrival.id_product");

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'key' => function($model){
                return $model->id;
            },
            'pagination' => [

                'pageSize' =>'100',
            ],
        ]);

        $query->joinWith(['idProduct.idType']);

  // $query->join('JOIN', 'bar_code','bar_code.id_product=arrival.id_product');
        $dataProvider->sort->attributes['type_name'] = [
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
            'id' => $this->id,
            'id_user' => $this->id_user,
            'product.id_type' => $this->type_name,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'usd' => $this->usd,
            'pricesell' => $this->pricesell,
            'sum' => $this->sum,
            'id_store' => $this->id_store,
            'received' => $this->received,
            'postponed' =>$this->postponed,
            'datetime' => $this->datetime,
            'id_contr' => $this->id_contr,
            'id_product' => $this->id_product,
            'number' => $this->number,
            'discount' => $this->discount,
            'returnp' => $this->returnp,
			'type_product.id'=> $this->type,
            'rest' => $this->rest,

        ]);
           $query->andFilterWhere(['like', 'bar_code.name', $this->barcode]);
		     // $query->andFilterWhere(['like', 'type_product.name', $this->type]);
            // $query->andFilterWhere(['like', 'product.name', "$this->name_product%",false]);
 $query->andFilterWhere(['like', 'product.name', $this->name_product]);
        $query->andFilterWhere(['like', 'product.article_number', $this->article_number]);
        return $dataProvider;
    }
}
