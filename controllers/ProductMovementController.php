<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\ProductMovementSearch;

/**
 * ProductMovementController — отчёт «Движение товара».
 *
 * URL: /product-movement/report
 * Feature #27, Story #31.
 *
 * Паттерн: ArrivalController::actionReport + SellController::actionReport.
 * Доступ: только авторизованные пользователи (AccessControl, role '@').
 */
class ProductMovementController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'report' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Отчёт «Движение товара» — единый хронологический список операций.
     *
     * Обязательный фильтр: id_product (ProductMovementSearch::rules()).
     * Если товар не выбран — возвращает пустой ArrayDataProvider.
     *
     * @return string HTML-страница с GridView.
     */
    public function actionReport()
    {
        $searchModel  = new ProductMovementSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('report', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
