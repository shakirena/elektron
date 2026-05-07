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
use app\models\Users;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Arrivals';
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="arrival-index">

    <?php $typeList = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name'); ?>
    <?php $storeList = ArrayHelper::map(Store::find()->all(), 'id', 'name'); ?>
    <?php $user = ArrayHelper::map(Users::find()->all(), 'id_user', 'fio'); ?>
  <!--  <?php Pjax::begin(); ?>-->
   <br> <br> 
   

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

     //   'panel'=>['type'=>'primary'],
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
                'group'=>true,
                'filter' => false,
                // 'groupedRow'=>true,                    // move grouped column to a single grouped row
                // 'groupOddCssClass'=>'kv-grouped-row',  // configure odd group cell css class
                //   'groupEvenCssClass'=>'kv-grouped-row', // configure even group cell css class
                'groupHeader' =>function ($model, $index, $widget) {
                    return [
                        'mergeColumns' => [[1,3]],
                        'content' =>[
                            1=> ' Summary (Inbound (goods) delivery note №'.$model->number.')',
                            4=> $model->getSumNumber($model->number,"quantity"),

                            6=>$model->getSumNumber($model->number,"sum"),

                            8=>$model->getSumNumber($model->number,"sum_usd"),
                        ],
                        'contentFormats' => [
                            4=> ['format'=>'number','decimals'=>0],

                            6=> ['format'=>'number','decimals'=>2],

                            8=> ['format'=>'number','decimals'=>0],
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
			[   'attribute' => 'id_user',
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
                'attribute' =>'name_product',
                'value' => 'nameProduct',
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
               // 'footer' => $searchModel->getSumArrival($dataProvider->query,'quantity')
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
               // 'footer' => $searchModel->getSumArrival($dataProvider->query,'sum')
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
		

        ],
    ]); ?>
    <!-- <?php Pjax::end(); ?>-->
  </div>
