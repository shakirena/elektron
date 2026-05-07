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
        <?= Html::button('<i class="glyphicon glyphicon-ok"></i>  OK', ['class' => 'btn btn-success', 'onclick' =>"document.location.replace('table-rest?type1='+$('#type1').val()+'&type2='+$('#type2').val()+'&type3='+$('#type3').val()+'&type4='+$('#type4').val()+'&type5='+$('#type5').val()+'&product='+$('#product').val()+'&date1= $searchModel->date_start&date2=$searchModel->date_end')"]); //?>

    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,

        'filterModel' => $searchModel,
        'panel'=>['type'=>'primary'],
        //'pjax'=>true,
        'striped'=>true,
        'hover'=>true,

        'tableOptions' => [

            'class' => 'table-rena table-rena2',
            'style' => 'font-size:9pt'

        ],
        'footerRowOptions' => ['style' => 'font-weight:bold;text-decoration:underline;color:red;'],


        'rowOptions' =>
            function ($dataProvider, $key, $index, $grid) {
                return ['id' => $dataProvider['id']
                ];
            },

        'columns' => [
            ['class'=>'kartik\grid\SerialColumn'],

            [
                'attribute' => 'type_name',
                'format'=>'raw',
                'label' =>'Grupa',
                'value' => 'nameType',
                // 'value' => 'NumberString',
                'group'=>true,
                'filter' => false,
                'width' => '300px',
                // 'groupedRow'=>true,                    // move grouped column to a single grouped row
                // 'groupOddCssClass'=>'kv-grouped-row',  // configure odd group cell css class
                //   'groupEvenCssClass'=>'kv-grouped-row', // configure even group cell css class
                'groupHeader' =>function ($model, $index, $widget) {
                    return [
                        'mergeColumns' => [[1,2]],
                        'content' =>[
                            1=> ' Summary (Inbound (goods) delivery note №'.$model->number.')',
                            3=>  GridView::F_SUM,

                            4=> GridView::F_SUM,


                        ],
                        'contentFormats' => [
                            4=> ['format'=>'number','decimals'=>2],

                        ],
                        'contentOptions' => [

                            4 => ['style' => 'text-align:right'],
                        ],
                        'options' => ['class' => 'danger','style'=> 'font-weight:bold']
                    ];
                },

            ],
            [
                'attribute' =>'name_product',
                'label' =>'Malın adı',
                //'filter' => $productList,
                //'value'=>'idType',
                'value' => 'getType',
                'format'=>'raw',


                'group'=>true,
                'width' => '300px',
                //'filterInputOptions' => ['placeholder' => 'Any type']
            ],


            [
                'attribute' =>'rest',
                'label'=>'Anbarda <br> sayı',
                'value' => 'rest',
                'format'=>'raw',
                'width' =>'30px',
                'encodeLabel' => false,
                'footer' => $searchModel->getSumRest($dataProvider->query,'rest')
                // 'pageSummary' => true,
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


        ],
    ]); ?>

</div>
