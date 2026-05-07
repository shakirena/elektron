<?php

use app\models\Contractor;
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
    $contractorList = ArrayHelper::map(Contractor::find()->all(), 'id', 'name');
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
<?php 
echo HTML::a("<i class='glyphicon glyphicon-arrow-left'></i>",['index2'],['class' => 'btn btn-danger']);
?>
<br>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [
            'style' => 'width:100%;cursor:pointer',
            'class' => 'table-rena table-rena2',
        ],
        'pjax' =>true,
        'hover'=>true,
        'striped' =>true,
        'rowOptions' =>
            function ($dataProvider, $key, $index, $grid) {
                if ($dataProvider['rest']) $rest=$dataProvider['rest'];
                else $rest=0;
				
                return ['id' => $dataProvider['id_product'],
	
                   //'value' => Url::to(['arrival/add']),
                    'onClick'=>'addSell(this.id,'.$rest.','.$dataProvider["pack"].')'
                ];
            },

        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            [
                'attribute' =>'name_product',
                // 'label' =>'Malın adı',
                //'filter' => $productList,
                'value' => 'product.name',
                'format'=>'raw',
				'subGroupOf'=>1,
                'group'=>true,
                //'filterInputOptions' => ['placeholder' => 'Any type']
            ],
          
            [
                'attribute' =>'rest',
                'label'=>'Anbarda <br> sayı',
                'value' => function ($model, $index, $widget) {
                    return round($model->rest,4) ;
                },
                'format'=>'raw',
                'encodeLabel' => false,
                'footer' => $rest_sum,
				'filter'=>false,
                // 'pageSummary' => true,
            ],

        
           
			[
               
                'label' => 'Qiymeti',
                'value' =>'priceSell',
            ],
            [  
				'attribute' => 'id_store',
				'label' => 'Anbar',
                'value' => 'idStore.name',
                'filter' => $storeList,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'filterInputOptions' =>['placeholder'=>'Any ']
            ],
			

        ],
    ]); ?>

</div>
<?php
$script = <<< JS

 
$(document).on('pjax:success', function() {
  $(".table-rena2").removeClass("kv-table-wrap" );
 
});
	$(".table-rena2").removeClass("kv-table-wrap" );
  


JS;
$this->registerJs($script);
?>