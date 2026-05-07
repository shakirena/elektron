<?php

namespace app\controllers;

use app\models\Dclient;
use app\models\Sell;
use app\models\Arrival;
use Yii;
use app\models\Postponed;
use app\models\PostponedSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use kartik\mpdf\Pdf;
use app\models\Client;

/**
 * PostponedController implements the CRUD actions for Postponed model.
 */
class PostponedController extends Controller
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
     * Lists all Postponed models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PostponedSearch();
        $searchModel->received=0;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionReceive()
    {
        $searchModel = new PostponedSearch();
       $searchModel->received=1;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('receive', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionMaster()
    {
        $searchModel = new PostponedSearch();
        $searchModel->received=-1;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('master', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionReceive1()
    {
        $searchModel = new PostponedSearch();
        $searchModel->received=-1;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('receive', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Postponed model.
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
     * Creates a new Postponed model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Postponed();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    public function actionPostponedReceived($number)
    {
        $postponed=new Postponed();
        $postponed->updateAll(["received"=>1], ["number"=>$number, 'received' => 0]);

        $url = "https://pushall.ru/api.php?type=self&id=44560&key=eb3f83211ddab0c1610bc6e1bf2e0b42&text= Malın müştəriyə təhvil verilməsi təsdiq edildi (nömrə $number) &title=Anbarda yeni hərəkət";
        file_get_contents($url);
    }


    /**
     * Updates an existing Postponed model.
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
     * Deletes an existing Postponed model.
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
     * Finds the Postponed model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Postponed the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Postponed::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionPostponedPrint($number)
    {
        $flag = 0;
        foreach (Postponed::find()->where(["number" => $number,"received"=>'1'])->all() as $sell) {
             $arrival = Arrival::find()
                 ->select('sum(rest) as rest')
                 ->where(['id_product' => $sell->id_product])
                 ->andWhere(" postponed=0")
                 ->groupBy('id_product')
                 ->one();

             if ($arrival->rest > 0) {
                 $flag = 0;
             } else {
                 $flag = 1;
                 break;
             }

         }
         if ($flag == 1) return 0;
         else {
             $vse=0;$i=0;
             foreach (Postponed::find()->where(["number" => $number,"received"=>'1'])->all() as $postponed) {
                 $vse = 0;
                 $sell = Sell::find()->where(['id' => $postponed->id_sell])->one();

                 foreach (Arrival::find()->where(['id_product' => $postponed->id_product, 'id_store' => $postponed->id_store])->andWhere("rest>0 AND postponed=0")->orderBy(['datetime' => SORT_ASC])->all() as $arrival) {
                     if ($postponed->quantity <= $arrival->rest) {
                         $arrival->rest = $arrival->rest - $postponed->quantity;
                         $arrival->save();

                         $sell->postponed = $sell->postponed - $postponed->quantity;
                         $sell->user_issue = $postponed->id_user;
                         $sell->date_issue = $postponed->date;
                        // $sell->id_master=$postponed->id_master;
                         $sell->save();
                         print_r($sell);
                         $vse = 1;
                         $i++;
                         break;
                     } else {
                         $sell->postponed = $sell->postponed - $arrival->rest;
                       //  $sell->id_master=$postponed->id_master;
                         $sell->save();
                         $i++;
                         $postponed->quantity = $postponed->quantity - $arrival->rest;
                         $arrival->rest = 0;
                         $arrival->save();
                     }
                 }

                 if ($vse == 0) {
                     foreach (Arrival::find()->where(['id_product' => $postponed->id_product])->andWhere("rest>0 AND postponed=0 AND 'id_store'!=" . $postponed->id_store)->orderBy(['datetime' => SORT_ASC])->all() as $arrival) {
                         if ($postponed->quantity <= $arrival->rest) {
                             $arrival->rest = $arrival->rest - $postponed->quantity;
                             $arrival->save();

                             $sell->postponed = $sell->postponed - $postponed->quantity;
                             $sell->user_issue = $postponed->id_user;
                             $sell->date_issue = $postponed->date;
                          //   $sell->id_master=$postponed->id_master;
                             $sell->save();
                             print_r($sell);
                             $vse = 1;
                             $i++;
                             break;
                         } else {
                             $sell->postponed = $sell->postponed - $arrival->rest;
                           //  $sell->id_master=$postponed->id_master;
                             $sell->save();
                             $i++;
                             $postponed->quantity = $postponed->quantity - $arrival->rest;
                             $arrival->rest = 0;
                             $arrival->save();
                         }
                     }
                 }


             }
             $postponed=new Postponed();
             $postponed->updateAll(["received"=>'-1'], ["number"=>$number]);
             return 1;}}

    public function actionPrintPostponed($number)
    {


        $searchModel = new PostponedSearch();
        //  print_r($id);
        $searchModel->number=$number;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        ///  print_r($dataProvider->query);
        $sell=Sell::find()->select("sum(sum) as sum,id_client")->where(["number"=>$number])->one();
        $money=Dclient::find()->where(['id_client' => $sell->id_client,"number"=>$number])->one()->debt;
        $client=Client::find()->where(['id_client' => $sell->id_client])->one();
        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8, // leaner size using standard fonts
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'marginTop' => "0px",
            'marginLeft' => "3px",
            'marginRight' => "3px",
            'content' => $this->renderPartial('printp', [
                //   'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'number' => $number,
                'client' => $client,
                'sum' => $sell->sum,
                'money' =>$money

            ]),
            'options' => [
                'title' => 'MARKET',
                //'subject' => 'Generating PDF files via yii2-mpdf extension has never been easy',

            ],
            'methods' => [
                // 'SetHeader' => ['Generated On: ' . date("r")],
                'SetFooter' => false,
                'SetJS' =>'this.print();'
            ]
        ]);

        return $pdf->render();
    }

}



