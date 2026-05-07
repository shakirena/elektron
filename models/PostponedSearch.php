<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Postponed;

/**
 * PostponedSearch represents the model behind the search form about `app\models\Postponed`.
 */
class PostponedSearch extends Postponed
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'id_product', 'quantity', 'id_store', 'id_user', 'number', 'date', 'received','id_master'], 'integer'],
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
        $query = Postponed::find()->orderBy("date DESC");

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [

                'pageSize' =>'100',
            ],
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
            'id_product' => $this->id_product,
            'quantity' => $this->quantity,
            'id_store' => $this->id_store,
            'id_user' => $this->id_user,
            'number' => $this->number,
            'date' => $this->date,
            'received' => $this->received,
            'id_master' => $this->id_master,
        ]);

        return $dataProvider;
    }
}
