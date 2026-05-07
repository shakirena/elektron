<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Transfer;

/**
 * TransferSearch represents the model behind the search form about `app\models\Transfer`.
 */
class TransferSearch extends Transfer
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
            [['id',  'quantity', 'id_user', 'whence', 'whered', 'received','number'], 'integer'],
            [['date','id_product','date_start','date_end','type','number','name_product'], 'safe'],
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
        $query = Transfer::find()->orderBy("id DESC");

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
        ]);
        $query->joinWith(['idProduct']);
        $query->joinWith(['idProduct.idType']);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'number' => $this->number,
            'quantity' => $this->quantity,
            'id_user' => $this->id_user,
            'whence' => $this->whence,
            'whered' => $this->whered,
            'product.id_type' => $this->type,
            'id_product' => $this->id_product,
            'received' => $this->received,
        ]) ->andFilterWhere(['like', 'product.name', $this->name_product]);
        $query->andFilterWhere(['between','date',$this->date_start,$this->date_end]);


        return $dataProvider;
    }
}
