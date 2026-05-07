<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use \app\models\TypeProduct;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use kartik\select2\Select2;
use app\models\Store;
/* @var $this yii\web\View */
/* @var $searchModel app\models\DeviceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


?>
<div class="device-index">


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php $storeList = ArrayHelper::map(Store::find()->all(), 'id', 'name'); ?>

    <?php $typeList = ArrayHelper::map(TypeProduct::find()->orderBy('name')->asArray()->all(), 'id', 'name'); ?>

    <?php
    Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'current',
            //'tabindex' => true,
        ],

        'size' => "250px",

    ]);

    echo '<div id="modalContent1">'.  Html::input('hidden','id','',[' class' =>"form-control", 'id' =>'id'])
    .'
<div class="form-horizontal" role="form">
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-2 control-label">Miqdar</label>
    <div class="col-sm-10">'. Html::input('text','quantity','1',[' class' =>"form-control", 'id' =>'quantity']).'</div>
  </div>

  <div class="form-group">
    <label for="inputEmail3" class="col-sm-2 control-label">Gəliş qiyməti (AZN)</label>
    <div class="col-sm-10">'. Html::input('text','azd','1',[' class' =>"form-control", 'id' =>'price']).'</div>
  </div>

    <div class="form-group">
    <label for="inputEmail3" class="col-sm-2 control-label">Gəliş qiyməti (USD)</label>
    <div class="col-sm-10">'. Html::input('text','usd','1',[' class' =>"form-control", 'id' =>'usd']).'</div>
  </div>
</div>'.Html::button('<i class="glyphicon glyphicon-ok"></i>  OK', ['class' => 'btn btn-success', 'onclick' => 'addArrivalReceived($("#quantity").val(),$("#usd").val(),$("#price").val(),$("#id").val())']).'</div>';

    Modal::end();
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [
            'style' => 'width:800px;cursor:pointer',
            'class' => 'table-rena table-rena2',

        ],
        'pjax' =>true,
        'hover'=>true,
        'striped' =>true,
        'rowOptions' =>
            function ($dataProvider, $key, $index, $grid) {
                return ['id' => $dataProvider['id_product'],

                   //'value' => Url::to(['arrival/add']),
                    'onClick'=>'addTransfer(this.id)'
                ];
            },

        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            [
                'attribute' =>'name_product',
                'label' =>'Malın adı',
                //'filter' => $productList,
                'value' => 'product.name',
                'format'=>'raw',
                'group' => 'true',

                // 'groupedRow'=>true,
                'width' => '300px',
                //'filterInputOptions' => ['placeholder' => 'Any type']
            ],
            [
                'attribute' =>'type',
                // 'value' => 'type.name',
                'filter' => $typeList,
                'value' => 'type.name',
                'format'=>'raw',



                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true]
                ],
                'filterType' => GridView::FILTER_SELECT2,
                'width' => '200px',
                'filterInputOptions' => ['placeholder' => 'Any type']
            ],
			[
			
				'attribute'=>'barcode',
				'value'=>'nameBarcode'
			],
            [
                'attribute' =>'rest',
                'label'=>'Anbarda <br> sayı',
              	'value' => function ($model, $index, $widget) {
                    return round($model->rest,4);
					},
                'format'=>'raw',
                'width' =>'30px',
                'encodeLabel' => false,
                'footer' => $rest_sum,
                // 'pageSummary' => true,
            ],


            [
                'attribute' =>  'id_store',
                'label' => 'Filial',
                'value' =>'idStore.name',
                'width' =>'80px',
                'filter' => $storeList,
                // 'footer' => $sum_sumsell,
            ],

        ],
    ]); ?>

</div>
