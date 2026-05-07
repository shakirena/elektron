<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TypeBalanceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


?>
<div class="type-balance-index">


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <p>
        <?= Html::button('<i class="glyphicon glyphicon-plus"></i> əlavə', ['value' => Url::to(['create']), 'class' => 'btn btn-danger', 'id' => 'typeBalance']) ?>
    </p>
    <?php
    Modal::begin([
        'header' => '<h2>Create type product</h2>',
        'id' => 'type-balance-create',
        'size' => 'modal-sm',

    ]);

    echo '<div id="modalContent"></div>';

    Modal::end();
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [
            'style'=>'width:900px',
            'class' => 'table-rena table-rena2',

        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],


            'name',
            'type',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
