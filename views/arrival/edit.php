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

    <?php $typeList = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name'); ?>
    <?php $storeList = ArrayHelper::map(Store::find()->all(), 'id', 'name'); ?>
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
                'value' => 'idProduct.name',
                'format'=>'raw',


               'group'=>true,
                'width' => '300px',
                //'filterInputOptions' => ['placeholder' => 'Any type']
            ],
          
			  [ 
				'attribute' =>'type_name',
                'label' =>'Grupa',
				'value' => 'nameType',
                'filter' => $typeList,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '200px',
                'filterInputOptions' =>['placeholder'=>'Any ']
            ],
            [
                'attribute' =>'rest',
                'label'=>'Anbarda <br> sayı',
               'value' => function ($model, $index, $widget) {
                    return round($model->rest,2);
					},
                'format'=>'raw',
                'width' =>'30px',
                'encodeLabel' => false,
                'footer' => round($searchModel->getSumRest($dataProvider->query,'rest'),2)
               // 'pageSummary' => true,
            ],
           /* [

                'label' => 'Gözləmədə',
                'value' =>'postponed',
                'width' =>'80px',
                'footer' =>$searchModel->getSumPostponed($dataProvider->query)
                // 'filter' => $storeList,
                // 'footer' => $sum_sumsell,
            ],*/
			 [
                'label' =>  'Topdan sat. <br> (azn) ',
                'format'=>'raw',
                'value' => 'trade_price',
                'width' =>'30px',
                'encodeLabel' => false,
                //  'footer' => round($searchModel->getSumRest($dataProvider->query,"sum"),2)

            ],

            [
                'attribute' =>  'price ',
                'format'=>'raw',
                'value' => 'priceRest',
                'width' =>'30px',
                'label' => 'Alış<br>Qiyməti',
                'encodeLabel' => false,
                //'footer' => round($searchModel->getSumRest($dataProvider->query,"sum"),2)

            ],
			
			[   'attribute' =>   'polka',
                'encodeLabel' => false,
                'format' => 'raw',
				 'width' =>'40px',
                
                
            ],
   

            [   'attribute' =>    'price_top',
                'encodeLabel' => false,
                'format' => 'raw',
				 'width' =>'40px',
             
            ],
            [   'attribute' =>    'pack',
                'encodeLabel' => false,
                'format' => 'raw',
				 'width' =>'40px',
             
            ],
            [
                'attribute' =>  'id_store',
                'label' => 'Filial',
                'value' =>'idStore.name',
                'width' =>'80px',
                'filter' => $storeList,
               // 'footer' => $sum_sumsell,
            ],

            //'id_user',
            // 'received',
            ['class' => 'kartik\grid\ActionColumn'],

        ],
    ]); ?>
    <!-- <?php Pjax::end(); ?>-->
  </div>
