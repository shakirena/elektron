<?php

namespace app\controllers;

use Yii;
use app\models\Arrival;
use app\models\ArrivalSearch;

class ReportController extends \yii\web\Controller
{
    public $layout = 'reports';
    public function actionIndex()
    {
        $searchModel = new ArrivalSearch(['received' => 0]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('arrival\report', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }

}
