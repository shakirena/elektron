<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;
use \app\models\Store;
use \app\models\TypeProduct;
use yii\widgets\Pjax;
use kartik\date\DatePicker;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Transfers';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transfer-index">


    <?php $storeList = ArrayHelper::map(Store::find()->all(), 'id', 'name'); ?>
    <?php $typeList = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name');

    ?>
   
<br><br>
    <?php Pjax::begin(['id' => 'grid-arrival']) ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        //'pjax'=>true,
        'striped'=>true,
        'hover'=>true,
        'showFooter' => true,
        'footerRowOptions' => ['style' => 'font-weight:bold;text-decoration:underline;color:red;'],
        'panel'=>['type'=>'primary'],
        'tableOptions' => [

            'class' => 'table-rena table-rena2',
            'style' => 'font-size:9pt'

        ],
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
                'value' => 'numberGet',
                'group'=>true,
				 'groupHeader' =>function ($model, $index, $widget) {
                    return [
                        'mergeColumns' => [[1,4]],
                        'content' =>[
                            1=> ' Summary (sell note №'.$model->number.')',
                            5=>GridView::F_SUM,
                          

                        ],
                        'contentFormats' => [

                            5=> ['format'=>'number','decimals'=>2],
                         
                           
                        ],
                        'contentOptions' => [
                            1 => ['style' => 'font-variant:small-caps'],
                            5 => ['style' => 'text-align:right'],
                           

                        ],
                        'options' => ['class' => 'danger','style'=> 'font-weight:bold']
                    ];
                },
                ],
			[   'attribute' => 'id_user',
                'label' =>'User',
                'value' => 'idUser.fio',
                'filter' => $user,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '400px',
                'filterInputOptions' =>['placeholder'=>'Any type']
            ],
         
            [
                'attribute' => 'date',
                'format'=>'raw',
                'label' => 'Gəbul tarixi',
                'value' => 'date','width' => '150px',

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
                'label' =>'Malın adı',
               
                'value' => 'getType',

                'width' => '680px',
                'pageSummary' => 'Yekun',
                'pageSummaryOptions' => ['class' => 'text-right text-danger']
            ],
         /*  [
                'attribute' =>'type',
              // 'value' => 'type.name',
               'filter' => $typeList,
               'value' => 'type.name',
               'format'=>'raw',

               'width' =>'80px',
               'filterWidgetOptions' => [
                   'pluginOptions' => ['allowClear' => true]
               ],
               'filterType' => GridView::FILTER_SELECT2,
               'filterInputOptions' => ['placeholder' => 'Any type'],

            ],*/
            [
                'attribute' => 'quantity',
                'label' =>'Miqdar',
                'format'=>'raw',
                'value' => 'quantity',
                'width' =>'100px',
               // 'pageSummary' => true,
                'hAlign' => 'right',
                'footer' => $searchModel->getSum($dataProvider->query,'quantity')
            ],
            [
                'attribute' => 'whence',
                'label' =>'Hardan',
                'format'=>'raw',
                'value' => 'whence0.name',
                'width' =>'100px',
                'filter' => $storeList,

            ],
            [
                'attribute' => 'whered',
                'label' =>'Hara',
                'format'=>'raw',
                'value' => 'whered0.name',
                'width' =>'100px',
                'filter' => $storeList,

            ],


        ],
    ]); ?>
    <?php Pjax::end();?>
  </div>
