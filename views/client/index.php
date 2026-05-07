<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ClientSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Clients';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::button('<i class="glyphicon glyphicon-plus"></i>Əlavə et', ['value' => Url::to(['create']), 'class' => 'btn btn-danger', 'id' => 'client']) ?>
    </p>
    <?php
    Modal::begin([
        'header' => '<h2>Yeni müştəri adının açılması</h2>',
        'id' => 'client-create',
        'size' => 'modal-sm',

    ]);

    echo '<div id="modalContent"></div>';

    Modal::end();
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions'=>[
            'style'=>'width:900px',
            'class' => 'table-rena table-rena2 ',

        ],
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            'id_client',
            'fio',
            'phone',
            'adress',
            'mobile',
            'email',
			'barcode',
            ['class' => 'kartik\grid\ActionColumn'],
        ],
    ]); ?>
</div>
