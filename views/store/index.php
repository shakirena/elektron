<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\StoreSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Stores';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="store-index">

   <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::button('<i class="glyphicon glyphicon-plus"></i>Əlavə et', ['value' => Url::to(['create']), 'class' => 'btn btn-danger', 'id' => 'store']) ?>
    </p>
    <?php
    Modal::begin([
        'header' => '<h2>Yeni satış nöqtəsi adının açılması</h2>',
        'id' => 'store-create',
        'size' => 'modal-sm',

    ]);

    echo '<div id="modalContent"></div>';

    Modal::end();
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions'=>[
            'style'=>'width:600px',
            'class' => 'table-rena table-rena2 ',
        ],
        'columns' => [

            ['class' => 'kartik\grid\SerialColumn'],

            //'id',
            'name',
            'telephone',

            ['class' => 'kartik\grid\ActionColumn'],
        ],
    ]); ?>
</div>
