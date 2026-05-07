<?php

namespace app\controllers;

use app\models\Arrival;
use app\models\Barcodep;
use app\models\RestSearch;
use Yii;
use app\models\Sverka;
use app\models\Product;
use app\models\SverkaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\ProductSearch;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use app\models\TypeProduct;
/**
 * SverkaController implements the CRUD actions for Sverka model.
 */
class SverkaController extends Controller
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
     * Lists all Sverka models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SverkaSearch(["id_store" =>  Yii::$app->session->get("sverka")]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $sum_fakt=Sverka::find()->select("sum(quantity) as sum_fakt")
            ->where(["id_store" =>  Yii::$app->session->get("sverka")])
            ->one();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'sum_fakt' => $sum_fakt->sum_fakt,
        ]);
    }

    /**
     * Displays a single Sverka model.
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
     * Creates a new Sverka model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Sverka();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Sverka model.
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


    public function actionUpdateQuantity($id, $quantity)
    {
        $model = $this->findModel($id);
        $model->quantity = $quantity;
        $model->save();

    }
    /**
     * Deletes an existing Sverka model.
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
     * Finds the Sverka model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Sverka the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Sverka::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    public function actionFind()
    {

        $searchModel = new RestSearch();
        $searchModel->id_store=Yii::$app->user->identity->id_store;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderAjax('find', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }


    public function actionInsert($id, $quantity)
    {
        $model=Sverka::find()->where(["id_product"=>$id,"id_store" =>  Yii::$app->session->get("sverka")])->one();

        if($model->quantity) {
            $model->quantity=$model->quantity+ $quantity;
            $model->save();
        }
        else {
            $sverka = new Sverka();
            $sverka->id_product = $id;
            $sverka->quantity = $quantity;
            $sverka->id_store = Yii::$app->session->get("sverka");
            $sverka->save();
        }


    }

    public function actionInsertType(array $id)
    {
        foreach ($id as $id1) {

            $model = Sverka::find()->where(["id_product" => $id1, "id_store" => Yii::$app->session->get("sverka")])->one();

            if (!$model->quantity) /*{
                $model->quantity = $model->quantity + 1;
                $model->save();
            } else */
            {
                $rest=Arrival::find()->select("sum(rest) as rest")->where(["id_product" => $id1])->one();
                if ($rest->rest) $rest=$rest->rest;
                else $rest=1;

                    $sverka = new Sverka();
                    $sverka->id_product = $id1;
                    $sverka->quantity = $rest;
                    $sverka->id_store = Yii::$app->session->get("sverka");
                    $sverka->save();

            }

        }
    }

    public function actionInsertBarcode($barcode)
    {
        $product=Barcodep::find()

            ->where(["name"=>$barcode])
            ->one();
        if ($product->id) {
            $model=Sverka::find()->where(["id_product"=>$product->id_product,"id_store" =>  Yii::$app->session->get("sverka")])->one();
            if($model->quantity) {
                $model->quantity=$model->quantity+ 1;
                $model->save();
            }
            else {
                $sverka = new Sverka();
                $sverka->id_product = $product->id_product;
                $sverka->quantity =1;
                $sverka->id_store = Yii::$app->session->get("sverka");
                $sverka->save();
            }
        }

    }

    public function actionReceived()
    {
		
 
	//exec('mysqldump -uroot -pTuren2014 stroymataydin > D:/damp/stroymat_%date:/=%_.sql');
	
    $max = Arrival::find()->select('number')->max('number');
        if ($max) $max = $max + 1;
        else $max = 1;

		/*$sell=new Arrival();
        $sell->updateAll(["rest"=>0], ["id_store" =>  Yii::$app->session->get("sverka")]);
*/
        foreach (Sverka::find()->where(["id_store" =>  Yii::$app->session->get("sverka")])->all() as $model)
        {
            $arrival1=Arrival::find()->where(["id_product" => $model->id_product,"id_store" =>  Yii::$app->session->get("sverka")])->orderBy("datetime DESC")->one();
            if ($arrival1->id) {
                foreach( Arrival::find()->where(["id_product" => $model->id_product, "id_store" =>  Yii::$app->session->get("sverka")])->all() as $arrival)
                {
                    $arrival->rest=0;
                    $arrival->save();

                }
                $arrival1->rest=$model->quantity;;
                $arrival1->save();
                $model->delete();
            }
            else {


                $arrival = new Arrival();
                $arrival->id_product =  $model->id_product;
                $arrival->quantity = $model->quantity;
                $arrival->rest = $model->quantity;
                $arrival->price =0;
                $arrival->id_store=1;
                $arrival->number=$max;
              //  $arrival->pricesell =$pricesell;
               // $arrival->usd=$usd;
                //$arrival->sum = $quantity*$price;
                $arrival->id_user = Yii::$app->user->identity->id_user;
                $arrival->received =1;
                $arrival->save();
                $model->delete();
            }


        }
	

    }

    public function actionUpdateStore($id)
    {

        Yii::$app->session->set("sverka",$id);

    }



    public function actionCheck($id){
        $model=TypeProduct::find()->where(["id_parent" => $id])->one();
        if ($model->id) { return 0;}
        else { return 1;}

    }
}
