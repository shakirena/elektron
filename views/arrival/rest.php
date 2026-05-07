<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use kartik\date\DatePicker;
use kartik\dateRange\DateRangePicker;
use kartik\grid\GridView;
use app\models\Contractor;
use app\models\TypeProduct;
use app\models\Store;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Arrivals';
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="arrival-index">
  
<?php
  Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'client-modal',
            'tabindex' => true,
        ],

        //'size' => 'modal-sm',

    ]);

    echo '<div id="clientContent"></div>';

    Modal::end();
?>
    <?php $type = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name'); ?>
    <?php $storeList = ArrayHelper::map(Store::find()->all(), 'id', 'name'); ?>
    <?php $contractorList = ArrayHelper::map(Contractor::find()->all(), 'id', 'name'); ?>
	<br>
	  <div class="btn-group">
	  <?= Html::input("text",'barcode','',['id'=>'barcode','size'=>'6','style'=>'width:250px !important', 'class' => 'form-control','onChange' => "findProduct()"])?>
	  </div>
	   <div class="btn-group">
    <?= Html::button('<i class="glyphicon glyphicon-search"></i>Axtarış', [ 'class' => 'btn btn-danger', 'onClick' => "findProduct()"]) ?>
     </div> 
	  <br><br><br>
  <!--  <?php Pjax::begin(); ?>-->


    <?= GridView::widget([
        'dataProvider' => $dataProvider,

        'filterModel' => $searchModel,
        //'pjax'=>true,
        'striped'=>true,
        'hover'=>true,
        'showFooter' => true,
        'tableOptions' => [

            'class' => 'table-rena table-rena2',
            'style' => 'font-size:9pt'

        ],
        'footerRowOptions' => ['style' => 'font-weight:bold;text-decoration:underline;color:red;'],

        'panel'=>['type'=>'primary'],
        'rowOptions' =>
            function ($dataProvider, $key, $index, $grid) {
                return ['id' => $dataProvider['id']
                ];
            },

        'columns' => [
            ['class'=>'kartik\grid\SerialColumn'],

           

            [
                'attribute' =>'name_product',
                'label' =>'Malın adı',
                //'filter' => $productList,
                //'value'=>'idType',
                'value' => 'nameProduct',
                'format'=>'raw',

                'subGroupOf'=>1,
                'group'=>true,
                'width' => '300px',
                //'filterInputOptions' => ['placeholder' => 'Any type']
            ],
			[
				'attribute'=>'barcode',
				'filter'=>false,
				'value'=>'nameBarcode'
			],
            [
                'attribute' =>'rest',
                'label'=>'Anbarda <br> sayı',
                
				'value' => function ($model, $index, $widget) {
					$model->rest=round($model->rest,4);
					//if ($model->rest<=3 && $model->id_store==Yii::$app->session->get("store") )  return "<span style='background:#FFD52D'>$model->rest</span>";
					//if ($model->rest<=3)   return "<span style='background:#EEFF00'>$model->rest</span>";
                    return $model->rest;
					},
                'format'=>'raw',
                'width' =>'30px',
                'encodeLabel' => false,
                'footer' => $searchModel->getSumRest($dataProvider->query,'rest')
               // 'pageSummary' => true,
            ],

            [
                'attribute' =>  'price ',
                'format'=>'raw',
                'value' => 'priceRest',
                'width' =>'30px',
                'label' => 'Gəliş qiym.<br> (azn)',
                'encodeLabel' => false,
                //'footer' => round($searchModel->getSumRest($dataProvider->query,"sum"),2)

            ],

            [
              //  'attribute' =>  'sum',
                'format'=>'raw',

                'value' => 'priceSum',
   
                'width' =>'30px',
                'label' => 'Gəliş qiym. <br>cəmi (azn)',
                 'encodeLabel' => false,
                'footer' => round($searchModel->getSumRest($dataProvider->query,"sum"),2)

            ],
			
            [
                'label' =>  'Satış qiym. <br> (azn) ',
                'format'=>'raw',
                'value' => 'priceSell',
                'width' =>'30px',
                'encodeLabel' => false,
                //  'footer' => round($searchModel->getSumRest($dataProvider->query,"sum"),2)

            ],
			 [
                'label' =>  'Topdan sat. <br> (azn) ',
                'format'=>'raw',
                'value' => 'priceOpt',
                'width' =>'30px',
                'encodeLabel' => false,
                //  'footer' => round($searchModel->getSumRest($dataProvider->query,"sum"),2)

            ],
			
			 [
                'label' =>  'Satış qiym.  <br> cəmi (azn) ',
                'format'=>'raw',
                'value' => 'priceSellSum',
                'width' =>'30px',
                'encodeLabel' => false,
                 'footer' => round($searchModel->getSumRest($dataProvider->query,"pricesell"),2)

            ],
			  [   'attribute' => 'id_contr',

                'value' => 'idContr.name',
                'filter' => $contractorList,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '200px',
                'filterInputOptions' =>['placeholder'=>'Any ']
            ],
             [
                'attribute' =>  'type_name',
				'label' =>  'Mal qrupu ',
                'filter' => $type,
                'value' => 'idProduct.idType.name',
                    'filterWidgetOptions' =>[
                        'pluginOptions'=>['allowClear'=>true]
                    ],
                    'filterType' =>GridView::FILTER_SELECT2,
                    'width' => '200px',
                    'filterInputOptions' =>['placeholder'=>'Any ']


            ],
          
            //'id_user',
            // 'received',
            [  
				'attribute' => 'id_store',
				'label' => 'Anbar',
                'value' => 'idStore.name',
                'filter' => $storeList,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '200px',
                'filterInputOptions' =>['placeholder'=>'Any ']
            ],
			[
				'label'=>'Blok',
				'value'=>'boxing'
			
			],
			[
				'class' => 'kartik\grid\ActionColumn', 
				'template' => '{update}',
				'urlCreator' => function( $action, $model, $key, $index ){

                    if ($action == "update") {

                        return Url::to(['update', 'id' => $key]);

                    }

                }
			],
        ],
    ]); ?>
    <!-- <?php Pjax::end(); ?>-->
  </div>
