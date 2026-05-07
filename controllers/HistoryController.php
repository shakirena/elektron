<?php

namespace app\controllers;

use Yii;
use app\models\History;
use app\models\Arrival;
use app\models\HistorySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HistoryController implements the CRUD actions for History model.
 */
class HistoryController extends Controller
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
     * Lists all History models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new HistorySearch();
		$searchModel->date_start = date('Y-m-d');
        $searchModel->date_end = date('Y-m-d');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single History model.
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
     * Creates a new History model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new History();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing History model.
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
     * Deletes an existing History model.
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
     * Finds the History model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return History the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
	 
	 
    protected function findModel($id)
    {
        if (($model = History::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	 public function actionHistoryReport()
{


        $searchModel = new HistorySearch(['received' => '1']);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

      
        return $this->render('history_report', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
 }
public function actionHistory()
{
$date=History::find()->orderBy("date DESC")->one()->date;

 return $this->render('history', [          
            'date' => $date
        ]);

}
public function actionReceivedHistory()
{
	$date=History::find()->orderBy("date DESC")->one()->date;
	if (date("Y-m-d")!=$date) 
	{
	foreach (Arrival::find()->select("sum(rest) as rest,id_product,id_contr,id_store,pricesell,price")->groupBy("id_product,id_store")->all() as $model)
	{
		$history=new History();
		$history->id_product=$model->id_product;
		$history->rest=$model->rest;
		$history->id_store=$model->id_store;
		$history->pricesell=$model->pricesell;
		$history->price=$model->price;
		$history->id_contr=$model->id_contr;
		$history->date_create=date("Y-m-d H:i:s");
		$history->date=date("Y-m-d");
		$history->save();
	}
	}
	$searchModel = new HistorySearch();
	$searchModel ->date=$date;
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

}
}
