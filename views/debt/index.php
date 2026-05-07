<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use kartik\grid\GridView;
use app\models\Contractor;
use app\models\Kassa;
use kartik\select2\Select2;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\DebtSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = 'Debts';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="debt-index">
	<?php
    Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'modal',
            'tabindex' => true,
        ],

        'size' => '300px',

    ]);

    echo '<div id="modalDclient"></div>';

    Modal::end();
    ?>
    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
 <?= Html::button('<i class="glyphicon glyphicon-plus"></i> Əlavə et',['value' => Url::to(['debt/debt-add']),'class' => 'btn btn-danger','id' => 'addDclient']); ?>
  
    <?php $contractorList = ArrayHelper::map(Contractor::find()->all(), 'id', 'name'); ?>

    <?php
        Modal::begin([
            // 'header' => '<h4>Find device</h4>',
            'options' => [
                'id' => 'info',
                'tabindex' => true,
            ],

            'size' => '300px',

        ]);

        echo '<div id="modalContent"></div>';

        Modal::end();
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [
            'style' => 'cursor:pointer',
            'class' => 'table-rena table-rena2',

        ],
		'showFooter' => true,
        'footerRowOptions' => ['style' => 'font-weight:bold;text-decoration:underline;color:red;'],

        'pjax' =>true,
        'hover'=>true,
        'striped' =>true,
    /*    'rowOptions' =>
            function ($dataProvider, $key, $index, $grid) {
                return ['id' => $dataProvider['id'],
                    'value' => Url::to(['finance/contr', 'number' =>$dataProvider['number'] ]),
                    'onClick'=>"infoDebt(this.id)"
                ];
            },*/
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            [
                'attribute' => 'id_contr',
                'filter' => $contractorList,
                'width' => '200px',
                'value' => 'idContr.name',
                'format'=>'raw',

                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true]
                ],
                'filterType' => GridView::FILTER_SELECT2,
                'filterInputOptions' => ['placeholder' => 'Any type'],
                'contentOptions' =>  function ($dataProvider, $key, $index, $grid) {
                    return ['id' => $dataProvider['id'],
                        'value' => Url::to(['finance/contr', 'number' =>$dataProvider['number'] ]),
                        'onClick'=>"infoDebt(this.id)"
                    ];
                },
            ],
           
       
            [
                'attribute' => 'debt',
				  'value' => function($model){
								return round($model['debt'],2);
				},
                 'footer' =>  round($searchModel->getSumDebt($dataProvider->query),2),
                'contentOptions' =>  function ($dataProvider, $key, $index, $grid) {
                    return ['id' => $dataProvider['id'],
                        'value' => Url::to(['finance/contr', 'number' =>$dataProvider['number'] ]),
                        'onClick'=>"infoDebt(this.id)"
                    ];
                },
            ],

           

            [

                'label' =>'Ödəniş',
                'format' => 'raw',
				 'width' => '130px',
                'value' => function ($model, $index, $widget) {
                    return Html::input('text', 'sum[]', 0, ['class' => 'form-control', 'size' => '3','id' => 'sum'.$model["id_contr"]]).
					Select2::widget([
							'data' =>  ArrayHelper::map(Kassa::find()->all(), 'id', 'name'),
							'name' => 'kassa',
							'options' => [
								'placeholder' => 'Seçin',
								'id'=>'kassa'.$model["id_contr"],

							]
						]);
                },

            ],
            [

                'label' =>'Qeyd',
                'format' => 'raw',
                'value' => function ($model, $index, $widget) {

                    return Html::textarea( 'note[]', "", ['class' => 'form-control', 'cols' => '30','id' => 'note'.$model["id_contr"]]);
                },
                'width' => '200px'
            ],
            [

                'label' =>'Ödəmək',
                'format' => 'raw',
                'value' => function ($model, $index, $widget) {

                    return Html::button('<i class="glyphicon glyphicon-ok"></i>  Ödəmək', ['class' => 'btn btn-success',  'onClick' => "debtReceived($model->id_contr)"]);
                }
            ],
			[
                'label' => 'Info',
                'format' => 'raw',
                'value' => function ($model, $index, $widget) {

                    return Html::button('<i class="glyphicon glyphicon-info"></i>  История', ['class' => 'btn btn-info',  'onClick' => "infoContr($model->id_contr)"]);
                }
            ]
            // 'number',

            // 'discount',
            // 'sum_usd',


        ],
    ]); ?>



</div>
