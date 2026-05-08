<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Arrival;

/**
 * ArrivalSearch represents the model behind the search form about `app\models\Arrival`.
 */
class ArrivalSearch extends Arrival
{
    public $date_start;
    public $date_end;
    public $type;
	public $type_name;
    public $name_product;
	public $barcode;
    public $article_number;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'id_user', 'id_store', 'received', 'id_contr', 'number', 'discount', 'returnp', 'rest','postponed','transfer'], 'integer'],

            [['datetime','date_start','date_end', 'id_product','type','name_product','barcode','type_name','article_number'], 'safe'],
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
        $query = Arrival::find()->andWhere("id_contr>=1 or id_contr IS NULL")->orderBy("number DESC");;

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [

                'pageSize' =>'30',
            ],
            'sort' => false,
        ]);

        $query->joinWith(['idProduct.idType']);
		$query->join('JOIN', 'bar_code','bar_code.id_product=arrival.id_product');

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
   if ( $this->date_start)  $this->date_start=$this->date_start." 00:00:00";
        if ($this->date_end)  $this->date_end=$this->date_end." 23:59:59";
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'id_product' => $this->id_product,
            'id_user' => $this->id_user,
          
            'id_store' => $this->id_store,
            'received' => $this->received,
            'id_contr' => $this->id_contr,
            'number' => $this->number,
            'discount' => $this->discount,
            'returnp' => $this->returnp,
            'rest' => $this->rest,
			'transfer' => $this->transfer,
            'postponed' =>$this->postponed,
			'product.id_type' => $this->type_name,

        ])
		  ->andFilterWhere(['like', '`bar_code`.`name`', $this->barcode])
            ->andFilterWhere(['like', 'product.name', $this->name_product]);
        $query->andFilterWhere(['like', 'product.article_number', $this->article_number]);
        $query->andFilterWhere(['between','datetime',$this->date_start,$this->date_end]);
        return $dataProvider;
    }
}
