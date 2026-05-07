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
use app\models\Sell;
use app\models\Dclient;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Arrivals';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="arrival-index">




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
    <br> <br>
    <?php $clientList = ArrayHelper::map(Client::find()->all(), 'id_client', 'fio'); ?>
    <?php $user = ArrayHelper::map(Users::find()->all(), 'id_user', 'fio'); ?>
    <?php $typeList = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name'); ?>
    <?php $storeList = ArrayHelper::map(Store::find()->all(), 'id', 'name'); $i=0;$num=0;?>
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

            

            [
                'attribute' => 'number',
                'format'=>'raw',
                'value' => 'getNumberSell',
                'group'=>true,

                'groupHeader' =>function ($model, $index, $widget) {
                    return [
                        'mergeColumns' => [[1,4]],
                        'content' =>[
                            1=> ' Summary (sell note №'.$model->number.')',
                            5=>GridView::F_SUM,
                            6=>GridView::F_SUM,
							8=>GridView::F_SUM,
                            

                          //  9=>GridView::F_SUM,
                            // 6=>GridView::F_SUM,

                         //  11=>$model->getTest(),
                          //  12=>$model->getTest1(),
                           // 12=>$model->getTest2(),
                        ],
                        'contentFormats' => [
							5=> ['format'=>'number','decimals'=>2],
                            6=> ['format'=>'number','decimals'=>2],
                            8=> ['format'=>'number','decimals'=>2],
                            
                           
                        ],
                        'contentOptions' => [
                            1 => ['style' => 'font-variant:small-caps'],
                            6 => ['style' => 'text-align:right'],
                           
                           
                            8 => ['style' => 'text-align:right'],



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
				'attribute'=>'barcode',
				'value'=>'nameBarcode'
			],

            [
                'attribute' => 'quantity',
                'label' =>'Miqdar',
                'format'=>'raw',
                'value' => 'quantity',
                'width' =>'100px',
                'hAlign' => 'right',
                'footer' =>  round($searchModel->getSumArrival($dataProvider->query,'quantity'),2)

            ],
			[
                
                'label' =>'Mal qalığı',
                'format'=>'raw',
                'value' => 'resta',
                'width' =>'100px',
                'hAlign' => 'right',
                //'footer' =>  round($searchModel->getSumRest($dataProvider->query,'rest'),2)

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
                'footer' =>  round($searchModel->getSumArrival($dataProvider->query,'sum'),2)
            ],
        /*    [
                'attribute' =>  'usd',
                'label' => 'Satış <br> qiyməti ($)',
                'format'=>'raw',
                'value' => 'usd',
                'width' =>'100px',
                'encodeLabel' => false,

                'hAlign' => 'right',
            ],
              [

                 'label' => 'Cəmi <br> ($)',
                 'format'=>'raw',
                 'value' => 'usdSum',
                 'width' =>'100px',
                 'encodeLabel' => false,
                  'hAlign' => 'right',
                 'footer' =>  round($searchModel->getSumArrival($dataProvider->query,'usd'),2)
             ],
			 */
            [
                'attribute' => 'earnings',
                'label'=>'Mənfəət',
                //'value' =>'sum',
				 'value' => function ($model, $index, $widget) {
					 if (  Yii::$app->user->identity->id_role==6 ) 
                    return $model->earnings;
				else return "=";
                },
                'width' =>'100px',
                'hAlign' => 'right',
				'filter'=>false,
                'footer' => round($searchModel->getSumArrival($dataProvider->query,'earnings'),2)
            ],
			
            [
                'attribute' =>  'price',
                'label' => 'Alis <br> qiyməti',
                'format'=>'raw',
                'value' => 'priceAr',
                'width' =>'100px',
                'encodeLabel' => false,

                'hAlign' => 'right',
            ],
			[
                'attribute' =>  'sn',
               
                'format'=>'raw',
                
        
                'encodeLabel' => false,

             
            ],

            /*[
                'label' => 'Qalıq borc',
				'value'=> 'restBorc',
                'width' => '100px',
                'footer' =>  round($searchModel->getSumBorc($dataProvider->query),2)

            ], */
			[   
                'label' => 'Kassa',
                'value' => 'getKassa',
              
            ],
            [   'attribute' => 'id_client',
                'label' => 'Müştəri',
                'value' => 'idClient.fio',
                'filter' => $clientList,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '400px',
                'filterInputOptions' =>['placeholder'=>'Any type']
            ],
			 [ 
                'label'=> 'Şirketi',
                'value' => 'getContrValue',
               
                'width' => '100px',
            
            ],
			 
			 [   'attribute' => 'type',
                'label'=> 'Mal grupu',
                'value' => 'idProduct.idType.name',
                'filter' => $typeList,
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
 <?php

  $itog = $sum+$zakaz+ $return;
  $summa = $sum_earnings - $earnings;
    echo "Cəmi: ".round($sum,2)."<br>";
    echo "Vozvrat: ".round($return,2)."<br>";
	
	if (  Yii::$app->user->identity->id_role==6 )
	echo "Mənfəət: ".round($summa,2)."<br>";
	echo "<b>Yekun: ".round($itog,2)."</b><br>";
	

  ?>