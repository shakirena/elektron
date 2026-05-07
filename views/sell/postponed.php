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
use app\models\Store;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Arrivals';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div >





    <br> <br>
    <?php $user = ArrayHelper::map(Users::find()->all(), 'id_user', 'fio'); ?>
    <?php $typeList = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name'); ?>
    <?php $storeList = ArrayHelper::map(Store::find()->all(), 'id', 'name'); ?>
    <?php
    Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'postponed-modal',
            'tabindex' => true,


        ],
        'size' => 'modal-rena-lg',



    ]);

    echo '<div id="postponedContent"></div>';

    Modal::end();
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,


        // 'pjax'=>true,
        'striped'=>true,
        'hover'=>true,
        'tableOptions' => [

            'class' => 'table-rena table-rena2',
            'style' => 'font-size:9pt;width:1200px'

        ],

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
                'value' => 'getNumberPost',
                'width' => '100px',
                'group'=>true,
                'filter' => true,
                'groupHeader' =>function ($model, $index, $widget) {
                    return [
                        'mergeColumns' => [[1,4]],
                        'content' =>[
                            1=> ' Summary (sell note №'.$model->number.')',
                            5=>GridView::F_SUM,
                            6=>GridView::F_SUM,
                          
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
			[   'attribute' => 'user_issue',
                'label' => 'Satıcı',
                'value' => 'userIssue.fio',
                'filter' => $user,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '400px',
                'filterInputOptions' =>['placeholder'=>'Hər hansi']
            ],
			[
                'attribute' =>'name_product',
                'label' =>'Malın adı',
                //'filter' => $productList,
                'value' => 'idProduct.name',
                'pageSummary' => 'Yekun',
                'pageSummaryOptions' => ['class' => 'text-right text-danger'],
                'width' => '650px',

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
			[
                'attribute' => 'datetime',
                'format'=>'raw',
                'label' => 'Tarixi',
                'value' => 'datetime','width' => '150px',

                'filter' =>DatePicker::widget([
                    //,

                    'model' => $searchModel,
                    'attribute' => 'date_start',
                 //   'value' => date('Y-m-d'),
                    //'options' => ['placeholder' => 'Select issue date ...'],
                    'type' => DatePicker::TYPE_RANGE,
                    'attribute2' => 'date_end',
                  //  'value2' => date('Y-m-d'),
                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd',
                        'autoClose' => true
                        // 'todayHighlight' => false
                    ]
                ]),

                // 'group'=>true,

            ],
            [   'attribute' => 'name_client',
                'label' => 'Müştəri',
                'value' => 'idClient.fio',
             //   'filter' => $clientList,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
              //  'filterType' =>GridView::FILTER_SELECT2,
                'width' => '400px',
              //  'filterInputOptions' =>['placeholder'=>'Any type']
            ],
        

            [   'attribute' => 'id_store',
                'label' => 'Filial',
                'value' => 'idStore.name',
                'filter' => $storeList,
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

</div>
