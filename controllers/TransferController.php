<?php

namespace app\controllers;

use app\models\Sell;
use Yii;
use app\models\Transfer;
use app\models\Arrival;
use app\models\TransferSearch;
use app\models\RestSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\TypeProduct;
use app\models\Product;
use app\models\Sell2;
use app\models\Barcodep;

use yii\filters\AccessControl;
/**
 * TransferController implements the CRUD actions for Transfer model.
 */
class TransferController extends Controller
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
			'access' => [
                'class' => AccessControl::className(),
                'only' => ['index','index2'],
                'rules' => [
                    [
                       
                        'allow' => true,
                        'roles' => ['@'],
                    ],
					
                ],
            ],
        ];
    }

    /**
     * Lists all Transfer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TransferSearch(['received' => 0,'id_user'=> Yii::$app->user->identity->id_user]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
 public function actionIndex2()
    {
        $searchModel = new TransferSearch(['received' => 0,'id_user'=> Yii::$app->user->identity->id_user]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index2', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * Displays a single Transfer model.
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
     * Creates a new Transfer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Transfer();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Transfer model.
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
     * Deletes an existing Transfer model.
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
     * Finds the Transfer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Transfer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Transfer::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionFind()
    {

        $searchModel = new RestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderAjax('find', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }
	public function actionCheckBarcode($barcode)
	{
		
		 $product=Barcodep::find()
            ->select("count(*) as id_product")
            ->where(["name"=>$barcode])
            ->one();
			
		if ($product->id_product<=1)  return 0;

		else return 1;
		
	}
	public function actionBarcodeShow($barcode)
	{
		$i=0;
	
		foreach (Barcodep::find()
            ->where(["name"=>$barcode])
            ->all() as  $product)
			{
				
				$products[$i]=$product->id_product;
				$i++;
			}
			
			$product=Product::find()->where(["in","id",$products])->all();
		return $this->renderAjax('show_barcode', [
                'products' => $product,
            ]);
        
	}
  public function actionFind2()
    {

        $searchModel = new RestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('find2', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }

    public function actionInsert($id, $quantity)
    {


        $model = new Transfer();
        $model->id_product = $id;
        $model->id_user = Yii::$app->user->identity->id_user;
        $model->quantity = $quantity;

        $model->received = 0;
        $model->save();




    }
	  public function actionInsertBarcode($barcode)
    {
       
	     $product=Barcodep::find()
            ->select("id_product")
            ->where(["name"=>$barcode])
            ->one();
		 $sell=Transfer::find()->where(["id_product"=>$product->id_product,'received'=>0])->one();
		if ($sell->id) {
			$sell->quantity=$sell->quantity+1;
			$sell->save();
			print_r($sell);
		}	
		else 
		{
		$model = new Transfer();
        $model->id_product =  $product->id_product;
        $model->id_user = Yii::$app->user->identity->id_user;
        $model->quantity = 1;

        $model->received = 0;
        $model->save();
		}
	   
	}
    public function actionDeleteAll()
    {
        $model = new Transfer();
        $model->deleteAll(['received' => 0,'id_user'=> Yii::$app->user->identity->id_user]);
    }
    public function actionUpdateQuantity($id, $quantity)
    {
        $model = $this->findModel($id);
        $model->quantity = $quantity;
        $model->save();

    }
    public function actionCheckRest($whence){
        $model = new Transfer();
		$flag=-1;
        foreach ($model->find()->where(['received'=>0,'id_user'=>Yii::$app->user->identity->id_user])->all() as $row) {
            $arrival = Arrival::find()
                ->select('sum(rest) as rest')
                ->where(['id_product' => $row->id_product,'id_store'=>$whence])
                ->groupBy('id_product')
                ->one();
            if ($arrival->rest>=$row->quantity)
            { $flag=1;}
            else
			{ $flag=Product::find()->where(["id"=>$row->id_product])->one()->name;break;}
               
        }

        if ($flag==1) return 0;
        else return $flag;
    }
    public function actionReceived($whence, $where)
    {
        $mes="";
        $max = Arrival::find()->select('number')->max('number');
        if ($max) $max = $max + 1;
        else $max = 1;

        $number = Transfer::find()->select('number')->max('number');
        if ($number) $number = $number + 1;
        else $number = 1;
      foreach (Transfer::find()->where(['received' => 0,'id_user' =>Yii::$app->user->identity->id_user ])->all() as $row) {
			$quantity=$row->quantity;
            $model=Arrival::find()
              ->select("pricesell,price,pricesell_min,trade_price,price_top,id_contr,pack,usd")
              ->where(["id_product"=>$row->id_product, 'transfer' => 0])
			  
              ->orderBy([
                  'id' => SORT_DESC
              ])
              ->one();
			 $rest = Arrival::find()->select("sum(rest) as rest")->where(["id_product"=>$row->id_product])
			  
              ->orderBy([
                  'id' => SORT_DESC
              ])
              ->one()->rest;
            if ($rest>=$row->quantity)
            {
				
				
						$arrival = new Arrival();
                        $arrival->id_product = $row->id_product;
                        $arrival->quantity = $row->quantity;
                        $arrival->rest = $row->quantity;
                        $arrival->price =$model->price;
                        $arrival->pricesell =$model->pricesell;
						$arrival->pricesell_min =$model->pricesell_min;
						$arrival->trade_price =$model->trade_price;
						$arrival->price_top  =$model->price_top ;
						$arrival->pack  =$model->pack ;
                        $arrival->usd=$model->usd;
                        $arrival->sum = $row->quantity*$model->price;
                        $arrival->id_user = Yii::$app->user->identity->id_user;
                        $arrival->id_store=$where;
                        $arrival->id_contr=$model->id_contr;
						$arrival->transfer=$number;
                        $arrival->datetime=date("Y-m-d H:i:s");
                        $arrival->received = 1;
                        $arrival->number=$max;
						$arrival->save();
						
               foreach (Arrival::find()->where(["id_product"=>$row->id_product,'id_store'=>$whence])
                    ->orderBy([
                        'datetime' => SORT_DESC
                    ])->all() as $arr)
                {
					  if ($row->quantity<=$arr->rest){
						 $arr->rest=$arr->rest-$row->quantity;
                        $arr->save();
                        break;
						
					}
					else {
                        $row->quantity=$row->quantity-$arr->rest;
                        $arr->rest=0;
                        $arr->save();
					}
				}
                  
                        

                      
                
                
				$row->quantity=$quantity;
                $row->received=1;
                $row->whence=$whence;
                $row->whered=$where;
                $row->date=date("Y-m-d H:i:s");
                $row->number=$number;
                $row->save();

            }
            else {
                $data[0]=1;
                $name=Product::find()->where(["id"=>$row->id_product])->one()->name;
                $data[1]=$name;
               return $name;
            }

        }

		return $number;

    }
	public function actionCancel($number)
	{
		$model = new Arrival();
        $model->deleteAll(["transfer"=>$number]);
		foreach (Transfer::find()->where(["number"=>$number])->all() as $row)
		{
			$ar=Arrival::find()->where(["id_product"=>$row->id_product,'id_store'=>$row->whence]) ->orderBy([
                        'datetime' => SORT_DESC
                    ])->one();
			$ar->rest=$ar->rest+$row->quantity;
			$ar->save();
			
		}
		
		
		$model = new Transfer();
		 $model->updateAll(['received' => 0,'id_user'=>Yii::$app->user->identity->id_user ], ["number"=>$number]);
   
		
		
	}
	public function actionReceivedPosponed($number)
	{
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
		 foreach (Transfer::find()->where(['number' => $number ])->all() as $row) {
			 
			  $arrival=Arrival::find()
					->select('pricesell,trade_price')
					->where(['id_product' => $row->id_product,'received'=>1])
					->orderBy([
						'datetime' => SORT_DESC
					])
					->one();
			 
			
					
					
					$sell = new Sell2();
						$sell->id_product = $row->id_product;
						$sell->id_user = Yii::$app->user->identity->id_user;
						$sell->user_issue = Yii::$app->user->identity->id_user;
						if ($arrival->pricesell) $price=$arrival->pricesell;
						else $price=0;
						if ($arrival->trade_price) $trade_price=$arrival->trade_price;
						else $trade_price=0;
						
						$sell->price =$price;
						$sell->sum = $row->quantity*$arrival->pricesell;
						$sell->quantity =$row->quantity;
						$sell->datetime=date("Y-m-d H:i:s");
						$sell->sold = 0;
						$sell->postponed=1;
						$sell->number=$max;
						$sell->usd =$trade_price;
						$sell->id_store=$row->whered;
						$sell->save();
						
						print_r($sell);


			
		 }

	}
 

    public function actionCheck($id){
        $model=TypeProduct::find()->where(["id_parent" => $id])->one();
        if ($model->id) { return 0;}
        else { return 1;}

    }
	
	public function actionPrint($number){
       
		$searchModel = new TransferSearch(['number' => $number]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        //$dataProvider=ArrivalSearch::find()->all();
        return $this->render('print', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }

    public function actionReport()
    {


        $searchModel = new TransferSearch(['received' => '1']);
       $searchModel->date_start = date('Y-m-d');
       $searchModel->date_end = date('Y-m-d');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        //$dataProvider=ArrivalSearch::find()->all();
        return $this->render('report', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

  

}
