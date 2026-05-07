<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Product;

/**
 * ProductSearch represents the model behind the search form about `app\models\Product`.
 */
class MoveSearch extends Arrival
{
    public $rest;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_user', 'id_product'], 'required'],
            [['id_user', 'id_product', 'id_store', 'received', 'id_contr', 'number', 'discount', 'returnp', 'rest'], 'integer'],
            [['quantity', 'price', 'usd', 'pricesell', 'sum'], 'number'],
            [['datetime'], 'safe'],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['id_user' => 'id_user']],
            [['id_store'], 'exist', 'skipOnError' => true, 'targetClass' => Store::className(), 'targetAttribute' => ['id_store' => 'id']],
            [['id_contr'], 'exist', 'skipOnError' => true, 'targetClass' => Contractor::className(), 'targetAttribute' => ['id_contr' => 'id']],
            [['id_product'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['id_product' => 'id']],
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
        $d=4;
        $query1= Sell::find()->select("sum(quantity) as quantity,number,id_store,datetime,id_client,flag as flag")
            ->andWhere("id_product=1")
            ->groupBy("number");



        $query =Arrival::find()->select("sum(quantity) as quantity,number,id_store,datetime,id_contr,sum(quantity) as flag")
            ->andWhere("id_product=1")
            ->groupBy("number")
            ->union($query1,false)
            ->orderBy("datetime ASC");
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' =>'10',
            ]
        ]);

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

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'bar_code', $this->bar_code]);

        return $dataProvider;
    }
}
