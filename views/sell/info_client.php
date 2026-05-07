<?php
use yii\widgets\Pjax;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use app\models\Client;
use app\models\TypeProduct;
use app\models\Users;
use app\models\Store;
use kartik\select2\Select2;
use app\models\TypeBalance;
/* @var $this yii\web\View */
/* @var $searchModel app\models\BalanceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


?>
<div class="balance-index" style="float: left">
    <?php $user = ArrayHelper::map(Users::find()->all(), 'id_user', 'fio'); ?>
    <?php $type = ArrayHelper::map(TypeBalance::find()->all(), 'id', 'name'); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'tableOptions' => [
            'style'=>'width:900px',
            'class' => 'table-rena table-rena2 text-right',

        ],
       // 'showPageSummary' => true,
       // 'pageSummaryRowOptions' => ['class' => 'text-right danger'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [   'attribute' => 'id_user',
                'label' => 'Satıcı',
                'value' => 'idUser.fio',
                'filter' => $user,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '150px',
                'filterInputOptions' =>['placeholder'=>'Hər hansi'],
                //'pageSummary' => 'Yekun',
                //'pageSummaryOptions' => ['class' => 'text-right text-danger'],
            ],
            [
                'attribute' => 'datetime',
                'label' => 'Tarixi',
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
                'attribute' =>    'id_type',
                'value' => 'idType.name',
                'filter' => $type,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '150px',
                'filterInputOptions' =>['placeholder'=>'Hər hansi'],
            ],
           // 'current_sum',

            'note',

            [
                'attribute' => 'sum',
                'label' => 'Məbləğ',
               // 'pageSummary' => true,
                'width' => '50px',
            ],


        ],
    ]); ?>

</div>
