<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\TypeProduct;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TypeProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Type Products';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="type-product-index">



    <?php
    Modal::begin([
        'header' => '<h2>Yeni mal grupunun  açılması</h2>',
        'id' => 'type-create',
        'size' => 'modal-sm',

    ]);

    echo '<div id="modalContent"></div>';

    Modal::end();
    ?>
    <br>
    <div class="btn-group">

            <?= Html::button('<i class="glyphicon glyphicon-plus"></i>Əlavə et', ['value' => Url::to(['create?j=1']), 'class' => 'btn btn-danger', 'onclick' => "addTypeProduct()", 'id' => 'type']) ?>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions'=>[
            'style'=>'width:800px;',
            'class' => 'table-rena table-rena2 ',

        ]
        ,
        'pjax' => true,
        'columns' => [

            ['class' => 'kartik\grid\SerialColumn'],

            // 'id',
            'name',
            ['class' => 'kartik\grid\ActionColumn'],
        ],
    ]); ?>
</div>
