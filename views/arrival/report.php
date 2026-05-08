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
    <?php $contractorList = ArrayHelper::map(Contractor::find()->andWhere("id>1")->all(), 'id', 'name'); ?>
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
                'attribute' => 'getNumber',
                'format'=>'raw',
                // 'value' => 'NumberString',
                'group'=>true,
                'filter' => false,
                // 'groupedRow'=>true,                    // move grouped column to a single grouped row
                // 'groupOddCssClass'=>'kv-grouped-row',  // configure odd group cell css class
                //   'groupEvenCssClass'=>'kv-grouped-row', // configure even group cell css class
                'groupHeader' =>function ($model, $index, $widget) {
                    return [
                        'mergeColumns' => [[1,3]],
                        'content' =>[
                            1=> ' Summary (Inbound (goods) delivery note №'.$model->number.')',
                           
                            5=>GridView::F_SUM,
                            6=>GridView::F_SUM,
							7=>GridView::F_SUM,
							10=>GridView::F_SUM,


                        ],
                        'contentFormats' => [
                            4=> ['format'=>'number','decimals'=>2],

                            5=> ['format'=>'number','decimals'=>2],

                            6=> ['format'=>'number','decimals'=>2],
							7=> ['format'=>'number','decimals'=>2],
							10=> ['format'=>'number','decimals'=>2],
                        ],
						
                        'contentOptions' => [
                            1 => ['style' => 'font-variant:small-caps'],
                            4 => ['style' => 'text-align:right'],
                            5 => ['style' => 'text-align:right'],
                            6 => ['style' => 'text-align:right'],
                            7 => ['style' => 'text-align:right'],
                            8 => ['style' => 'text-align:right'],
                        ],
                        'options' => ['class' => 'danger','style'=> 'font-weight:bold']
                    ];
                },

            ],
            [
                'attribute' => 'datetime',
                'format'=>'raw',
                'label' => 'Gəbul tarixi',
                'value' => 'datetime','width' => '250px',

                'filter' =>DatePicker::widget([
                    //,

                    'model' => $searchModel,
                    'attribute' => 'date_start',
                    'value' => date('Y-m-d'),
                    //'options' => ['placeholder' => 'Select issue date ...'],
                    'type' => DatePicker::TYPE_RANGE,
                    'attribute2' => 'date_end',
                    'value2' => date('Y-m-d'),
                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd',
                        'autoClose' => true
                        // 'todayHighlight' => false
                    ]
                ]),

                // 'group'=>true,

            ],
            [
                'attribute' =>'name_product',
                'value' => 'idProduct.name',
                'pageSummary' => 'Yekun',
                'pageSummaryOptions' => ['class' => 'text-right text-danger'],
                'width' => '550px',

            ],
			 [
			'attribute'=>'barcode',
			'value'=>'nameBarcode'
		],
            [
                'label'  => 'Artikul nomresi',
                'value'  => 'idProduct.article_number',
                'format' => 'raw',
                'width'  => '120px',
            ],
            [
                'attribute' => 'quantity',
                'label' =>'Miqdar',
                'format'=>'raw',
                'value' => 'quantity',
                'width' =>'100px',
                'hAlign' => 'right',
                'footer' =>round( $searchModel->getSumArrival($dataProvider->query,'quantity'),2)
            ],
            [
                'attribute' =>  'price',
                'label' =>'Qiyməti',
                'format'=>'raw',
                'value' => 'price',
                'width' =>'100px',

                'hAlign' => 'right',

            ],
            [
                'attribute' => 'sum',
                'value' =>'sum',
                'width' =>'100px',
                'encodeLabel' => false,
                'pageSummary' => true,
                'hAlign' => 'right',
                'footer' =>round( $searchModel->getSumArrival($dataProvider->query,'sum'),2)
            ],
			[
                'label' =>  'Topdan sat. <br> (azn) ',
                'format'=>'raw',
                'value' => 'trade_price',
                'width' =>'30px',
                'encodeLabel' => false,
                //  'footer' => round($searchModel->getSumRest($dataProvider->query,"sum"),2)

            ],
            [
                'attribute' =>  'pricesell ',
                'label' =>'Satış <br> qiyməti',
                'format'=>'raw',
                'value' => 'pricesell',
                'width' =>'100px',
              
				'encodeLabel' => false,
                'pageSummary' => true,
                'hAlign' => 'right',
            ],
           [
                'label' => 'Cəmi<br>satış qiym.',
                'value' =>'sumUsd',
                'width' =>'100px',
                'encodeLabel' => false,
                'pageSummary' => true,
                'hAlign' => 'right',
                'footer' => $searchModel->getSumArrival($dataProvider->query,'pricesell')
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
                'filter' => $typeList,
                'value' => 'idProduct.idType.name',
                    'filterWidgetOptions' =>[
                        'pluginOptions'=>['allowClear'=>true]
                    ],
                    'filterType' =>GridView::FILTER_SELECT2,
                    'width' => '200px',
                    'filterInputOptions' =>['placeholder'=>'Any ']


            ],
			
            [   'attribute' => 'id_store',
				 'label' => 'Filial',

                'value' => 'idStore.name',
                'filter' => $storeList,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '200px',
                'filterInputOptions' =>['placeholder'=>'Any ']
            ],
      
          /*  [
                'label' => 'Return',
                'value' => function ($model, $index, $widget) {
                    return Html::button("Qaytarmaq", ["onclick" => "returnArrival2($model->number)", 'class' => 'btn btn-danger']);
                },
                'format' => 'raw',
                'group' => true,
                'subGroupOf'=>1,

            ]*/
        ],
    ]); ?>
    <!-- <?php Pjax::end(); ?>-->
  </div>
