<?php

use yii\helpers\Html;

use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use kartik\grid\GridView;
use app\models\Contractor;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\DebtSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = 'Debts';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="debt-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],


            'number',
            [
                'attribute' => 'debt',
                'width' => '80px'
            ],

            'sum',


        ],
    ]); ?>
</div>
