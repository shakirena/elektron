<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Sell;

/**
 * SellSearch represents the model behind the search form about `app\models\Sell`.
 */
class Sell2Search extends Sell2
{

    public $date_start;
    public $date_end;
    public $name_product;
    public $name_client;
    public $type;
    public $status;
    public $barcode;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'id_user', 'sold', 'number', 'flag','postponed','user_issue','id_client'], 'integer'],
            [[ 'price_ar', 'discount', 'earnings', 'returnp', 'debt','usd'], 'number'],
            [['datetime', 'date_issue','sn','date_start','date_end','type','id_product','id_store','barcode','name_product','id','name_client','status'], 'safe'],
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
        $query = Sell2::find()->orderBy("datetime DESC");

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
           'sort' => false,
            'pagination' => [

                'pageSize' =>'300',
            ],
        ]);
       $query->joinWith(['idProduct.idType']);

        $query->joinWith(['idProduct.barcodes']);

        $query->joinWith(['idClient']);
        $dataProvider->sort->attributes['type'] = [
            'asc' => ['type_product.name' => SORT_ASC],
            'desc' => ['type_product.name' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['name_product'] = [
            'asc' => ['product.name' => SORT_ASC],
            'desc' => ['product.name' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['name_client'] = [
            'asc' => ['client.fio' => SORT_ASC],
            'desc' => ['client.fio' => SORT_DESC],
        ];
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
            'sell.id' => $this->id,
            'id_user' => $this->id_user,
            'user_issue'=>$this->user_issue,
            'id_store' => $this->id_store,
            'id_product' => $this->id_product,
            'postponed' =>$this->postponed,
         //   'quantity' => $this->quantity,
         //   'price' => $this->price,
            'price_ar' => $this->price_ar,
            'discount' => $this->discount,
        //    'sum' => $this->sum,
            'sold' => $this->sold,
            'product.id_type' => $this->type,
          //  'sell.id_client' => $this->id_client,
            'number' => $this->number,
            'earnings' => $this->earnings,
            'flag' => $this->flag,
            'returnp' => $this->returnp,
            'debt' => $this->debt,
            'date_issue' => $this->date_issue,
            'status' => $this->status,
        ])
            ->andFilterWhere(['like', 'product.name', $this->name_product])
    ->andFilterWhere(['like', '`client`.`fio`', $this->name_client])
            ->andFilterWhere(['like', '`bar_code`.`name`', $this->barcode]);
        $query->andFilterWhere(['between','datetime',$this->date_start,$this->date_end]);

        $query->andFilterWhere(['like', 'sn', $this->sn]);
     //   $query->andFilterWhere(['like', 'sn', $this->g);
        return $dataProvider;
    }
}
