<?php

namespace app\controllers;

use app\models\Debt;
use app\models\TypeBalance;
use Yii;
use app\models\Balance;
use app\models\Dclient;
use app\models\BalanceSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
/**
 * BalanceController implements the CRUD actions for Balance model.
 */
class BalanceController extends Controller
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
     * Lists all Balance models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BalanceSearch();
        $searchModel->date_start = date('Y-m-d');
        $searchModel->date_end = date('Y-m-d');
        $current = Balance::find()->select("current_sum")->orderBy("id DESC")->one();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
$rasxod=round(Balance::find()->select("sum(sum) as sum")->joinWith("idType")->andWhere("type_balance.type=1")->andWhere($dataProvider->query->where)->one()->sum,2);
        $prixod=round(Balance::find()->select("sum(sum) as sum")->joinWith("idType")->andWhere("type_balance.type=0")->andWhere($dataProvider->query->where)->one()->sum,2);
$ostatok=$prixod-$rasxod;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'rasxod' =>  $rasxod,
            'prixod' =>  $prixod,
            'ostatok' =>$ostatok
        ]);




    }

    /**
     * Displays a single Balance model.
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
     * Creates a new Balance model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    { $model = new Balance();
        if (Yii::$app->request->post()) {
            $current = Balance::find()->select("current_sum")->orderBy("id DESC")->one();
            $balance = Yii::$app->request->post("Balance");
            $model->sum=$balance["sum"];
            $model->id_type=$balance["id_type"];
            $model->datetime=$balance["datetime"];
            $model->note=$balance["note"];
            $model->id_store=$balance["id_store"];
            if (Yii::$app->request->post("tyype"==0))
            $model->current_sum = $balance["sum"] + $current->current_sum;
            else $model->current_sum =  $current->current_sum-$balance["sum"];
            $model->id_user= Yii::$app->user->identity->id_user;
            $model->user_name=$balance["user_name"];
           // print_r($model);
            if ($model->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
        else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Balance model.
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
     * Deletes an existing Balance model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {

        $sum=Balance::find()->where(["id"=>$id])->one();
        $type=TypeBalance::find()->where(["id"=>$sum->id_type])->one()->id;
        $model=Balance::find()->orderBy("id DESC")->one();
        if($type==2 || $type==3)   {
            $debt=Dclient::find()->where(['number'=>$sum->number])->one();
            print_r($debt);
            $debt->debt=$debt->debt+$sum->sum;
            $debt->save();
        }
        if($type==15)   {
            $debt=Dclient::find()->where(['number'=>$sum->number])->one();
            print_r($debt);
            $debt->debt=$debt->debt-$sum->sum;
            $debt->save();
        }
        if ($type==1) return $this->redirect(['index']);
        //$model->current_sum=$model->current_sum+$sum;
       // else $model->current_sum=$model->current_sum-$sum;

        $this->findModel($id)->delete();
      return $this->redirect(['index']);
    }

    public function actionTypeBalance($type)
    {

        return    Select2::widget([
            'data' => ArrayHelper::map(TypeBalance::find()->where(["type" =>$type])->all(), 'id', 'name'),
            'name' => 'id_type',
            'options' => [
                'placeholder' => 'Seçin',

                'id'=>'id_type',

            ]
        ]);


    }

    /**
     * Finds the Balance model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Balance the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Balance::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
