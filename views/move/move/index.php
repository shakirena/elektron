<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use kartik\date\DatePicker;
use app\models\TypeMove;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel app\models\MoveSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$this->params['breadcrumbs'][] = $this->title;
?>
<div class="move-index">

 <?php $typeList = ArrayHelper::map(TypeMove::find()->all(), 'id', 'name'); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
		 'tableOptions'=>[
            'style'=>'width:1000px;',
            'class' => 'table-rena table-rena2 ',

        ],
		'showFooter' => true,
        'footerRowOptions' => ['style' => 'font-weight:bold;text-decoration:underline;color:red;'],
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            [
				'attribute'=>"id_product",
				'value'=>'idProduct.name',
			],
			[
				'attribute'=>'barcode',
				'value'=>'nameBarcode'
			],
            [
				'attribute'=>"type",
				'value'=>'type0.name',
				'filter' => $typeList,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '400px',
                'filterInputOptions' =>['placeholder'=>'Any type']
			],
			[
				'attribute'=> 'quantity',
				'footer' =>  round($searchModel->getSumArrival($dataProvider->query,'quantity'),2)
			],
			  'price',
           [
				'attribute'=> 'sum',
				'footer' =>  round($searchModel->getSumArrival($dataProvider->query,'sum'),2)
		   
		   ],
          
           
            
           [
                'attribute' => 'datetime',
                'label' => 'Tarix',
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

        ],
    ]); ?>
</div>
