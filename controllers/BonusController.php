<?php

namespace app\controllers;

use Yii;
use app\models\Bonus;
use app\models\BonusSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * BonusController implements the CRUD actions for Bonus model.
 */
class BonusController extends Controller
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
     * Lists all Bonus models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model1 = Bonus::findOne(1);
        $model2 = Bonus::findOne(2);
        return $this->render('index', [
            'model1' => $model1,
            'model2' => $model2,
            
        ]);
    }
    public function actionEdit($type,$par)
    {

        if ($type=='par1')
            {
                $model1 = Bonus::findOne(1);
                $model1->par1 = $par;
                $model1->save();
            }
            if ($type=='par2' || $type=='par3')
            {
                $model1 = Bonus::findOne(2);
                $model1->$type = $par;
                $model1->save();
                
            }
    }

    public function actionEditStatus($id)
    {
        if ($id==1)
        {
            $model1 = Bonus::findOne(2);
            $model1->status = 0;
            $model1->save();

            $model1 = Bonus::findOne(1);
            $model1->status = 1;
            $model1->save();
        }
        else{
            $model1 = Bonus::findOne(2);
            $model1->status = 1;
            $model1->save();

            $model1 = Bonus::findOne(1);
            $model1->status = 0;
            $model1->save();
        }

    }
    /**
     * Displays a single Bonus model.
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
     * Creates a new Bonus model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Bonus();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Bonus model.
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
     * Deletes an existing Bonus model.
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
     * Finds the Bonus model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Bonus the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Bonus::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
