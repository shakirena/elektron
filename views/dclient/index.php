<?php
use yii\bootstrap\Modal;
use yii\helpers\Html;
use kartik\grid\GridView;
//use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use app\models\Client;
use kartik\date\DatePicker;
use yii\widgets\Pjax;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel app\models\DclientSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = 'Dclients';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dclient-index">


    <?php $clientList = ArrayHelper::map(Client::find()->all(), 'id_client', 'fio'); ?>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [
            'style' => 'font-size:10pt',
            'class' => 'table table-striped table-bordered',

        ],
        'pjax' =>true,
        'columns' => [
           // ['class' => 'kartik\grid\SerialColumn'],


           [
              'attribute'  => 'id_client',
               'value' =>'idClient.fio',
               'filter' => $clientList,
               'filterWidgetOptions' =>[
                   'pluginOptions'=>['allowClear'=>true]
               ],
               'filterType' =>GridView::FILTER_SELECT2,
               'width' => '300px',
               'filterInputOptions' =>['placeholder'=>'Any type']
           ] ,
            [
                'attribute' => 'datetime',
                'format'=>'raw',
                'width' => '250px',
                'filter' =>DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'date_start',
                    //'options' => ['placeholder' => 'Select issue date ...'],
                    'type' => DatePicker::TYPE_RANGE,
                    'attribute2' => 'date_end',
                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd',
                        'autoClose' => true
                        // 'todayHighlight' => false
                    ]
                ]),
                // 'group'=>true,

            ],
            [
                'attribute' =>  'number',
                'width' => '50px',
                'contentOptions' =>[  function ($dataProvider, $key, $index, $grid) {
                    return ['id' => $dataProvider['id'],
                        'value' => Url::to(['sell/report1kjkjkj' ,'number' =>$dataProvider['number']]),
                        'onClick'=>"infoSell(this.id)"
                    ];
                },
                'style' => "color:red"
                ]
            ]
           ,
            'debt',
            'sum',
            [

                'label' =>'Ödəniş',
                'format' => 'raw',
                'value' => function ($model, $index, $widget) {
                    return Html::input('text', 'sum[]', 0, ['class' => 'form-control', 'size' => '3','id' => 'sum'.$model["id"]]);
                }
            ],
            [

                'label' =>'Qeyd',
                'format' => 'raw',
                'value' => function ($model, $index, $widget) {

                    return Html::textarea( 'note[]', "", ['class' => 'form-control', 'cols' => '30','id' => 'note'.$model["id"]]);
                },
                'width' => '200px'
            ],
            [

                'label' =>'Ödəmək',
                'format' => 'raw',
                'value' => function ($model, $index, $widget) {

                    return Html::button('<i class="glyphicon glyphicon-ok"></i>  Ödəmək', ['class' => 'btn btn-success',  'onClick' => "debtReceived($model->id)"]);
                }
            ],

            // 'datetime',
            // 'date_return',

           // ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

     <?php
    Modal::begin([
        'header' => '<h2>Create type product</h2>',
        'id' => 'sell-info',
        'size' => 'modal-sm',

    ]);

    echo '<div id="modalContent1"></div>';

    Modal::end();
    ?>
</div>
