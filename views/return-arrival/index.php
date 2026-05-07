<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ReturnArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Return Arrivals';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="return-arrival-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Return Arrival', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'id_user',
            'id_product',
            'id_contr',
            'quantity',
            // 'price',
            // 'usd',
            // 'id_store',
            // 'date',
            // 'received',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
