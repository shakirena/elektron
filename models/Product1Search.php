<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Product;

/**
 * ProductSearch represents the model behind the search form about `app\models\Product`.
 */
class Product1Search extends Product
{
    public $rest;
    public $barcode;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'id_type','rest'], 'integer'],
            [['name','barcode'], 'safe'],
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
      /* $query=Yii::$app
            ->db
            ->createCommand("SELECT A.name,(SELECT sum(B.rest) as sum FROM arrival B WHERE A.id=B.id_product GROUP BY B.id_product) FROM product as A")
          ;*/



          $query = Product::find();
        //  ->select("name,id_product,".Arrival::find()->select("sum(rest) as sum")->where("product.id=id_product")->groupBy("id_product")->one());
        // add conditions that should always apply here*/
        $query->join('JOIN', 'bar_code','bar_code.id_product=product.id')->where("length(bar_code.name)<=5");
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' =>'10',
            ]
        ]);
$dataProvider->sort->attributes['name'] = [
            'asc' => ['product.name' => SORT_ASC],
            'desc' => ['product.name' => SORT_DESC],
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

            'id_type' => $this->id_type,
        ]);

        $query->andFilterWhere(['like', 'product.name', $this->name]);
        $query->andFilterWhere(['like', 'bar_code.name', $this->barcode]);
          //  ->andFilterWhere(['like', 'bar_code', $this->bar_code]);

        return $dataProvider;
    }
}
