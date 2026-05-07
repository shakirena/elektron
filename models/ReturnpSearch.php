<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Returnp;

/**
 * ReturnpSearch represents the model behind the search form about `app\models\Returnp`.
 */
class ReturnpSearch extends Returnp
{
    public $date_start;
    public $date_end;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id',  'id_user','id_client','received','id_store','number'], 'integer'],
            [['reason', 'id_product','data','date_start','date_end'], 'safe'],
            [['quantity'], 'number'],
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
        $query = Returnp::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->joinWith(['idProduct']);


        $dataProvider->sort->attributes['id_product'] = [
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
            'id_user' => $this->id_user,
            'id_store' => $this->id_store,
            'id_client' => $this->id_client,
            'received' => $this->received,
            'quantity' => $this->quantity,
            'number' => $this->number,
        ])
            ->andFilterWhere(['like', 'product.name', $this->id_product]);

        $query->andFilterWhere(['like', 'reason', $this->reason]);

        $query->andFilterWhere(['between','data',$this->date_start,$this->date_end]);
        return $dataProvider;
    }
}
