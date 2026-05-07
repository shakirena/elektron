<?php

namespace app\controllers;

use Yii;
use app\models\TypeProduct;
use app\models\TypeProductSearch;
use app\models\ProductSearch;
use app\models\Product;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
/**
 * TypeProducController implements the CRUD actions for TypeProduct model.
 */
class TypeProducController extends Controller
{
    public $layout = 'admin';
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
     * Lists all TypeProduct models.
     * @return mixed
     */
    public function actionIndex()
    {
      $searchModel = new TypeProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }

    /**
     * Displays a single TypeProduct model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
		
		  $searchModel = new ProductSearch();
		   $searchModel->id_type=$id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		
        return $this->render('view', [
            'model' => $this->findModel($id),
			  'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

	public function actionPrint($id)
    {
		
		  $product = Product::find()->where(['id_type'=>$id])->all();


		
        return $this->renderAjax('print', [
            'model' => $this->findModel($id),
			  'products' => $product,
        ]);
    }
    /**
     * Creates a new TypeProduct model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreateProduct($id){
        $files = $_FILES["Product"];
        $model = new Product();
        if (Yii::$app->request->post()) {
                    $product = Yii::$app->request->post("Product");
                    $model->name = $product['name'];
                    $model->id_type = $product['id_type'];

                    if ($model->save()) { //&& $model->save()
                        if ($files["size"]["image"]){
                            if ($files["size"]["image"] > 1024*3*1024)
                            {
                                echo "Размер превышает три мегабайта";

                            }

                            if(is_uploaded_file($files["tmp_name"]["image"]))
                            {

                                mkdir("img/images/".$model->id,0700);
                                move_uploaded_file($files["tmp_name"]["image"],"img/images/".$model->id."/". $files["name"]["image"]);
                                $model->image = $files["name"]["image"];
                                $model->save();
                            }
                            else
                            {
                                echo "Ошибка загрузки файла";
                                exit;
                            }
                        }


                          return $this->redirect(['index']);
                    } else {
                        return $this->renderAjax('..\product\create', [
                            'model' => $model,
                            'type' =>$id
                        ]);
                    }
        }
        else {
                return $this->renderAjax('..\product\create', [
                    'model' => $model,
                    'type' =>$id
                ]);
        }



    }
    public function actionCreateType($id){
        $model = new TypeProduct();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('create', [
                'model' => $model,
                'type' => $id
            ]);
        }
    }

    /**
     * Updates an existing TypeProduct model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing TypeProduct model.
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
     * Finds the TypeProduct model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TypeProduct the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TypeProduct::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionType1($id)
    {
        // $model=TypeProduct::find()->where(["id_parent" => $id])->all();
        return   " Grupa 2". Select2::widget([
            'data' =>  ArrayHelper::map(TypeProduct::find()->where(['id_parent' => $id])->all(), 'id', 'name'),
            'name' => 'type2',
            'options' => [
                'placeholder' => 'Seçin',

                'id'=>'type2',

            ]
        ]);
    }

    public function actionType2($id)
    {
        // $model=TypeProduct::find()->where(["id_parent" => $id])->all();
        return   " Grupa 3". Select2::widget([
            'data' =>  ArrayHelper::map(TypeProduct::find()->where(['id_parent' => $id])->all(), 'id', 'name'),
            'name' => 'type3',
            'options' => [
                'placeholder' => 'Seçin',

                'id'=>'type3',

            ]
        ]);
    }
    public function actionType3($id)
    {


            return " Grupa 4" . Select2::widget([
                'data' => ArrayHelper::map(TypeProduct::find()->where(['id_parent' => $id])->all(), 'id', 'name'),
                'name' => 'type4',
                'options' => [
                    'placeholder' => 'Seçin',

                    'id' => 'type4',

                ]
            ]);

    }
    public function actionType5($id)
    {

            return   " Grupa 5". Select2::widget([
                'data' =>  ArrayHelper::map(TypeProduct::find()->where(['id_parent' => $id])->all(), 'id', 'name'),
                'name' => 'type5',
                'options' => [
                    'placeholder' => 'Seçin',

                    'id'=>'type5',

                ]
            ]);
    }

    public function actionType4($id)
    {
        // $model=TypeProduct::find()->where(["id_parent" => $id])->all();
        return    Select2::widget([
            'data' => ArrayHelper::map(Product::find()->where(["id_type" => $id])->all(), 'id', 'name'),
            'name' => 'product',
            'options' => [
                'placeholder' => 'Seçin',

                'id'=>'product',

            ]
        ]);
    }

    public function actionCheck($id){
        $model=TypeProduct::find()->where(["id_parent" => $id])->one();
        if ($model->id) { return 0;}
         else { return 1;}

    }
}
