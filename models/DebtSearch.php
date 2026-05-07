<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Debt;

/**
 * DebtSearch represents the model behind the search form about `app\models\Debt`.
 */
class DebtSearch extends Debt
{
    public $date_start;
    public $date_end;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'id_user', 'number', 'id_contr', 'discount'], 'integer'],
            [['debt', 'sum', 'sum_usd'], 'number'],
            [['datatime','date_start','date_end'], 'safe'],
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
        $query = Debt::find()->select("sum(debt) as debt, sum(sum) as sum,sum(sum_usd) as sum_usd,id_contr")->groupBy("id_contr")->orderBy("debt DESC");;

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
            'id_user' => $this->id_user,
            'debt' => $this->debt,
            'sum' => $this->sum,
            'number' => $this->number,
            'id_contr' => $this->id_contr,
            'discount' => $this->discount,
            'sum_usd' => $this->sum_usd,
        ]);
        $query->andFilterWhere(['between','datatime',$this->date_start,$this->date_end]);

        return $dataProvider;
    }
}
