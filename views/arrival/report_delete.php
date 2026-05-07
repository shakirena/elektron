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
    <?php $contractorList = ArrayHelper::map(Contractor::find()->all(), 'id', 'name'); ?>
    <?php $typeList = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name');
    $dataProvider->pagination=false;
    ?>
    <?php $storeList = ArrayHelper::map(Store::find()->all(), 'id', 'name'); ?>



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
                'attribute' => 'number',
                'format'=>'raw',
               // 'value' => 'NumberString',
                'group'=>true,
                'filter' => false,
               // 'groupedRow'=>true,                    // move grouped column to a single grouped row
              // 'groupOddCssClass'=>'kv-grouped-row',  // configure odd group cell css class
             //   'groupEvenCssClass'=>'kv-grouped-row', // configure even group cell css class
                'groupHeader' =>function ($model, $index, $widget) {
                    return [
                        'mergeColumns' => [[1,4]],
                        'content' =>[
                            1=> ' Summary (Inbound (goods) delivery note №'.$model->number.')',
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
                'value' => 'getType',
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
                'pageSummary' => true,
                'hAlign' => 'right',
            ],
            [
                'attribute' =>  'price',
                'format'=>'raw',
                'value' => 'price',
                'width' =>'100px',
                'pageSummary' => true,
                'hAlign' => 'right',
            ],
           [
               'attribute' => 'sum',
               'value' =>'sum',
               'width' =>'100px',
               'encodeLabel' => false,
               'pageSummary' => true,
               'hAlign' => 'right',
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
            ],
            //'id_user',
       //     'returnp',


        ],
    ]); ?>

  </div>
