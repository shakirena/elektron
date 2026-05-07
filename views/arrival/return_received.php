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
/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Arrivals';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="arrival-index">


    <?php $contractorList = ArrayHelper::map(Contractor::find()->all(), 'id', 'name'); ?>
    <?php $typeList = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name'); ?>
    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,

        'filterModel' => $searchModel,
        //'pjax'=>true,
        'striped'=>true,
        'hover'=>true,

        'panel'=>['type'=>'primary'],
        'rowOptions' =>
            function ($dataProvider, $key, $index, $grid) {
                return ['id' => $dataProvider['id']
                ];
            },

        'columns' => [
            ['class'=>'kartik\grid\SerialColumn'],

            [
                'attribute' => 'number',
                'format'=>'raw',
                'value' => 'number',
                'group'=>true,
                'filter' => false,
                'groupedRow'=>true,                    // move grouped column to a single grouped row
                'groupOddCssClass'=>'kv-grouped-row',  // configure odd group cell css class
                'groupEvenCssClass'=>'kv-grouped-row', // configure even group cell css class
            ],
            [
                'attribute' => 'datetime',
                'format'=>'raw',
                'value' => 'datetime','width' => '200px',

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
                'attribute' =>'id_product',
                'label' =>'Malın adı',
                //'filter' => $productList,
                'value' => 'idProduct.name',

                'width' => '200px',
                'filterInputOptions' => ['placeholder' => 'Any type']
            ],
           [
                'attribute' =>'type',
              // 'value' => 'type.name',
               'filter' => $typeList,
               'value' => 'type.name',
               'format'=>'raw',

               'width' =>'100px',
               'filterWidgetOptions' => [
                   'pluginOptions' => ['allowClear' => true]
               ],
               'filterType' => GridView::FILTER_SELECT2,
               'width' => '200px',
               'filterInputOptions' => ['placeholder' => 'Any type']
            ],
            [
                'attribute' => 'quantity',
                'format'=>'raw',
                'value' => 'quantity',
                'width' =>'100px'
            ],
            [
                'attribute' =>  'price',
                'format'=>'raw',
                'value' => 'price',
                'width' =>'100px'
            ],
           [
               'attribute' => 'sum',
               'value' =>'sum',
               'width' =>'100px'
           ],

           [   'attribute' => 'id_contr',
            'value' => 'idContr.name',
            'filter' => $contractorList,
            'filterWidgetOptions' =>[
                'pluginOptions'=>['allowClear'=>true]
            ],
            'filterType' =>GridView::FILTER_SELECT2,
            'width' => '400px',
            'filterInputOptions' =>['placeholder'=>'Any type']
        ],
            //'id_user',
            // 'received',


        ],
    ]); ?>
    <?php Pjax::end(); ?>
  </div>
