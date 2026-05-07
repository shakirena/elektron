<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use app\models\Store;
use app\models\Contractor;
use app\models\TypeProduct;
use kartik\date\DatePicker;
/* @var $this yii\web\View */
/* @var $searchModel app\models\HistorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Histories';

?>
<div class="history-index">

   
   
    <?php $storeList = ArrayHelper::map(Store::find()->all(), 'id', 'name'); ?>
    <?php $contractorList = ArrayHelper::map(Contractor::find()->all(), 'id', 'name'); ?>
    <?php $typeList = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name'); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
		'tableOptions' => [

            'class' => 'table-rena table-rena2',
            'style' => 'font-size:9pt'

        ],
		'showFooter' => true,
		'footerRowOptions' => ['style' => 'font-weight:bold;text-decoration:underline;color:red;'],

        'columns' => [
          
			[
                'attribute' =>'id_product',
                'value' => 'idProduct.name',
				'subGroupOf'=>1,
                'group'=>true,
				'pageSummary' => 'Yekun',
                'pageSummaryOptions' => ['class' => 'text-right text-danger'],

            ],
			 [
                'attribute' =>  'type_name',
				'label' =>  'Mal qrupu ',
                'filter' => $typeList,
                'value' => 'idProduct.idType.name',
                    'filterWidgetOptions' =>[
                        'pluginOptions'=>['allowClear'=>true]
                    ],
                    'filterType' =>GridView::FILTER_SELECT2,
                    'width' => '200px',
                    'filterInputOptions' =>['placeholder'=>'Any ']


            ],
            
            
			 [  
				'attribute' => 'id_store',
				'label' => 'Anbar',
                'value' => 'idStore.name',
                'filter' => $storeList,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '200px',
                'filterInputOptions' =>['placeholder'=>'Any ']
            ],
			  [
                'attribute' => 'date',
                'format'=>'raw',
            
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
			[
				'attribute'=>'rest',
				'footer' =>round( $searchModel->getSumHistory($dataProvider->query,'rest'),2)
			],
			
			[
				'attribute'=>'pricesell',
				 'footer' =>round( $searchModel->getSumHistory($dataProvider->query,'pricesell'),2)
			],
			[
				'attribute'=>'price',
				'footer' =>round( $searchModel->getSumHistory($dataProvider->query,'price'),2)
			],
		
			
	 
			
        ],
    ]); ?>
</div>
