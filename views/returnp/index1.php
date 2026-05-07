<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\date\DatePicker;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use app\models\Client;
use app\models\Users;
use app\models\Store;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ReturnpSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


?>
<div class="returnp-index">
    <?php $clientList = ArrayHelper::map(Client::find()->all(), 'id_client', 'fio'); ?>
    <?php $user = ArrayHelper::map(Users::find()->all(), 'id_user', 'fio'); ?>
    <?php $store = ArrayHelper::map(Store::find()->all(), 'id', 'name'); ?>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'striped'=>true,
        'hover'=>true,
        'showFooter' => true,
        'tableOptions' => [

            'class' => 'table-rena table-rena2',
            'style' => 'font-size:9pt'

        ],
        'footerRowOptions' => ['style' => 'font-weight:bold;text-decoration:underline;color:red;'],

        // 'pjax'=>true,
        'panel'=>['type'=>'primary'],
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],
            [
                'attribute' => 'number',
                'format'=>'raw',
                'value' =>  'getNumber',
                'group'=>true,
                'filter' => false,
                'width' => '120px',
                // 'groupedRow'=>true,                    // move grouped column to a single grouped row
                // 'groupOddCssClass'=>'kv-grouped-row',  // configure odd group cell css class
                //   'groupEvenCssClass'=>'kv-grouped-row', // configure even group cell css class
                'groupHeader' =>function ($model, $index, $widget) {
                    return [
                        'mergeColumns' => [[1,3]],
                        'content' =>[
                            1=> ' Summary (Inbound (goods) delivery note №'.$model->number.')',
                            4=> $model->getSumNumber($model->number),

                        ],
                        'contentFormats' => [
                            4=> ['format'=>'number','decimals'=>0],

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
                'attribute' => 'data',
                'format'=>'raw',
                'value' => 'data','width' => '250px',

                'filter' =>DatePicker::widget([
                    //,

                    'model' => $searchModel,
                    'attribute' => 'date_start',
                    'type' => DatePicker::TYPE_RANGE,
                    'attribute2' => 'date_end',

                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd',
                        'autoClose' => true
                        // 'todayHighlight' => false
                    ]
                ]),
                'enableSorting' => false,
                'pageSummary' => 'Yekun',
                'pageSummaryOptions' => ['class' => 'text-right text-warning']

            ],
            [
                'attribute' =>'id_product',
                'label' =>'Malın adı',
                'value' => 'idProduct.name',
                'width' => '200px',
            ],
           [
               'attribute' => 'quantity',
               'footer' => $searchModel->getSumReturn($dataProvider->query),
           ] ,
            [
                'attribute'=> 'reason',
                'width' => '200px',
            ],
           // 'id_user',
            [   'attribute' => 'id_client',
                'value' => 'idClient.fio',
                'filter' => $clientList,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '250px',
                'filterInputOptions' =>['placeholder'=>'Any type']
            ],
            [   'attribute' => 'id_user',
                'value' => 'idUser.fio',
                'filter' => $user,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '250px',
                'filterInputOptions' =>['placeholder'=>'Any type']
            ],

           [   'attribute' => 'id_store',
                'value' => 'idStore.name',
                'filter' => $store,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '250px',
                'filterInputOptions' =>['placeholder'=>'Any type']
            ],


            // 'quantity',

        ],
    ]); ?>

</div>
