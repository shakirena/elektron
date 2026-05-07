<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Finance;

/**
 * FinanceSearch represents the model behind the search form about `app\models\Finance`.
 */
class FinanceSearch extends Finance
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'from_kassa', 'to_kassa', 'id_user'], 'integer'],
            [['sum'], 'number'],
            [['note', 'datetime'], 'safe'],
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
        $query = Finance::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'from_kassa' => $this->from_kassa,
            'to_kassa' => $this->to_kassa,
            'sum' => $this->sum,
            'id_user' => $this->id_user,
            'datetime' => $this->datetime,
        ]);

        $query->andFilterWhere(['like', 'note', $this->note]);

        return $dataProvider;
    }
}
