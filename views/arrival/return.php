<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use \app\models\TypeProduct;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use app\models\Store;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel app\models\DeviceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


?>
<div class="device-index">


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php $storeList = ArrayHelper::map(Store::find()->all(), 'id', 'name'); ?>

    <?php $typeList = ArrayHelper::map(TypeProduct::find()->orderBy('name')->asArray()->all(), 'id', 'name'); ?>

    <?php $typeList = ArrayHelper::map(TypeProduct::find()->orderBy('name')->asArray()->all(), 'id', 'name'); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [
            'style' => 'width:800px;cursor:pointer',
            'class' => 'table-rena table-rena2',
        ],
        'pjax' =>true,
        'hover'=>true,
        'striped' =>true,
        'rowOptions' =>
            function ($dataProvider, $key, $index, $grid) {
                if ($dataProvider['rest']) $rest=$dataProvider['rest'];
                else $rest=0;
                return ['id' => $dataProvider['id'],

                   //'value' => Url::to(['arrival/add']),
                    'onClick'=>'returnArrival(this.id)'
                ];
            },

        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            [
                'attribute' =>'id_product',
                // 'label' =>'Malın adı',
                //'filter' => $productList,
                'value' => 'product.name',
                'format'=>'raw',
                'group' => 'true',

                // 'groupedRow'=>true,
                'width' => '300px',
                //'filterInputOptions' => ['placeholder' => 'Any type']
            ],
            [
                'attribute' =>'type',
                // 'value' => 'type.name',
                'filter' => $typeList,
                'value' => 'type.name',
                'format'=>'raw',



                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true]
                ],
                'filterType' => GridView::FILTER_SELECT2,
                'width' => '200px',
                'filterInputOptions' => ['placeholder' => 'Any type']
            ],
            [
                'attribute' =>'rest',
                'label'=>'Anbarda <br> sayı',
                'value' => 'rest',
                'format'=>'raw',
                'width' =>'30px',
                'encodeLabel' => false,

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

        ],
    ]); ?>

</div>
