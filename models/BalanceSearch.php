<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Balance;

/**
 * BalanceSearch represents the model behind the search form about `app\models\Balance`.
 */
class BalanceSearch extends Balance
{
    public $date_start;
    public $date_end;
    public $type;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'id_user','id_store','type'], 'integer'],
            [['datetime', 'date_start','date_end','number'], 'safe'],//'note',
            [[ 'current_sum', 'id_type'], 'number'],//'sum',
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
        $query = Balance::find()->orderBy(" `balance`.`datetime` DESC, type_balance.type ASC,`balance`.`id_type` DESC,");

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->joinWith(['idType']);
        $dataProvider->setPagination(['pageSize'=>300]);
      //  $query->joinWith(['idType']);
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
            'id_user' => $this->id_user,
            'id_store' => $this->id_store,
            'number' => $this->number,
            'datetime' => $this->datetime,
          //  'sum' => $this->sum,
            'current_sum' => $this->current_sum,
            'id_type' => $this->id_type,
           // 'number' => $this->number,
            'type_balance.type' => $this->type,
        ]);

      //  $query->andFilterWhere(['like', 'note', $this->note]);
        $query->andFilterWhere(['between','datetime',$this->date_start,$this->date_end]);

        return $dataProvider;
    }
}
