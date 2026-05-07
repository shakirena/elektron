<?php

namespace app\controllers;

use Yii;
use app\models\Costs;
use app\models\Sell;
use app\models\Dclient;
use app\models\Debt;
use app\models\Kassa;
use app\models\TypeCosts;
use app\models\CostsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CostsController implements the CRUD actions for Costs model.
 */
class CostsController extends Controller
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
     * Lists all Costs models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CostsSearch();
		$searchModel->date_start = date('Y-m-d');//." 00:00:00";
		$searchModel->date_end = date('Y-m-d');//." 23:59:59";
		$searchModel->type = 0;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    } 
	
	public function actionIndex2()
    {
        $searchModel = new CostsSearch();
		$searchModel->date_start = date('Y-m-d');//." 00:00:00";
		$searchModel->date_end = date('Y-m-d');//." 23:59:59";
		$searchModel->type = 1;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,"id_kassa!=1");

		$dataProvider->query->andWhere('id_kassa!=1');
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Costs model.
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
     * Creates a new Costs model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Costs();

        if ($model->load(Yii::$app->request->post())) {
			$post=Yii::$app->request->post("Costs");
			$model->sum = -$model->sum;
			$model->id_user = Yii::$app->user->identity->id_user;
			$model->datetime=$post['datetime']." ". date("H:i:s");
			$model->save();
			Yii::$app->session->remove('id_type');
			
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
	public function actionPrixod()
    {
        $model = new Costs();

        if ($model->load(Yii::$app->request->post())) {
			$post=Yii::$app->request->post("Costs");
			$model->id_user = Yii::$app->user->identity->id_user;
			$model->datetime=$post['datetime']." ". date("H:i:s");
			$model->save();
			Yii::$app->session->remove('id_type');
            return $this->redirect(['index2']);
        } else {
            return $this->render('prixod', [
                'model' => $model,
            ]);
        }
    }
	public function actionSelect($id)
	{
	
		return round(Costs::find()->select("sum(sum) as sum")->where(['id_kassa' => $id])->one()->sum,2);
	
	}
	
	public function actionTransfer()
	{
		$model = new Costs();
		$kassa = Kassa::find()->all();//Costs::find()->select("sum(costs.sum) as sum, kassa.name as note")->joinWith("idKassa")->where("costs.id_kassa is not null")->groupBy("id_kassa")->all();
	
		if ($model->load(Yii::$app->request->post()))
		{
			
			$model->id_user = Yii::$app->user->identity->id_user;
			$model->datetime= date("Y-m-d H:i:s");
			$model->save();
			
			$model2 = new Costs();
			$model2->id_kassa = $model->from_kassa;
			$model2->from_kassa = $model->id_kassa;
			$model2->datetime=date("Y-m-d H:i:s");
			$model2->id_user = Yii::$app->user->identity->id_user;
			$model2->sum = -$model->sum;
			$model2->save();
			
			return $this->redirect(['transfer']);
        } else {
            return $this->render('transfer', [
                'model' => $model,
				'kassa' => $kassa,
            ]);
        }
	
	}
	public function actionKassa()
    {
        return $this->render('kassa');
    }
	
	public function actionKassas()
    {
	$kassa = Costs::find()->select("sum(costs.sum) as sum, kassa.name as note")->joinWith("idKassa")->groupBy("id_kassa")->all();
		
        return $this->render('kassas',[
		
				'kassa' => $kassa,
		]);
    }
	public function actionReport($id,$date1,$date2,$type)
    {
	   $date1.=' 00:00:00';
       $date2.=' 23:59:59';
	   
		$model = Costs::find()->where(["id_kassa" =>$id])->andWhere("datetime>='$date1' AND datetime<='$date2'")->orderBy("datetime ASC")->all();
		$current=Costs::find()->select("sum(sum) as sum")->where(["id_kassa" =>$id])->andWhere("datetime<'$date1'")->one()->sum;
		return $this->render('report', [
					'model' => $model,
					'current' => $current
				]);
	}  
	public function actionCreateType()
    {
        $model = new TypeCosts();

        if ($model->load(Yii::$app->request->post())) {
			$model->type = 0;
			$model->save();
            Yii::$app->session->set('id_type', $model->id);
			
            return $this->redirect(['create']);

        }
        return $this->renderAjax('..\type-costs\create', [
            'model' => $model,
        ]);
    }
	public function actionCreateTypePrixod()
    {
        $model = new TypeCosts();

        if ($model->load(Yii::$app->request->post())) {
			$model->type = 1;
			$model->save();
            Yii::$app->session->set('id_type', $model->id);
			
            return $this->redirect(['prixod']);

        }
        return $this->renderAjax('..\type-costs\create', [
            'model' => $model,
        ]);
    }
    /**
     * Updates an existing Costs model.
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
     * Deletes an existing Costs model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
		$model = $this->findModel($id);
		if ($model->id_type==1) 
		{
			if ($model->fid)
			{	
				$dclient = Dclient::find()->where(["id" => $model->fid])->one();
				$dclient->delete();
			}
			else   return $this->redirect(['index']);	
		}
		
		if ($model->id_type==2) 
		{
			if ($model->fid)
			{	
				$dclient = Debt::find()->where(["id" => $model->fid])->one();;
				$dclient->delete();
			}
			else   return $this->redirect(['index']);	
		}
        $this->findModel($id)->delete();

        return $this->redirect(['transfer']);
    }

    /**
     * Finds the Costs model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Costs the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Costs::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
