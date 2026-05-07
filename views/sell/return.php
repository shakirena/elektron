<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use \app\models\TypeProduct;
use \app\models\Client;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel app\models\DeviceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


?>
<div class="device-index">


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php
    Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'select',
            'tabindex' => true,
        ],

        'size' => '300px',

    ]);

    echo '<div id="selectContent">'.Html::input('hidden','id','',[' class' =>"form-control", 'id' =>'id']).'

            <div class="form-horizontal" role="form">
              <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">Miqdar</label>
                <div class="col-sm-10">'. Html::input('text','quantity','1',[' class' =>"form-control", 'id' =>'quantity']).'</div>
              </div>

              <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">Причина</label>
                <div class="col-sm-10">'. Html::textarea('reason',"",['id' =>'reason','class' =>"form-control"]).'</div>
              </div>
               <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">Müştəri</label>
                <div class="col-sm-10">'. Select2::widget([
                            'data' =>  ArrayHelper::map(Client::find()->all(), 'id_client', 'fio'),
                            'name' => 'contractor',
                            'options' => [
                                'id'=>'client',

                            ]
                        ]) .'</div>
              </div>
            </div>'

       .Html::button('<i class="glyphicon glyphicon-ok"></i>  OK', ['class' => 'btn btn-success', 'onclick' => 'returnSellReceived($("#quantity").val(),$("#reason").val(),$("#id").val(),$("#client").val())']).'</div>';


    Modal::end();
    ?>

    <?php $typeList = ArrayHelper::map(TypeProduct::find()->orderBy('name')->asArray()->all(), 'id', 'name'); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [
            'style' => 'width:800px;cursor:pointer',
            'class' => 'table table-striped table-bordered',

        ],
        'pjax' =>true,
        'hover'=>true,
        'striped' =>true,
        'rowOptions' =>
            function ($dataProvider, $key, $index, $grid) {
                if ($dataProvider['rest']) $rest=$dataProvider['rest'];
                else $rest=0;
                return ['id' => $dataProvider['id'],
                    'onClick'=>'returnSell(this.id)'
                ];
            },

        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            'name',

            [
                'attribute' => 'id_type',
                'filter' => $typeList,
                'value' => 'idType.name',
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true]
                ],
                'filterType' => GridView::FILTER_SELECT2,
                'width' => '200px',
                'filterInputOptions' => ['placeholder' => 'Any type']
            ],
            'bar_code'

        ],
    ]); ?>

</div>
