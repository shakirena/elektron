<?php

namespace app\controllers;

use app\models\Finance;
use app\models\Balance;
use app\models\FinanceSearch;
use Yii;
use app\models\Debt;
use app\models\Costs;
use app\models\DebtSearch;
use app\models\DebtReportSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DebtController implements the CRUD actions for Debt model.
 */
class DebtController extends Controller
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
     * Lists all Debt models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DebtSearch();
      

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionReport($number)
    {
        $searchModel = new DebtSearch();
      $searchModel->number=$number;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
	 public function actionReport2($date1,$date2)
    {
        $searchModel = new DebtReportSearch();
		$searchModel->date_start = $date1;
		$searchModel->date_end = $date2;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('report', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * Displays a single Debt model.
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
     * Creates a new Debt model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Debt();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Debt model.
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
     * Deletes an existing Debt model.
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
     * Finds the Debt model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Debt the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Debt::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	
    public function actionReceivedDebt($id,$sum,$note,$kassa)
    {
	$sum1=$sum;
	
			$debt=new Debt();
			$debt->id_user=Yii::$app->user->identity->id_user;
			$debt->id_contr=$id;
			$debt->debt=-$sum;
			$debt->note=$note;
			//$debt->sum_usd=-$arrival->usd;
			$debt->datatime=date("Y-m-d H:i:s");
			$debt->save();
			
			
			$cost = new Costs();
			$cost->id_kassa = $kassa;
			$cost->sum = -$sum;
			$cost->fid = $debt->id;
			$cost->id_type = 2;
			$cost->note = $note;
			$cost->id_user = Yii::$app->user->identity->id_user;
			$cost->datetime=date("Y-m-d H:i:s");
			$cost->save();
	
	}
	
	 public function actionDebtAdd()
    {
        $model = new Debt();
        if (Yii::$app->request->post()) {
            $dclient = Yii::$app->request->post("Debt");

      
            $model->id_contr = $dclient['id_contr'];
			$model->id_user=Yii::$app->user->identity->id_user;
			$model->debt = $dclient['debt'];
			$model->sum_usd = $dclient['sum_usd'];
			$model->datatime = date("Y-m-d H:i:s");

            $model->save();
			return $this->redirect(['index']);
        }
        else {
            
            return $this->renderAjax('debt_add', [
                'model' => $model,
            ]);
        }

    }
}
