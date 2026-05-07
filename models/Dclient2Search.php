<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Dclient;

/**
 * DclientSearch represents the model behind the search form about `app\models\Dclient`.
 */
class Dclient2Search extends Dclient
{
    public $date_start;
    public $date_end;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'id_client', 'number'], 'integer'],
            [['debt', 'sum','usd'], 'number'],
            [['datetime', 'date_return','date_start','date_end'], 'safe'],
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
         $query = Dclient::find()->where("debt>0")->orderBy("debt DESC");

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
        if ( $this->date_start)  $this->date_start=$this->date_start." 00:00:00";
        if ($this->date_end)  $this->date_end=$this->date_end." 23:59:59";
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'id_client' => $this->id_client,
            'number' => $this->number,
            'debt' => $this->debt,
           // 'datetime' > $this->datetime." 00:00:00",
            'sum' => $this->sum,
            'date_return' => $this->date_return,
        ]);
        $query->andFilterWhere(['between','datetime',$this->date_start,$this->date_end]);
        return $dataProvider;
    }
}
