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
                'attribute' => 'id',
                'format'=>'raw',
                // 'value' => 'NumberString',
              
                'filter' => false,

            ],
            [
                'attribute' => 'date',
                'format'=>'raw',
                'label' => 'Gəbul tarixi',
                'value' => 'date','width' => '250px',

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
                'footer' =>round( $searchModel->getSumArrival($dataProvider->query,'price'),2)
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
            [   'attribute' => 'id_store',
                'label' => 'Filial',
                'value' => 'idStore.name',
                'filter' => $storeList,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '100px',
                'filterInputOptions' =>['placeholder'=>'Hər hansi']
            ]
        ],
    ]); ?>
    <!-- <?php Pjax::end(); ?>-->
  </div>
