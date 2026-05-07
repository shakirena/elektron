<?php

namespace app\controllers;

use app\models\Arrival;
use app\models\Contractor;
use app\models\Sell;
use app\models\Store;
use app\models\Costs;
use app\models\Kassa;
use app\models\Client;
use Yii;
use app\models\Discount;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\helpers\Url;
use app\models\ProductSearch;
use yii\helpers\ArrayHelper;
use app\models\Product;
use app\models\ClientSearch;
use app\models\MoveSearch;
use kartik\select2\Select2;
use app\models\TypeProduct;
use app\models\TypeCosts;
use app\models\Debt;
use app\models\Dclient;
class MoveController extends Controller
{
   // public $layout = 'start';
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }


    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
    public function actionContr()
    {
        return $this->render('contr');
    }
	
	public function actionProduct()
    {
        return $this->render('product');
    }
    public function actionFind()
    {

        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderAjax('find', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }
	
	
		
	public function actionItogReceived($date1,$date2)
    {
		
		$date11=$date1;
        $date22=$date2;
		
		$date1.=' 00:00:00';
        $date2.=' 23:59:59';
		$pos=Costs::find()->select("sum(sum) as sum")->andWhere("datetime>='$date1' AND datetime<='$date2' AND  id_type= 3 and id_kassa!=1")->one()->sum;
		$bonus=Costs::find()->select("sum(sum) as sum")->andWhere("datetime>='$date1' AND datetime<='$date2' AND  id_type= 3 and id_kassa=1")->one()->sum;
		
		$sum=Sell::find()->select("sum(sum) as sum")->andWhere("datetime>='$date1' AND datetime<='$date2'")->one()->sum;
		$oplata=$sum-Dclient::find()->select("sum(debt) as debt")->andWhere("datetime>='$date1' AND datetime<='$date2' AND debt>0 and number IS NOT NULL")->one()->debt;
		$dolg=Costs::find()->select("sum(sum) as sum")->andWhere("datetime>='$date1' AND datetime<='$date2' AND id_type=1 and id_kassa=4")->one()->sum;
		$return=Dclient::find()->select("sum(sum) as sum")->andWhere("datetime>='$date1' AND datetime<='$date2' AND  sum<0")->one()->sum;
		
		$dclient=Dclient::find()->select("sum(debt) as debt")->andWhere("datetime>='$date1' AND datetime<='$date2' AND debt>0")->one()->debt;
		$debt=Debt::find()->select("sum(debt) as debt")->andWhere("datatime>='$date1' AND datatime<='$date2' AND debt<0 AND number IS NULL")->one()->debt;
		$costs=Costs::find()->joinWith(["idType"])->select("sum(sum) as sum")->andWhere("datetime>='$date1' AND datetime<='$date2' AND type_costs.type=0 and id_kassa!=1")->one()->sum;
		$prixod=Costs::find()->joinWith(["idType"])->select("sum(sum) as sum")->andWhere("datetime>='$date1' AND datetime<='$date2' AND type_costs.type = 1 and id_kassa!=1")->one()->sum;
		
		$discount = Discount::find()->select('sum(sum) as sum')->andWhere("datetime>='$date1' AND datetime<='$date2'")->one()->sum;
		$kassa = Costs::find()->select("sum(sum) as sum")->where(['id_kassa' => 1 ])->one()->sum;
		$name = Kassa::find()->where(['id' => 1 ])->one()->name;
     
	
        
        return $this->render('itog', [
            'sum' => $sum,
			'oplata'=>$oplata,
            'dolg' => $dolg,
			'return' => $return,
			'date1'=>$date11,
			'date2'=>$date22,
			'costs'=>$costs,
			'dclient'=>$dclient,
			'prixod'=>$prixod,
			'debt'=>$debt,
			'discount' => $discount,
			'kassa' => $kassa,
			'name' => $name,
			"pos" => $pos,
			"bonus" => $bonus
        ]);

    }

    
	public function actionItog()
    {

		if (Yii::$app->request->get('prixod')) {
		 if (!$id = TypeCosts::find()->where(['type'=>1])->one()->id) 
		 { 
			$model = new TypeCosts();
			$model->type = 1;
			$model->name="Medaxil";
			$model->save();
			$id = $model->id;
		 }
			$costs = new Costs();
			$costs->id_type = $id;
			$costs->sum = Yii::$app->request->get('prixod');
			$costs->datetime = date("Y-m-d H:i:s");
			$costs->save();
			
		}
		if (Yii::$app->request->get('date1'))
		{
			 $this->redirect(['itog-received','date1'=>Yii::$app->request->get('date1'),'date2'=>Yii::$app->request->get('date2')]);
			
		
		}
		
        return $this->render('itog', [
           
        ]);

    }
	public function actionMoveReport()
	{
		$searchModel = new MoveSearch();
		$searchModel->date_start = date('Y-m-d');//." 00:00:00";
		$searchModel->date_end = date('Y-m-d');//." 23:59:59";
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
   
         return $this->render('move/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
	
	}
    public function actionReport1($id,$date1,$date2,$type)
    {
	   $date1.=' 00:00:00';
        $date2.=' 23:59:59';
		
	if ($type==1) {
				$model=Debt::find()->andWhere("datatime>='$date1' AND datatime<='$date2'")->where(["id_contr" =>$id])->orderBy("datatime ASC")->all();
				$debt=Debt::find()->select("sum(debt) as sum,sum(sum_usd) as sum_usd")->where(["id_contr" =>$id])->one();
				$current=Debt::find()->select("sum(debt) as sum,sum(sum_usd) as sum_usd")->where(["id_contr" =>$id])->andWhere("datatime<'$date1'")->one();
				$client=Contractor::find()->where(["id" =>$id])->one()->name;
				return $this->render('report_contre1', [
					'model' => $model,
					'debt'=> $debt,
					'current'=>$current,
					'contractor' => $client
				]);
	}
     else 
	   	$model=Debt::find()->andWhere("datatime>='$date1' AND datatime<='$date2'")->where(["id_contr" =>$id])->orderBy("datatime ASC")->all();
				$debt=Debt::find()->select("sum(debt) as sum,sum(sum_usd) as sum_usd")->where(["id_contr" =>$id])->one();
				$current=Debt::find()->select("sum(debt) as sum,sum(sum_usd) as sum_usd")->where(["id_contr" =>$id])->andWhere("datatime<'$date1'")->one();
				$client=Contractor::find()->where(["id" =>$id])->one()->name;
				return $this->render('report_contre', [
					'model' => $model,
					'debt'=> $debt,
					'current'=>$current,
					'contractor' => $client
				]);

    }

    public function actionClient()
    {
        $searchModel = new ClientSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('client', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionClientShow()
    {
        $searchModel = new ClientSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderAjax('client1', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionReportClient($id,$type,$date1,$date2)
    {
        $date1.=' 00:00:00';
        $date2.=' 23:59:59';
        $client=Client::find()->where(["id_client" =>$id])->one()->fio;
		$model=Dclient::find()->where(["id_client" =>$id])->andWhere("datetime>='$date1' AND datetime<='$date2'")->orderBy("datetime ASC")->all();
		$debt=Dclient::find()->select("sum(debt) as sum,sum(usd) as usd")->where(["id_client" =>$id])->one();
		$current=Dclient::find()->select("sum(debt) as sum")->where(["id_client" =>$id])->andWhere("datetime<'$date1'")->one();
		if ($current) $current=$current->sum;
			else $current=0;

		$prixod = Costs::find()->select("sum(sum) as sum")->where(['id_client' => $id,'id_kassa' =>1,'id_type' =>4])->andWhere("datetime<'$date1'")->one()->sum;
		$rasxod = Costs::find()->select("sum(sum) as sum")->where(['id_client' => $id,'id_kassa' =>1,'id_type' =>2])->andWhere("datetime<'$date1'")->one()->sum;
		$bonus_current = round($prixod - $rasxod,2);
        if ($type==1) {
						return $this->render('report_client', [
							'model' => $model,
							'debt'=> $debt,
							'bonus'=> $bonus,
							'current'=>$current,
							'bonus_current' => $bonus_current,
							'client' => $client
		]);
					
            }
         if ($type==2) {
						return $this->render('report_client2', [
							'model' => $model,
							'debt'=> $debt,
							'bonus'=> $bonus,
							'current'=>$current,
							'bonus_current' => $bonus_current,
							'client' => $client
		 ]);
       }

    }
  




    public function actionCheck($id){
        $model=TypeProduct::find()->where(["id_parent" => $id])->one();
        if ($model->id) { return 0;}
        else { return 1;}

    }
}
















































































































































































































































































































