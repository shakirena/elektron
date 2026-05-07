<?php

namespace app\controllers;

use Yii;
use app\models\Barcode;
use app\models\BarcodeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Product1Search;
use app\models\ProductSearch;
use app\models\Barcodep;
use yii\data\ActiveDataProvider;

/**
 * BarcodeController implements the CRUD actions for Barcode model.
 */
class BarcodeController extends Controller
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
     * Lists all Barcode models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BarcodeSearch();
		$searchModel->price=0;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
	
	public function actionPrice()
    {
        $searchModel = new BarcodeSearch();
		$searchModel->price=1;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('price', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionFind()
    {

        $searchModel = new ProductSearch();
        //$searchModel->find()->andWhere("length(name)<=5");
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderAjax('find', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }
  public function actionFind2()
    {

        $searchModel = new ProductSearch();
        //$searchModel->find()->andWhere("length(name)<=5");
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderAjax('find2', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }
    public function actionInsert($id, $quantity)
    {
        $model=Barcode::find()->where(["id_product"=>$id,'price'=>0])->one();

        if($model->count) {
            $model->count=$model->count+ $quantity;
            $model->save();
        }
        else {
            $model = new Barcode();
            $model->id_product = $id;
			$model->price = 0;
            $model->count = $quantity;
            $model->save();
        }
    }
	
	 public function actionInsertBarcode($barcode)
    {
		$product=Barcodep::find()

            ->where(["name"=>$barcode])
            ->one();
			
		if ($product->id) {
            $model=Barcode::find()->where(["id_product"=>$product->id_product,'price'=>0])->one();
				if($model->count) {
				$model->count=$model->count+ 1;
				$model->save();
			}
				else {
				$model = new Barcode();
				$model->id_product = $product->id_product;
				$model->price = 0;
				$model->count = 1;
				$model->save();
				print_r($model);
			}
        }

    }
       
  public function actionInsertPrice($id, $quantity)
    {
        $model=Barcode::find()->where(["id_product"=>$id,'price'=>1])->one();

        if($model->count) {
            $model->count=$model->count+ $quantity;
            $model->save();
        }
        else {
            $model = new Barcode();
            $model->id_product = $id;
			$model->price = 1;
            $model->count = $quantity;
            $model->save();
        }
    }
    public function actionUpdateQuantity($id, $quantity)
    {
        $model = $this->findModel($id);
        $model->count = $quantity;
        $model->save();

    }
    /**
     * Displays a single Barcode model.
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
     * Creates a new Barcode model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Barcode();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Barcode model.
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
     * Deletes an existing Barcode model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
	
	 public function actionDeletePrice($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['price']);
    }
 public function actionDeleteAll()
    {
        $model = new Barcode();
        $model->deleteAll('price=0');
    }
	public function actionDeleteAllPrice()
    {
        $model = new Barcode();
        $model->deleteAll('price=1');
    }
    public function actionPrint()
    {
        $model=Barcode::find()->select("*")
        ->joinWith("idProduct")->all();


        return $this->render('print', [
            'model' => $model,
        ]);
    }
    public function actionPrint2()
    {
	$this->layout='barcode';
        $model=Barcode::find()->select("*")->where(["price"=>0])
            ->joinWith("idProduct")->all();


        return $this->render('print2', [
            'model' => $model,
        ]);
    }
	public function actionPrintNew()
    {
	$this->layout='barcode';
        $model=Barcode::find()->select("*")->where(["price"=>0])
            ->joinWith("idProduct")->all();


        return $this->render('print_new', [
            'model' => $model,
        ]);
    }
	public function actionPrintPrice()
    {
	$this->layout='barcode';
        $model=Barcode::find()->select("*")->where(["price"=>1])
            ->joinWith("idProduct")->all();


        return $this->render('print', [
            'model' => $model,
        ]);
    }
    /**
     * Finds the Barcode model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Barcode the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Barcode::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
