<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ContractorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Contractors';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contractor-index">


    <p>
        <?= Html::button('<i class="glyphicon glyphicon-plus"></i>Əlavə et', ['value' => Url::to(['create']), 'class' => 'btn btn-danger', 'id' => 'contractor']) ?>
    </p>
    <?php
    Modal::begin([
        'header' => '<h2>Yeni şirkət adının açılması</h2>',

        'id' => 'contractor-create',
        'size' => 'modal-sm',

    ]);

    echo '<div id="modalContent"></div>';

    Modal::end();
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [
            'style'=>'width:1000px',
            'class' => 'table-rena table-rena2 ',

        ],
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

           // 'id',
            'name',
            'telephone',
            [
                'attribute' =>  'specification',
                'width' => '350px'
            ],


            ['class' => 'kartik\grid\ActionColumn', 'template' => '{delete}{update}'],
        ],
    ]); ?>
</div>
