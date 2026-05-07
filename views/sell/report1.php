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
<div class="arrival-index">



    <div class="btn-group" id="div1" style="width: 150px">
        <?= Select2::widget([
            'data' =>  ArrayHelper::map(TypeProduct::find()->where(['id_parent' =>0])->all(), 'id', 'name'),
            'name' => 'type1',
            'options' => [
                'placeholder' => 'Seçin',
                'onchange' => 'changeType1($("#type1").val())',
                'id'=>'type1',

            ]
        ]); ?>
    </div>
    <div class="btn-group" id="div2" style="width: 150px; display:none">
        <?= Select2::widget([
            'name' => 'type2',
            'options' => [
                'placeholder' => 'Seçin',
                'onchange' => 'changeType2($("#type2").val())',
                'id'=>'type2',


            ]
        ]); ?>
    </div>
    <div class="btn-group" id="div3" style="width: 150px;display:none">
        <?= Select2::widget([
            'name' => 'type3',
            'options' => [
                'placeholder' => 'Seçin',
                'onchange' => 'changeType3($("#type3").val())',
                'id'=>'type3',

            ]
        ]); ?>
    </div>

    <div class="btn-group" id="div4" style="width: 150px;display:none">
        <?= Select2::widget([
            'name' => 'type4',
            'options' => [
                'placeholder' => 'Seçin',
                'onchange' => 'changeType4($("#type4").val())',
                'id'=>'type4',

            ]
        ]); ?>
    </div>
    <div class="btn-group" id="div5" style="width: 150px;display:none">
        <?= Select2::widget([
            'name' => 'type5',
            'options' => [
                'placeholder' => 'Seçin',
                'onchange' => 'changeType4($("#type5").val())',
                'id'=>'type5',

            ]
        ]); ?>
    </div>
    <div class="btn-group" id="div6" style="width: 350px;display:none">
        <?= Select2::widget([
            'name' => 'product',
            'options' => [
                'placeholder' => 'Seçin',
                'id'=>'product',
                'multiple'=>true,
            ]
        ]); ?>
    </div>

    <div class="btn-group">
        <?= Html::button('<i class="glyphicon glyphicon-ok"></i>  OK', ['class' => 'btn btn-success', 'onclick' =>"document.location.replace('table?type1='+$('#type1').val()+'&type2='+$('#type2').val()+'&type3='+$('#type3').val()+'&type4='+$('#type4').val()+'&type5='+$('#type5').val()+'&product='+$('#product').val()+'&date1= $searchModel->date_start&date2=$searchModel->date_end')"]); //?>

    </div>
    <br> <br>
    <?php $clientList = ArrayHelper::map(Client::find()->all(), 'id_client', 'fio'); ?>
    <?php $user = ArrayHelper::map(Users::find()->all(), 'id_user', 'fio'); ?>
    <?php $typeList = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name'); ?>
    <?php $storeList = ArrayHelper::map(Store::find()->all(), 'id', 'name'); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'tableOptions' => [

            'class' => 'table-rena table-rena2',
            'style' => 'font-size:9pt'

        ],

        //  'pjax'=>true,
        'striped'=>true,
        'hover'=>true,
        'panel'=>['type'=>'primary'],
        'showPageSummary' => true,
        'rowOptions' =>
            function ($dataProvider, $key, $index, $grid) {
                return ['id' => $dataProvider['id']
                ];
            },
        'showFooter' => true,
        'footerRowOptions' => ['style' => 'font-weight:bold;text-decoration:underline;color:red;'],

        'columns' => [

            //   ['class'=>'kartik\grid\SerialColumn'],
            [
                'label'=>'№',
                'value' =>  function ($model, $key, $index, $column) use (&$i,&$num) {
                    if($model->number!=$num) {$i++; $num=$model->number; }
                    return $i;},
                'group'=>true,

                'subGroupOf'=>2,
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

            [
                'attribute' => 'number',
                'format'=>'raw',
                'value' => 'getNumberSell',
                'group'=>true,

                'groupHeader' =>function ($model, $index, $widget) {
                    return [
                        'mergeColumns' => [[1,5]],
                        'content' =>[
                            1=> ' Summary (sell note №'.$model->number.')',
                            6=>GridView::F_SUM,
                            8=>GridView::F_SUM,
                            9=>GridView::F_SUM,
                            //  9=>GridView::F_SUM,
                            // 6=>GridView::F_SUM,

                            //  11=>$model->getTest(),
                            //  12=>$model->getTest1(),
                            11=>$model->getTest(),
                        ],
                        'contentFormats' => [

                            6=> ['format'=>'number','decimals'=>2],
                            8=> ['format'=>'number','decimals'=>2],
                            9=> ['format'=>'number','decimals'=>2],
                            11=> ['format'=>'number','decimals'=>2],
                            12=> ['format'=>'number','decimals'=>2],
                            13=> ['format'=>'number','decimals'=>2]
                        ],
                        'contentOptions' => [
                            1 => ['style' => 'font-variant:small-caps'],
                            5 => ['style' => 'text-align:right'],
                            7 => ['style' => 'text-align:right'],
                            9 => ['style' => 'text-align:right'],
                            8 => ['style' => 'text-align:right'],

                            10 => ['style' => 'text-align:right'],

                        ],
                        'options' => ['class' => 'danger','style'=> 'font-weight:bold']
                    ];
                },

            ],


            [
                'attribute' => 'datetime',
                'label' => 'Gəbul tarixi',
                'format'=>'raw',
                'value' => 'datetime',
                'width' => '150px',

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
                'format'=>'raw',
                'pageSummary' => 'Yekun',
                'pageSummaryOptions' => ['class' => 'text-right text-danger'],
                'width' => '550px',

            ],
            [
                'attribute' => 'barcode',
                'value' => 'nameBarcode',
                'width' => '200px',
            ],

            [
                'attribute' => 'quantity',
                'label' =>'Miqdar',
                'format'=>'raw',
                'value' => 'quantity',
                'width' =>'100px',
                'hAlign' => 'right',
                'footer' => $searchModel->getSumArrival($dataProvider->query,'quantity')

            ],

            [
                'attribute' =>  'price',
                'label' => 'Satış <br> qiyməti',
                'format'=>'raw',
                'value' => 'price',
                'width' =>'100px',
                'encodeLabel' => false,

                'hAlign' => 'right',
            ],
            [
                'attribute' => 'sum',
                'value' =>'sum',
                'width' =>'100px',
                'hAlign' => 'right',
                'footer' => $searchModel->getSumArrival($dataProvider->query,'sum')
            ],
            [
                'attribute' => 'earnings',
                'label'=>'Mənfəət',
                //'value' =>'sum',
                'width' =>'100px',
                'hAlign' => 'right',
                'footer' => $searchModel->getSumArrival($dataProvider->query,'sum')
            ],

            [   'attribute' => 'name_client',
                'label' => 'Müştəri',
                'value' => 'idClient.fio',

                'width' => '400px',

            ],

            [
                'label' => 'Qalıq borc',
                'width' => '100px',
                'footer' => $searchModel->getSumBorc($dataProvider->query)

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
                'filterInputOptions' =>['placeholder'=>'Any type']
            ],

            /* [   'attribute' => 'type',
                 'value' => 'idType'
             ],*/


            // 'received',


        ],
    ]); ?>

  </div>
