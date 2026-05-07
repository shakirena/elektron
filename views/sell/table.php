<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use app\models\Client;
use app\models\TypeProduct;
use app\models\Users;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Arrivals';
//$this->params['breadcrumbs'][] = $this->title;
?>

    <?php $clientList = ArrayHelper::map(Client::find()->all(), 'id_client', 'fio'); ?>
    <?php $user = ArrayHelper::map(Users::find()->all(), 'id_user', 'fio'); ?>
    <?php $typeList = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name'); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax'=>true,
        'striped'=>true,
        'hover'=>true,
        'panel'=>['type'=>'primary'],
        'showPageSummary' => true,
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
                'groupHeader' =>function ($model, $index, $widget) {
                    return [
                        'mergeColumns' => [[1,4]],
                        'content' =>[
                            1=> ' Summary (sell note №'.$model->number.')',
                            5=>GridView::F_SUM,
                            6=>GridView::F_SUM,
                            7=>GridView::F_SUM,
                        ],
                        'contentFormats' => [
                            5=> ['format'=>'number','decimals'=>0],
                            6=> ['format'=>'number','decimals'=>2],
                            7=> ['format'=>'number','decimals'=>2],
                        ],
                        'contentOptions' => [
                            1 => ['style' => 'font-variant:small-caps'],
                            5 => ['style' => 'text-align:right'],
                            6 => ['style' => 'text-align:right'],
                            7 => ['style' => 'text-align:right'],
                        ],
                        'options' => ['class' => 'danger','style'=> 'font-weight:bold']
                    ];
                },

            ],
            [
                'attribute' => 'datetime',
                'label' => 'Gəbul tarixi',
                'format'=>'raw',
                'value' => 'datetime','width' => '350px',

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
                'pageSummary' => 'Yekun',
                'pageSummaryOptions' => ['class' => 'text-right text-danger'],
                'width' => '250px',

            ],
           [
                'attribute' =>'type',
              // 'value' => 'type.name',

               'value' => 'getType',
               'format'=>'raw',

               'width' =>'100px',

            ],
            [
                'attribute' => 'quantity',
                'label' =>'Miqdar',
                'format'=>'raw',
                'value' => 'quantity',
                'width' =>'100px',
                'pageSummary' => true,
                'hAlign' => 'right',

            ],
            [
                'attribute' =>  'price',
                'label' => 'Satış <br> qiyməti',
                'format'=>'raw',
                'value' => 'price',
                'width' =>'100px',
                'encodeLabel' => false,
                'pageSummary' => true,
                'hAlign' => 'right',
            ],
           [
               'attribute' => 'sum',
               'value' =>'sum',
               'width' =>'100px',
               'hAlign' => 'right',
               'pageSummary' => true,

           ],

          [   'attribute' => 'id_client',
              'label' => 'Müştəri',
            'value' => 'idClient.fio',
            'filter' => $clientList,
            'filterWidgetOptions' =>[
                'pluginOptions'=>['allowClear'=>true]
            ],
            'filterType' =>GridView::FILTER_SELECT2,
            'width' => '400px',
            'filterInputOptions' =>['placeholder'=>'Any type']
        ],
            [   'attribute' => 'id_user',
                'label' => 'Satıcı',
                'value' => 'idUser.fio',
                'filter' => $user,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '400px',
                'filterInputOptions' =>['placeholder'=>'Hər hansi']
            ]
            ,
            // 'received',


        ],
    ]); ?>

