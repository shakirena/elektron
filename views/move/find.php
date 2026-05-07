<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use \app\models\TypeProduct;
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
                $name=$dataProvider['name'];
                return ['id' => $dataProvider['id'],

                   //'value' => Url::to(['arrival/add']),
                      'onClick'=>'clickProduct(this.id,"'.$name.'")'
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
            'bar_code',
            [
                'label'=>'Anbarda sayı',
                'value' => 'rest'
            ]

        ],
    ]); ?>

</div>
