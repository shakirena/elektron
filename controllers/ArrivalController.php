<?php

namespace app\controllers;

use app\models\Debt;
use app\models\Exchange;
use app\models\Postponed;
use app\models\Product;
use app\models\Barcode;
use app\models\ProductSearch;
use app\models\Sell;
use app\models\Balance;
use Yii;
use yii\bootstrap\Html;
use app\models\Arrival;
use app\models\ArrivalSearch;
use app\models\ArrivalIndexSearch;
use app\models\RestSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use kartik\mpdf\Pdf;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\TypeProduct;
use app\models\ReturnArrival;
use app\models\Barcodep;
use app\models\Image;
use app\models\Password;
use app\models\UploadForm;
use yii\web\UploadedFile;


use yii\filters\AccessControl;
//use app\models\Move;
/**
 * ArrivalController implements the CRUD actions for Arrival model.
 */
class ArrivalController extends Controller
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
                'only' => ['index','index2','find','find2','report','dialog','postponed'],
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
     * Lists all Arrival models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ArrivalIndexSearch(['received' => 0,'id_user'=> Yii::$app->user->identity->id_user,'postponed'=>0 ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $sum=Arrival::find()->select('sum(sum) as sum,sum(usd*quantity) as usd,datetime,id_store,id_contr')->where(['received'=>'0','id_user'=> Yii::$app->user->identity->id_user,'postponed'=>0])->one();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'sum'=> $sum->sum,
            'usd' => $sum->usd,
			'model'=>$sum

        ]);
    }
	public function actionIndex2()
    {
        $searchModel = new ArrivalIndexSearch(['received' => 0,'id_user'=> Yii::$app->user->identity->id_user,'postponed'=>0 ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $sum=Arrival::find()->select('sum(sum) as sum,sum(usd*quantity) as usd,datetime,id_store,id_contr')->where(['received'=>'0','id_user'=> Yii::$app->user->identity->id_user,'postponed'=>0])->groupBy('number')->one();
        return $this->render('index2', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'sum'=> $sum->sum,
            'usd' => $sum->usd,
			'model'=>$sum

        ]);
    }
    /**
     * Displays a single Arrival model.
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
     * Creates a new Arrival model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Arrival();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
	public function actionCreateCheck()
    {
       
      
			$b=Yii::$app->request->post("Barcodep");
			 foreach ( $b['name'] as $barcode)
                {
                   $barcod=Barcodep::find()->where(["name"=> $barcode])->one();
					if ($barcod->id)  return 1;

				}
			
		
	}
	public function actionCreateProduct()
    {
        $model = new Product();
		$image=new UploadForm();
        if (Yii::$app->request->post()) {
			$b=Yii::$app->request->post("Barcodep");
		
            $product = Yii::$app->request->post("Product");

            $model->name = $product['name'];
            $model->id_type = $product['id_type'];
			$model->boxing = $product['boxing'];


            if ($model->save()) { //&& $model->save()
               if ( $b){

                foreach ( $b['name'] as $barcode)
                {
                    $bar_code=new Barcodep();
                    $bar_code->id_product=$model->id;
                    $bar_code->name=$barcode;
                    $bar_code->save();

            }}
			
			
				if ($image->imageFiles = UploadedFile::getInstances($image, 'imageFiles')){
                if ($image->upload($model->id)) {
                  
                    $image_tratment=new \app\models\Image();
                    $image_tratment->id_tre=$model->id;
                    $image_tratment->path="uploads/".$model->id."/".$image->imageFiles[0]->name;
                    $thumbnFile="thumb/".$model->id."/".$image->imageFiles[0]->name;
					$image_tratment->thumb=$thumbnFile;
			Image::thumbnail( Url::to([$image_tratment->path],true), 50, 50)->save($_SERVER['DOCUMENT_ROOT'] .'/web/'.$thumbnFile, ['quality' => 80]);
                    $image_tratment->save();
                    // return;
                }}
			
			
				$arrival = new Arrival();
				$arrival->pricesell =0;
				$arrival->price =0;
				
			
				$arrival->id_product = $model->id;
				$arrival->quantity = 1;
				$arrival->rest =1;
				
				$arrival->id_user = Yii::$app->user->identity->id_user;
				$arrival->received = 0;
				$arrival->datetime = date("Y-m-d H:i:s");
				$arrival->save();
               return $this->redirect(['index']);
            }

        }
        else {

            $model->id_type=Product::find()->orderBy("id DESC")->one()->id_type;
            $barcode=new Barcodep();
            return $this->renderAjax('create-product', [
                'model' => $model,
                'barcode' =>$barcode,
				'image' => $image,
            ]);
        }

    }
    /**
     * Updates an existing Arrival model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $barcode=new Barcodep();
         $product=Product::find()->where(["id" => $model->id_product])->one();
        if (Yii::$app->request->post()) {
			 $product_post = Yii::$app->request->post("Product");
            $arrival = Yii::$app->request->post("Arrival");
			$model2 = new Arrival();
            $model2->updateAll(['pricesell'=>$arrival["pricesell"],'pack'=>$arrival["pack"],'price_top'=>$arrival["price_top"],'trade_price'=>$arrival["trade_price"],'pricesell_min'=>$arrival["pricesell_min"]],["id_product"=>$model->id_product]);
		
			/*
            $model2 = new Arrival();
            $model2->updateAll(['rest'=>0],["id_product"=>$model->id_product,"id_store"=>$model->id_store]);
			*/
			/*
            $model=Arrival::find()->where(["id_product"=>$model->id_product])->orderBy("datetime DESC")->one();

           
            $model->price= $arrival["price"];
            $model->pricesell= $arrival["pricesell"];
            $model->usd= $arrival["usd"];
           // $model->rest= $arrival["rest"];
			$model->polka= $arrival["polka"];
			$model->pack= $arrival["pack"];
			$model->price_top= $arrival["price_top"];
			$model->trade_price= $arrival["trade_price"];
			$model->pricesell_min= $arrival["pricesell_min"];
            $model->save();
			*/
            $product->name=$product_post["name"];
			$product->id_type=$product_post["id_type"];
            $product->save();

            $barcode->deleteAll(['id_product' => $model->id_product]);
            $b=Yii::$app->request->post("Barcodep");
            foreach ( $b['name'] as $barcode)
            {
                $bar_code=new Barcodep();
                $bar_code->id_product=$model->id_product;
                $bar_code->name=$barcode;
                $bar_code->save();

            };

            $searchModel = new RestSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

           	return $this->redirect(['edit']);
        } else {
            $barcode=new Barcodep();
            return $this->render('update', [
                'model' => $model,
                'product' =>$product,
                'barcode' =>$barcode
            ]);
        }
    }

    /**
     * Deletes an existing Arrival model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $arrival=Arrival::find()->where(["id"=>$id])->one();
        $product=Product::find()->where(["id"=>$arrival->id_product])->one();
        $product->delete();
       // $this->findModel($id)->delete();
        $searchModel = new RestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('edit', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }
    public function actionDelete2($id)
    {

         $this->findModel($id)->delete();
        $searchModel = new ArrivalSearch(['received' => 0,'id_user'=> Yii::$app->user->identity->id_user,'postponed'=>0 ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $sum=Arrival::find()->select('sum(sum) as sum,sum(usd*quantity) as usd')->where(['received'=>'0','id_user'=> Yii::$app->user->identity->id_user,'postponed'=>0])->groupBy('number')->one();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'sum'=> $sum->sum,
            'usd' => $sum->usd

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
    public function actionDeleteAll()
    {
        $model = new Arrival();
        $model->deleteAll(['received' => 0,'id_user'=> Yii::$app->user->identity->id_user,'postponed'=>0]);
    }
    /**
     * Finds the Arrival model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Arrival the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Arrival::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

	public function  actionFindProduct()
	{
		foreach (Barcodep::find()->all() as $arrival) 
		{
			$product = Product::find()->where(['id' => $arrival->id_product ])->one();
			if (!$product) echo $arrival->id_product."<br>;";
		}
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
	public function actionFind2()
    {

        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('find2', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }
public function actionGetPricesell($id)
{
    $arrival=Arrival::find()
        ->select('pricesell,price')
        ->where(['id_product' => $id])
        ->orderBy([
            'id' => SORT_DESC
        ])
        ->one();
    if ($arrival->pricesell) $pricesell=$arrival->pricesell;
    else $pricesell=0;

    return $pricesell;
}
public function actionGetTradePrice($id)
{
    $arrival=Arrival::find()
        ->select('trade_price')
        ->where(['id_product' => $id])
        ->orderBy([
            'id' => SORT_DESC
        ])
        ->one();
    if ($arrival->trade_price) $pricesell=$arrival->trade_price;
    else $pricesell=0;

    return $pricesell;
}
public function actionGetBoxing($id)
    {
        $arrival=Arrival::find()
            ->select('boxing')
            ->where(['id_product' => $id])
            ->orderBy([
                'id' => SORT_DESC
            ])
            ->one();
        if ($arrival->boxing) $pricesell=$arrival->boxing;
        else $pricesell=1;
        return $pricesell;
    }

public function actionGetPricesellMin($id)
{
    $arrival=Arrival::find()
        ->select('pricesell_min')
        ->where(['id_product' => $id])
        ->orderBy([
            'id' => SORT_DESC
        ])
        ->one();
    if ($arrival->pricesell_min) $pricesell=$arrival->pricesell_min;
    else $pricesell=0;

    return $pricesell;
}
    public function actionGetPrice($id)
    {
        $arrival=Arrival::find()
            ->select('pricesell,price')
            ->where(['id_product' => $id])
            ->orderBy([
                'id' => SORT_DESC
            ])
            ->one();
        if ($arrival->price) $price=$arrival->price;
        else $price=0;

        return $price;
    }
	 public function actionGetUsdsell($id)
    {
        $arrival=Arrival::find()
            ->select('usdsell')
            ->where(['id_product' => $id])
            ->orderBy([
                'id' => SORT_DESC
            ])
            ->one();
        if ($arrival->usdsell) $price=$arrival->usdsell;
        else $price=0;

        return $price;
    }
	    public function actionGetUsd($id)
		{
			$arrival=Arrival::find()
				->select('usd')
				->where(['id_product' => $id])
				->orderBy([
					'id' => SORT_DESC
				])
				->one();
			if ($arrival->usd) $usd=$arrival->usd;
			else $usd=0;

			return $usd;
		}
		public function actionGetPolka($id)
		{
			$arrival=Arrival::find()
				->select('polka')
				->where(['id_product' => $id])
				->orderBy([
					'id' => SORT_DESC
				])
				->one();
			if ($arrival->polka) $polka=$arrival->polka;
			else $polka=0;

			return $polka;
		}
		public function actionGetPriceTop($id)
		{
			$arrival=Arrival::find()
				->select('price_top')
				->where(['id_product' => $id])
				->orderBy([
					'id' => SORT_DESC
				])
				->one();
			if ($arrival->price_top) $price_top=$arrival->price_top;
			else $price_top=0;

			return $price_top;
		}
		
		public function actionGetPack($id)
		{
			$arrival=Arrival::find()
				->select('pack')
				->where(['id_product' => $id])
				->orderBy([
					'id' => SORT_DESC
				])
				->one();
			if ($arrival->pack) $pack=$arrival->pack;
			else $pack=0;

			return $pack;
		}

    public function actionGetProcent($id)
{
    $arrival=Arrival::find()
        ->select('pricesell,price')
        ->where(['id_product' => $id])
        ->orderBy([
            'id' => SORT_DESC
        ])
        ->one();
    if ($proc=($arrival->pricesell-$arrival->price)*100/$arrival->price) 

    return round($proc,1);
	else return 0;
}
    public function actionGetProcentEdit($price,$pricesell)
    {

        $proc=($pricesell-$price)*100/$price;

        return round($proc,1);
    }

    public function actionProcentEdit($procent,$price)
    {

        $pricesell=round($price+$procent*$price/100,2);

        return $pricesell;
    }
	 public function actionGetPachkaPrice($pricesell,$pack)
    {

        $price=round($pricesell/$pack,3);

        return $price;
    }
    public function actionInsert( $quantity,$price,$id,$pricesell,$proc,$pack,$pricetop,$polka)
    {

		
            $arrival=Arrival::find()
                ->select('pricesell,price,polka,pack,price_top,pricesell_min,trade_price')
                ->where(['id_product' => $id])
                ->orderBy([
                    'datetime' => SORT_DESC
                ])
                ->one();
				 $trade_price=Arrival::find()
                ->select('trade_price')
                ->where(['id_product' => $id])->andWhere("trade_price>0")
                ->orderBy([
                    'datetime' => SORT_DESC
                ])
                ->one()->trade_price;
 	if ($trade_price) $trade_price=$trade_price;
            else $trade_price=0;
			
			if ($arrival->pricesell_min) $pricesell_min=$arrival->pricesell_min;
            else $pricesell_min=0;
			
        $model = new Arrival();
        $model->id_product = $id;
        $model->quantity = $quantity;
        $model->rest = $quantity;
        $model->price =$price;
		if ($arrival->price) $model->discount=round(($pricesell-$model->price)*100/$model->price,2);	
        $model->pricesell =$pricesell;
		$model->pack =$pack;
		$model->polka=$polka;
		$model->price_top =$price_top;
		$model->trade_price =$trade_price;
		$model->pricesell_min =$pricesell_min;
        $model->sum = $quantity*$price;
        $model->id_user = Yii::$app->user->identity->id_user;
        $model->received = 0;
		$model->datetime = date("Y-m-d H:i:s");
        $model->save();


    }

   
    public function actionInsertBarcode($barcode)
    {
        $product=Barcodep::find()
            ->select("id_product")
            ->where(["name"=>$barcode])
            ->one();
        if ($product->id_product) {
            $arrival=Arrival::find()
                ->select('pricesell,usd,price,polka,pack,price_top')
                ->where(['id_product' => $product->id_product])
                ->orderBy([
                    'datetime' => SORT_DESC
                ])
                ->one();
            if ($arrival->pricesell) $pricesell=$arrival->pricesell;
            else $pricesell=0;
			 if ($arrival->price) $price=$arrival->price;
            else $price=0;
			 if ($arrival->usd) $usd=$arrival->usd;
            else $usd=0;
			
			if ($arrival->polka) $polka=$arrival->polka;
            else $polka=0;
		
			if ($arrival->pack) $pack=$arrival->pack;
            else $pack=0;
			if ($arrival->price_top) $price_top=$arrival->price_top;
            else $price_top=0;
			if ($arrival->trade_price) $trade_price=$arrival->trade_price;
            else $trade_price=0;
			if ($arrival->pricesell_min) $pricesell_min=$arrival->pricesell_min;
            else $pricesell_min=0;
			
            $arrival = new Arrival();
            $arrival->pricesell =$pricesell;
			$arrival->price =$price;
			$arrival->polka =$polka;
		
			$arrival->pack =$pack;
			$arrival->price_top =$price_top;
			$arrival->trade_price =$trade_price;
			$arrival->pricesell_min =$pricesell_min;
            $arrival->id_product = $product->id_product;
            $arrival->quantity = 1;
            $arrival->rest =1;
            $arrival->sum = $price;
            $arrival->id_user = Yii::$app->user->identity->id_user;
            $arrival->received = 0;
			$arrival->datetime = date("Y-m-d H:i:s");
            $arrival->save();
			var_dump($arrival);
        }

    }

    public function actionUpdateQuantity($id, $quantity)
    {
        $model = $this->findModel($id);
        $model->quantity = $quantity;
        $model->rest = $quantity;
        $model->sum = $quantity * $model->price;
        $model->save();
        return  $model->sum ;
      

    }

    public function actionUpdatePrice($id, $price)
    {
        $model = $this->findModel($id);
        $model->price = $price;
        $model->discount=round(($model->pricesell-$price)*100/$price,2);
        $model->sum = $price * $model->quantity;
        $model->save();
        return  $model->sum ;

    }
	public function actionBarcodeInsert()
    {
		foreach (Arrival::find()->where(['received' => 0,'id_user' =>Yii::$app->user->identity->id_user,'postponed'=>0 ])->all() as $arrival)
		{
		
				$model = new Barcode();
				$model->id_product = $arrival->id_product;
				$model->price =0;
				$model->count = $arrival->quantity;
				$model->save();
			
			
		}
    }
    public function actionUpdateUsd($id, $price)
    {
        $model = $this->findModel($id);
        $model->usd = $price;
        $rate=Exchange::find()->one();
       $azn=$price * $rate->rates;
        $model->sum = $azn * $model->quantity;
       $model->price=$azn;
        $model->save();

    }
    public function actionUpdatePricesell($id, $price)
    {
        $model = $this->findModel($id);
        $model->pricesell = $price;
        $model->discount=round(($price-$model->price)*100/$model->price,2);
        $model->save();

    }
	public function actionUpdateUsdSell($id, $price)
    {
        $model = $this->findModel($id);
		$model->usdsell = $price;
		$rate=Exchange::find()->one();
        $model->pricesell=$price * $rate->rates;
        $model->discount=round((   $model->pricesell-$model->price)*100/$model->price,2);
        $model->save();

    }

    public function actionUpdateProc($id, $proc)
    {
        $model = $this->findModel($id);
        $model->pricesell = round($model->price+$proc*$model->price/100,2);
        $model->discount=$proc;
        $model->save();

    }
	
	public function actionCancel($number)
	{

		
	
		
	
		$debt=Debt::find()->where(['number' => $number ])->one();
		if ($debt->id) $debt->delete();
		
		$model2 = new Arrival();
        $model2->updateAll(['received'=>0],['number' => $number]);



	}	
/*public function actionTest(){
$i=0;
foreach (ReturnArrival::find()->all() as $row) {
		$arrival=Arrival::find()->where("id_product=$row->id_product")->one();
		if ($arrival->id)
		{
		
		$arrival->save();
		}
		else {$i++;echo $row->idProduct->name."<br>";}
		echo $i;
	}

}

public function actionTest1(){

foreach (Arrival::find()->andWhere("transfer!=0")->all() as $row) {
	
	$arrival=Arrival::find()->where(["id_product"=>$row->id_product])->andWhere("transfer=0")->orderBy([
                  'id' => SORT_DESC
              ])->one();
	$row->trade_price = $arrival->trade_price;
	$row->price_top = $arrival->price_top;
	$row->save();
    
	}

}*/
    public function actionReceived($store,$contractor, $date)
    {

        $max = Arrival::find()->select('number')->max('number');
        if ($max) $max = $max + 1;
        else $max = 1;

        $row=Arrival::find()->select("sum(sum) as sum,datetime")->where(['received' => 0,'id_user' =>Yii::$app->user->identity->id_user,'postponed'=>0 ])->one();
        if( $row->sum>0) {
            $debt = new Debt();
            $debt->id_user = Yii::$app->user->identity->id_user;
			$debt->debt =$row->sum;
            $debt->datatime = $date." ".date("H:i:s");;
            $debt->number = $max;
            $debt->id_contr = $contractor;
            $debt->discount = 0;
            $debt->save();
        }
		
		/*foreach (Arrival::find()->where(["received"=>0, "id_user"=>Yii::$app->user->identity->id_user,'postponed'=>0])->all() as $arrival)
		{
			$move=new Move();
			$move->id_product=$arrival->id_product;
			$move->quantity=$arrival->quantity;
			$move->price=$arrival->price;
			$move->sum=$arrival->quantity*$arrival->price;
			$move->type=1;
			$move->datetime=date("Y-m-d H:i:s");
			$move->id_num=$arrival->id;
			$move->save();
		
		}
		*/
		
       if ($row->datetime) $date=$row->datetime;
	   else $date=date("Y-m-d H:i:s");
        $model = new Arrival();
        $model->updateAll(['id_store' => $store,'id_contr'=>$contractor, 'datetime' => $date, 'received' => 1, 'number' => $max], ['received' => 0,'id_user' =>Yii::$app->user->identity->id_user,'postponed'=>0]);
		Yii::$app->session->remove('contractor');
		
    }
	public function actionPhoto($id)
	{
		
		$path=Image::find()->where(["id_tre" =>  $id])->one();
		
		return  Html::img(Yii::getAlias('@web')."/". $path->path);

	}
    public function actionMpdf()
    {

        $searchModel = new ArrivalSearch(['received' => 0,'id_user' =>Yii::$app->user->identity->id_user,'postponed'=>0]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'received');


        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8, // leaner size using standard fonts
            'content' => $this->renderPartial('print', [
                // 'searchModel' => $searchModel,
                'dataProvider' => $dataProvider]),
            'options' => [
                'title' => 'MARKET',
                'subject' => 'Generating PDF files via yii2-mpdf extension has never been easy'
            ],
            'methods' => [
                'SetHeader' => ['Generated On: ' . date("r")],
                'SetFooter' => false,
            ]
        ]);

        return $pdf->render();
    }
	public function actionLogin()
    {
        $model = new Password();

		return $this->renderAjax('login', [
                'model' => $model,
            ]);
        
    }
    public function actionReport()
    {


        $searchModel = new ArrivalSearch(['received' => '1']);
        $searchModel->date_start = date('Y-m-d');
        $searchModel->date_end = date('Y-m-d');
		$searchModel->transfer = 0;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        //$dataProvider=ArrivalSearch::find()->all();
        return $this->render('report', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
	
    public function actionReport1($number)
    {


        $searchModel = new ArrivalSearch(['received' => '1']);
        $searchModel->number=$number;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        //$dataProvider=ArrivalSearch::find()->all();
        return $this->render('report', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
	  public function actionPrint($con)
    {
        $searchModel = new ArrivalSearch(['received' => 0,'postponed'=>0,'id_user'=> Yii::$app->user->identity->id_user]);
		
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		$dataProvider->pagination->pageSize=300;
            
        $sum=Arrival::find()->select('sum(sum) as sum,number,datetime,id_contr')->where(['received' => 0,'id_user'=> Yii::$app->user->identity->id_user])->one();
  
        return $this->renderAjax('print',[
            'dataProvider' => $dataProvider,
            'sum' => $sum,
			'con' => $con,
          
		
        ]);

    }
	public function actionUpdatePolka($id, $polka)
    {
        $model = $this->findModel($id);
        $model->polka = $polka;

        $model->save();

    }
    public function actionUpdatePriceTop($id, $price)
    {
        $model = $this->findModel($id);
        $model->price_top = $price;
        $model->save();

    }

    public function actionUpdatePack($id, $price)
    {
        $model = $this->findModel($id);
        $model->pack = $price;
        $model->price_top=round( $model->pricesell/$price,3);
        $model->save();

    }
    public function actionVozvrat()
    {


        $searchModel = new ArrivalSearch();
        $searchModel->date_start = date('Y-m-d');
        $searchModel->date_end = date('Y-m-d');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        //$dataProvider=ArrivalSearch::find()->all();
        return $this->render('report', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionReturn()
    {

        $searchModel = new RestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('return', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }
	 public function actionReceivedReturn2($number)
    {
		
		foreach(Arrival::find()->where(['number' => $number ])->all() as $arrival)
		{
			
			$return=new ReturnArrival();
			$return->id_user=Yii::$app->user->identity->id_user;
			$return->id_product=$arrival->id_product;
			$return->id_contr=$arrival->id_contr;
			$return->quantity=$arrival->quantity;
			$return->price=$arrival->price;
			$return->usd=$arrival->usd;
			$return->id_store=$arrival->id_store;
			$return->date=date("Y-m-d H:i:s");
			$return->received=1;
			$return->save();
			
			$debt=new Debt();
			$debt->id_user=Yii::$app->user->identity->id_user;
			$debt->id_contr=$arrival->id_contr;
			$debt->debt=-$arrival->price;
			$debt->sum_usd=-$arrival->usd;
			$debt->datatime=date("Y-m-d H:i:s");
			$debt->number=$return->id;
			$debt->discount=0;
			$debt->save();
			
			
			
			$arrival->delete();
			
		}
	}
		
    public function actionReceivedReturn($store,$contractor, $date)
    {
		
		foreach(Arrival::find()->where(['received' => 0,'id_user' =>Yii::$app->user->identity->id_user,'postponed'=>0 ])->all() as $arrival)
		{
			
			$return=new ReturnArrival();
			$return->id_user=Yii::$app->user->identity->id_user;
			$return->id_product=$arrival->id_product;
			$return->id_contr=$contractor;
			$return->quantity=$arrival->quantity;
			$return->price=$arrival->price;
			$return->usd=$arrival->usd;
			$return->id_store=$store;
			$return->date=date("Y-m-d H:i:s");;
			$return->received=1;
			$return->save();
			
			$debt=new Debt();
			$debt->id_user=Yii::$app->user->identity->id_user;
			$debt->id_contr=$contractor;
			$debt->debt=-$arrival->price*$arrival->quantity;
			$debt->sum_usd=-$arrival->usd;
			$debt->datatime=$date." ".date("H:i:s");
			$debt->number=$return->id;
			$debt->discount=0;
			$debt->save();
			/*
			$move=new Move();
			$move->id_product=$arrival->id_product;
			$move->quantity=$arrival->quantity;
			$move->price=$arrival->price;
			$move->sum=$arrival->quantity*$arrival->price;
			$move->type=3;
			$move->datetime=date("Y-m-d H:i:s");
			$move->save();
			*/
			$quantity=$arrival->quantity;
			foreach (Arrival::find()->where(["id_product"=>$arrival->id_product,'received' =>1,'postponed' => 0,"id_store"=>$store])->andWhere(" rest>0")->orderBy("datetime DESC")->all()as $row)
			{
				 if($quantity<=$row->rest) {
					   $row->rest=$row->rest-$quantity;
					   $row->save();
						break;
					}
				else 
					{
					$quantity=$quantity-$row->rest;
					$row->rest=0;
					$row->save();
					}
			}
			
			$arrival->delete();
			
			
		}
		
		Yii::$app->session->remove('contractor');

       /* foreach (Arrival::find()->where(["id_product"=>$id])->andWhere(" rest>0")->orderBy("datetime DESC")->all()as $row)
        {
           if($quantity<=$row->rest) {
               $row->rest=$row->rest-$quantity;
               $row->returnp=$row->returnp+$quantity;
               $row->save();

               $debt=Debt::find()->where(['number'=>$row->number])->one();
               $debt->debt=$debt->debt-$row->price*$quantity;
               $debt->sum=$debt->sum-$row->price*$quantity;
               $debt->save();
               break;
           }
            else {
                $row->returnp=$row->rest;
                $quantity=$quantity-$row->rest;
                $row->rest=0;
                $row->save();



            }

        }*/

    }
    public function actionRest()
    {
		$searchModel = new RestSearch();
		$barcode=Yii::$app->request->get('barcode');
		
		
		if ($barcode) {	
		foreach (Barcodep::find()->andWhere(['like', 'name',"%$barcode%", false])->all() as $barcode){

			$product[]=$barcode->id_product;
						
			}
		$searchModel->id_product=$product;	
		}
        
		
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $param=Yii::$app->request->queryParams["RestSearch"]["id_store"];
        if ($param)
            if (Yii::$app->request->queryParams["RestSearch"]["type"])
                $sum=Arrival::find()->select("sum(rest) as rest,sum(price*rest) as sum,sum(rest*pricesell) as sumsell")->joinWith(['idProduct.idType'])->where(['id_store'=>$param,'id_type'=>Yii::$app->request->queryParams["RestSearch"]["type"]])->one();
                else $sum=Arrival::find()->select("sum(rest) as rest,sum(price*rest) as sum,sum(rest*pricesell) as sumsell")->where(['id_store'=>$param])->one();
        else
            if (Yii::$app->request->queryParams["RestSearch"]["type"])
                $sum=Arrival::find()->select("sum(rest) as rest,sum(price*rest) as sum,sum(rest*pricesell) as sumsell")->joinWith(['idProduct.idType'])->where(['id_type'=>Yii::$app->request->queryParams["RestSearch"]["type"]])->one();
            else $sum=Arrival::find()->select("sum(rest) as rest,sum(price*rest) as sum,sum(rest*pricesell) as sumsell")->one();
        //$dataProvider=ArrivalSearch::find()->all();
        return $this->render('rest', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'rest_sum' => round($sum->rest,2),
            'sum_sum' => round($sum->sum,2),
            'sum_sumsell'=>round($sum->sumsell,2)
        ]);
    }

    public function actionEdit()
    {


        $searchModel = new RestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('edit', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionUsdAzn($usd){
        $rate=Exchange::find()->one();
        $price=$usd * $rate->rates;
        echo $price;
    }
  public function actionTradePrice($id,$price){
        $model = $this->findModel($id);
        $model->trade_price = $price;
        $model->save();
    }
public function actionUpdateBoxing($id,$price){
        $model = $this->findModel($id);
        $model->boxing = $price;
        $model->save();
    }
	public function actionUpdatePriceMin($id,$price){
        $model = $this->findModel($id);
        $model->pricesell_min = $price;
        $model->save();
    }
    public  function actionExchange($rate){
        $exchange=Exchange::find()->one();
        $exchange->rates=$rate;
        $exchange->date=date("Y-m-d H:i:s");
        $exchange->save();
    }

    public function actionPostponedArrival($store,$contractor){
		  $max = Arrival::find()->select('number')->max('number');
        if ($max) $max = $max + 1;
        else $max = 1;
        $model2 = new Arrival();
        $model2->updateAll(['postponed'=>1,'id_contr'=>$contractor,'id_store'=>$store,'number'=>$max],['postponed'=>0,'received' => 0,'id_user' =>Yii::$app->user->identity->id_user ]);


    }
  public function actionSelect($id)
    {
       Yii::$app->session->set('contractor',$id);
      
    }
    public function actionPostponed()
    {
        $searchModel = new ArrivalSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->where('postponed <> 0');
        return $this->render('postponed', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,


        ]);
    }


    public function actionPostponedSelect($number)
    {
      
        $model2 = new Arrival();
        $model2->updateAll(['postponed'=>0,'id_user'=>Yii::$app->user->identity->id_user],['number' => $number]);


    }
}
