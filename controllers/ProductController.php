<?php

namespace app\controllers;
use yii\helpers\Url;
use app\models\Sell;
use Faker\Provider\Barcode;
use Yii;
use app\models\Product;

use app\models\Arrival;
use app\models\Barcodep;
use app\models\ProductSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\UploadForm;
use yii\web\UploadedFile;
use yii\imagine\Image;
/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller
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
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Product model.
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
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
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
			
               return $this->redirect(['index']);
            }
            /*else {
                $barcode=new Barcodep();
                return $this->renderAjax('create', [
                    'model' => $model,
                    'barcode' =>$barcode
                ]);
            }*/
        }
        else {

            $model->id_type=Product::find()->orderBy("id DESC")->one()->id_type;
            $barcode=new Barcodep();
            return $this->renderAjax('create', [
                'model' => $model,
                'barcode' =>$barcode,
				'image' => $image,
            ]);
        }

    }
	 
    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $barcode=new Barcodep();
		$image=new UploadForm();
        if (Yii::$app->request->post()) {
			$b=Yii::$app->request->post("Barcodep");
			foreach ( $b['name'] as $barcode1){
			$bar=Barcodep::find()->where(['name'=>$barcode1])->andWhere("id_product!=$id")->one();
			if ($bar->id_product) {$flag=1;break;}
			}
			if ($flag==0) { 
            $product = Yii::$app->request->post("Product");
            $model->name = $product['name'];
			$model->boxing = $product['boxing'];
            $model->id_type = $product['id_type'];
            if ($model->save()) { //&& $model->save()
				
                $barcode->deleteAll(['id_product' => $id]);
              
                foreach ( $b['name'] as $barcode)
                {
                    $bar_code=new Barcodep();
                    $bar_code->id_product=$id;
                    $bar_code->name=$barcode;
                    $bar_code->save();

                };
				
				if ($image->imageFiles = UploadedFile::getInstances($image, 'imageFiles')){
                if ($image->upload($model->id)) {
                  
                    $image_tratment=new \app\models\Image();
                    $image_tratment->id_tre=$id;
                    $image_tratment->path="uploads/".$id."/".$image->imageFiles[0]->name;
                    $thumbnFile="thumb/".$model->id."/".$image->imageFiles[0]->name;
					$image_tratment->thumb=$thumbnFile;
					Image::thumbnail($_SERVER['DOCUMENT_ROOT'] .'/web/'.$image_tratment->path, 50, 50)->save($_SERVER['DOCUMENT_ROOT'] .'/web/'.$thumbnFile, ['quality' => 80]);
                    $image_tratment->save();
                    // return;
                }}
                return $this->redirect(['index']);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
			}
			else  return $this->render('update', [
                'model' => $model,
                'barcode' => $barcode,
				'image' => $image,
				'error'=> '1'
            ]);
			
        } else {
            return $this->render('update', [
                'model' => $model,
                'barcode' => $barcode,
				'image' => $image,
            ]);
        }
    }

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
	public function RDir ($path) {
	
		 // если путь существует и этопапка
		 if ( file_exists( $path ) AND is_dir( $path ) ) {
		   // открываем папку
			$dir = opendir($path);
			while ( false !== ( $element = readdir( $dir ) ) ) {
			  // удаляем только содержимое папки
			  if ( $element != '.' AND $element != '..' )  {
				$tmp = $path . '/' . $element;
				chmod( $tmp, 0777 );
			   // если элемент является папкой, то
			   // удаляем его используя нашу функцию RDir
				if ( is_dir( $tmp ) ) {
				 RDir( $tmp );
			   // если элемент является файлом, то удаляем файл
				} else {
				  unlink( $tmp );
			   }
			 }
		   }
		   // закрываем папку
			closedir($dir);
			// удаляем саму папку
		   if ( file_exists( $path ) ) {
			 rmdir( $path );
		   }
		 }
}
    public function actionDelete($id)
    {
        $arrival=Arrival::find()->where(['id_product'=>$id,'received'=>1])->one();
		if ($arrival->id) return $this->redirect(['index']);
		$image= \app\models\Image::find()->where(["id_tre"=>$id])->one();
		if ($image->id)
		{
		
			$this->RDir(dirname($_SERVER['DOCUMENT_ROOT'] .'/web/'.$image->path));
			$this->RDir( dirname($_SERVER['DOCUMENT_ROOT'] .'/web/'.$image->thumb));
			$image->delete();
		}
		$barcode=Barcodep::find()->where(["id_product"=>$id])->one();
		$barcode->delete();
		$this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


}
