<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\models\Client;
use app\models\TypeProduct;
use app\models\Users;
use app\models\Store;
use app\models\Master;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PostponedSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Postponeds';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="postponed-index">
    <?php $clientList = ArrayHelper::map(Client::find()->all(), 'id_client', 'fio'); ?>
    <?php $user = ArrayHelper::map(Users::find()->all(), 'id_user', 'fio'); ?>
    <?php $typeList = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name'); ?>
    <?php $storeList = ArrayHelper::map(Store::find()->all(), 'id', 'name'); ?>
    <?php $master = ArrayHelper::map(Master::find()->all(), 'id', 'name'); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [

            'class' => 'table-rena table-rena2',
            'style' => 'font-size:9pt;width:1200px'

        ],
        'columns' => [
            ['class'=>'kartik\grid\SerialColumn'],

            [
                'attribute' => 'number',
                'format'=>'raw',
                'value' => 'getNumber',
                'width' => '300px',
                'group'=>true,
                'filter' => false,
                'groupHeader' =>function ($model, $index, $widget) {
                    return [
                        'mergeColumns' => [[1,4]],
                        'content' =>[
                            1=> ' Summary (sell note №'.$model->number.')',
                            5=>GridView::F_SUM,
                            6=>GridView::F_SUM,
                            7=>GridView::F_SUM,
                        ],
                        'contentFormats' => [
                            5=> ['format'=>'number','decimals'=>0],
                            6=> ['format'=>'number','decimals'=>2],
                            7=> ['format'=>'number','decimals'=>2],
                        ],
                        'contentOptions' => [
                            1 => ['style' => 'font-variant:small-caps'],
                            5 => ['style' => 'text-align:right'],
                            6 => ['style' => 'text-align:right'],
                            7 => ['style' => 'text-align:right'],
                        ],
                        'options' => ['class' => 'danger','style'=> 'font-weight:bold']
                    ];
                },

            ],

            [
                'attribute' => 'date',
                'format'=>'raw',
                'label' => 'Tarixi',
                'value' => 'date','width' => '100px',

                // 'group'=>true,

            ],
            [
                'attribute' =>'id_product',
                'label' =>'Malın adı',
                //'filter' => $productList,
                'value' => 'getType',
                'pageSummary' => 'Yekun',
                'pageSummaryOptions' => ['class' => 'text-right text-danger'],
                'width' => '700px',

            ],

            [
                'attribute' => 'quantity',
                'label' =>'Miqdar',
                'format'=>'raw',
                'filter' => false,
                'value' => 'quantity',
                'width' =>'80px',
                'pageSummary' => true,
                'hAlign' => 'right',

            ],


            [   'attribute' => 'id_store',
                'label' => 'Filial',
                'value' => 'idStore.name',
                'filter' => $storeList,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '300px',
                'filterInputOptions' =>['placeholder'=>'Hər hansi']
            ],
            [   'attribute' => 'id_user',
                'label' => 'Satıcı',
                'value' => 'idUser.fio',
                'filter' => $user,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '300px',
                'filterInputOptions' =>['placeholder'=>'Hər hansi']
            ],
            [
                'attribute' => 'id_master',
                'value'=>'idMaster.name',
                'filter' => $master,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '300px',
                'filterInputOptions' =>['placeholder'=>'Hər hansi']

            ],
            ['class' => 'kartik\grid\ActionColumn', 'template' => '{update}'],
            // 'id_sell',
            // 'date',
            // 'received',


        ],
    ]); ?>
</div>
