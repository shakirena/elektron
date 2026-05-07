<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Arrival;

/**
 * ArrivalSearch represents the model behind the search form about `app\models\Arrival`.
 */
class ArrivalIndexSearch extends Arrival
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
            [['id', 'id_user', 'id_store', 'received', 'id_contr', 'number', 'returnp', 'rest','postponed'], 'integer'],

            [['datetime','date_start','date_end', 'id_product','type','name_product'], 'safe'],
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
        $query = Arrival::find()->orderBy("datetime DESC");;

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [

                'pageSize' =>'100',
            ],
            'sort' => false,
        ]);

        $query->joinWith(['idProduct.idType']);


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
            'id' => $this->id,
          
            'id_user' => $this->id_user,
            'type_product.name' => $this->type,
            'id_store' => $this->id_store,
            'received' => $this->received,
            'id_contr' => $this->id_contr,
            'number' => $this->number,
            'discount' => $this->discount,
            'returnp' => $this->returnp,
            'rest' => $this->rest,
            'postponed' =>$this->postponed

        ])
            ->andFilterWhere(['like', 'product.name', $this->id_product]);
        $query->andFilterWhere(['between','datetime',$this->date_start,$this->date_end]);
        return $dataProvider;
    }
}
