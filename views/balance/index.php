<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use app\models\Client;
use app\models\TypeProduct;
use app\models\Users;
use app\models\Store;
use kartik\select2\Select2;
use app\models\TypeBalance;
/* @var $this yii\web\View */
/* @var $searchModel app\models\BalanceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


?>
<div class="balance-index" style="float: left">
    <?php $user = ArrayHelper::map(Users::find()->all(), 'id_user', 'fio'); ?>
    <?php $type = ArrayHelper::map(TypeBalance::find()->all(), 'id', 'name'); ?>
    <?php $store = ArrayHelper::map(Store::find()->all(), 'id', 'name'); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [
            'style'=>'width:1100px',
            'class' => 'table-rena table-rena2 text-right',

        ],
       // 'showPageSummary' => true,
        'striped' => false,
        'hover' => true,
       // 'pageSummaryRowOptions' => ['class' => 'text-right danger'],
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],


            [
                'attribute' => 'datetime',
                'label' => 'Tarixi',
                'format'=>'raw',
                'group'=>true,
            //   'groupedRow' =>true,
             //   'groupOddCssClass' => 'kv-grouped-row',
             //   'groupEvenCssClass' => 'kv-grouped-row',
                'value' => 'datetime',
                'width' => '150px',
                'filterWidgetOptions' =>[
                  'pluginOptions' => ['allowClear' => true]
                ],
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
                    'groupHeader' =>function ($model, $index, $widget) {
                            return [
                                'mergeColumns' => [[1,8]],
                                'content' =>[
                                    1=>  $model->datetime,

                                ],
                                'contentFormats' => [
                                    4=> ['format'=>'number','decimals'=>2],
                                    5=> ['format'=>'number','decimals'=>2],
                                    6=> ['format'=>'number','decimals'=>2],
                                    7=> ['format'=>'number','decimals'=>2],
                                    9=> ['format'=>'number','decimals'=>2],
                                ],
                                'contentOptions' => [
                                    1 => ['style' => 'font-variant:small-caps'],

                                ],
                                'options' => ['class' => 'success','style'=> 'font-weight:bold']
                            ];
                        },
                // 'group'=>true,

            ],
            [   'attribute' => 'id_user',
                'label' => 'Satıcı',
                'value' => 'idUser.fio',
                'filter' => $user,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '150px',
                'filterInputOptions' =>['placeholder'=>'Hər hansi'],
                //'pageSummary' => 'Yekun',
                //'pageSummaryOptions' => ['class' => 'text-right text-danger'],
            ],
            [
                'attribute'=>'id_store',
                'value'=>'idStore.name',
                'filter' => $store,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'filterInputOptions' =>['placeholder'=>'Hər hansi'],

            ],
            [   'attribute' => 'type',
                'value' => 'type',
               'group' => true,
              'subGroupOf' =>1,
                'filter' => ["1" =>"Məxaric",'0'=>'Mədaxil'],

                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,

                'filterInputOptions' =>['placeholder'=>'Hər hansi'],
               'groupFooter' =>function ($model, $index, $widget) {
                    return [
                        'mergeColumns' => [[2,5]],
                        'content' =>[
                            3=> ' Məbləğ('.$model->type.')',
                            7=>GridView::F_SUM,

                        ],
                        'contentFormats' => [
                            7=> ['format'=>'number','decimals'=>2],

                        ],
                        'contentOptions' => [
                            2 => ['style' => 'font-variant:small-caps;font-weight:bold','class' => 'danger'],
                            6 => ['style' => 'font-variant:small-caps;font-weight:bold','class' => 'danger'],
                            7 => ['style' => 'font-variant:small-caps','class' => 'danger'],
                            8 => ['style' => 'font-variant:small-caps','class' => 'danger'],
                            9 => ['style' => 'font-variant:small-caps','class' => 'danger'],

                        ],
                       // 'options' => ['class' => 'danger','style'=> 'font-weight:bold']
                    ];
                },
            ],
            [
                'attribute' =>    'id_type',
                'value' => 'nameType',
'group'=>true,
                'filter' => $type,
                'subGroupOf'=>2,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '150px',
                'filterInputOptions' =>['placeholder'=>'Hər hansi'],

        /*  'groupFooter' =>true /*function ($model, $index, $widget) {
                    return [
                        'mergeColumns' => [[1,4]],
                        'content' =>[
                            1=> ' Summary ('.$model->getIdType()->one()->name.')',
                           // 7=>GridView::F_SUM,

                        ],
                        'contentFormats' => [
                           // 7=> ['format'=>'number','decimals'=>2],

                        ],
                        'contentOptions' => [
                           // 2 => ['style' => 'font-variant:small-caps;font-weight:bold','class' => 'danger'],
                           // 6 => ['style' => 'font-variant:small-caps;font-weight:bold','class' => 'danger'],
                           // 7 => ['style' => 'font-variant:small-caps','class' => 'danger'],
                           // 8 => ['style' => 'font-variant:small-caps','class' => 'danger'],

                        ],
                         'options' => ['class' => 'danger','style'=> 'font-weight:bold']
                    ];
                },*/
            ],

           // 'current_sum',

            [
                'attribute'=>'note',
                'value' =>'noteString',
                'format' => 'raw',
            ],

            [
                'attribute' => 'sum',
                'value' =>'sum',
               // 'pageSummary' => true,
                'width' => '50px',
            ],
            'user_name',
            ['class' => 'kartik\grid\ActionColumn'],
        ],
    ]); ?>


</div>
<div style="width: 200px;margin-left:1000px;margin-top: 250px ">
    Mədaxil: <?= $prixod?><br>
    Məxaric: <?= $rasxod?>

    <br> Qalıq: <?= $ostatok?>
</div>