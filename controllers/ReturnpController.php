<?php

namespace app\controllers;

use app\models\Arrival;
use app\models\Balance;
use app\models\Sell2;
use app\models\Dclient;
use Yii;
use app\models\Returnp;
use app\models\ReturnpSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Sell;

/**
 * ReturnpController implements the CRUD actions for Returnp model.
 */
class ReturnpController extends Controller
{
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
     * Lists all Returnp models.
     * @return mixed
     */
      public function actionIndex()
    {
        $searchModel = new ReturnpSearch();
        $searchModel->date_start = date('Y-m-d');//." 00:00:00";
		$searchModel->date_end = date('Y-m-d');//." 23:59:59";
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionReport($number)
    {
        $searchModel = new ReturnpSearch();
        $searchModel->number=$number;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionIndex1()
    {
        $searchModel = new ReturnpSearch();
        $searchModel->received=0;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index1', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Returnp model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Returnp model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Returnp();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Returnp model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Returnp model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Returnp model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Returnp the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Returnp::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    public function actionReturnReceived($number)
    {
        foreach (Returnp::find()->where(["number" => $number])->all() as $returnp) {
            $returnp->received=1;
            $returnp->save();

            $sell=Sell::find()->where(["id" => $returnp->id_sell])->one();
            if ($sell->quantity== $returnp->quantity)
            {
                $sell->returnp=1;
                $sell->save();
            }
            else {
                $sell->quantity=$sell->quantity-$returnp->quantity;
                $sell->returnp=1;
                $sell->save();
            }
            $arrival=new Arrival();
            $arrival->pricesell =0;
            $arrival->price =0;
            $arrival->id_product = $sell->id_product;
            $arrival->quantity =$returnp->quantity;
            $arrival->rest =$returnp->quantity;
            $arrival->sum = 0;
            $arrival->id_user = Yii::$app->user->identity->id_user;
            $arrival->received = 1;
            $arrival->id_contr = 0;
            if ($returnp->id_store) $arrival->id_store=$returnp->id_store;
           else  $arrival->id_store=Yii::$app->session->get("store");
            $arrival->datetime=date("Y-m-d");
            //print_r($arrival);
            $arrival->save();

            $money=$returnp->money;

        }
if ($money>0) {
        $model=Balance::find()->orderBy("id DESC")->one();
        $balance=new Balance();
        $balance->sum=$money;
        $balance->id_type=15;
        $balance->number=$returnp->number;
        $balance->id_user=Yii::$app->user->identity->id_user;
     //   $balance->number=$number;
        $balance->datetime=date("Y-m-d");
        $balance->current_sum = $model->current_sum-$money;
        $balance->id_store=  $returnp->id_store;
        $balance->id_client=  $returnp->id_client;
         print_r($balance);
        $balance->save();
        $url = "https://pushall.ru/api.php?type=self&id=44560&key=eb3f83211ddab0c1610bc6e1bf2e0b42&text= Malın qaytarılması təsdiq edildi (nömrə $number) &title=Malın qaytarılması";
        file_get_contents($url);}

    }
	
	
	
	public function actionCancel($number){
	
			 $sell=Sell2::find()->where(['sold' => 0,'id_user'=>Yii::$app->user->identity->id_user,'postponed'=>0])->one();
        if ($sell->number) $max=$sell->number;
	  else {
				$sell=Sell2::find()->where(['sold' => 0,'id_user'=>Yii::$app->user->identity->id_user])->orderBy("id DESC")->one();
			if ($sell->number)
				if ($sell->postponed>0) 	{   $max1 = Sell::find()->select('number')->max('number');
												$max2 = Sell2::find()->select('number')->max('number');
												if ($max1)
												if ($max1>$max2) $max = $max1 + 1;
												else $max = $max2 + 1;
													else $max = 1;
											}
				else $max=$sell->number;
			else 	{   $max1 = Sell::find()->select('number')->max('number');
												$max2 = Sell2::find()->select('number')->max('number');
												if ($max1)
												if ($max1>$max2) $max = $max1 + 1;
												else $max = $max2 + 1;
													else $max = 1;
											}


        }
			foreach (Returnp::find()->where(['number'=>$number])->all() as $row) {
							 $arrival=Arrival::find()
						->select('pricesell,price')
						->where(['id_product' => $id,'received'=>1])
						->orderBy([
							'datetime' => SORT_DESC
						])
						->one();
			
				
				
				
						$sell=new Sell2();
                       $sell->id_user=Yii::$app->user->identity->id_user;
                       $sell->id_product=$row->id_product;
                       $sell->quantity=$row->quantity;
                      if ($row->price) $sell->price=$row->price; else $sell->price=$arrival->pricesell;
						$sell->price_ar = $arrival->price;
                      
                       $sell->sold = 0;
                       $sell->sum=$sell->quantity*$sell->price;
                       $sell->datetime=date("Y-m-d H:i:s");
                       $sell->id_client = $row->id_client;
                       $sell->number=$max;
					  
                       $sell->id_user=Yii::$app->user->identity->id_user;
                      

                       $sell->id_store=$row->id_store;
                       $sell->earnings = $sell->earnings + ($sell->price - $sell->price_ar) * $sell->quantity;
                       $sell->postponed = 0;
                       $sell->debt =  $sell->sum;
                       $sell->save();
					   
					  
					   
					   	
						$arrival=Arrival::find()->where(['id_product' => $row->id_product, 'id_store' => $row->id_store])->andWhere("rest>0")->orderBy(['datetime' => SORT_DESC])->one();
						if ($arrival->id)
						{
						
						$arrival->rest=$arrival->rest-$row->quantity;
						$arrival->save();
						}
						
						
						 $row->delete();
					   
			}
			
	
		$barcode=new Dclient();
	$barcode->deleteAll(['number' => $row->number]);

}
}
