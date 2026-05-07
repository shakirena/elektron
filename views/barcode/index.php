<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\BarcodeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Barcodes';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="barcode-index">
    <br>
    <div class='row'>

        <div class="col-md-3">
            <?= Html::input("text",'barcode','',['id'=>'barcode','size'=>'10', 'class' => 'form-control','onChange' => 'addSverkaBarcode($("#barcode").val())'])?>
        </div>
        <div class="col-md-3">
            <?= Html::button('<i class="glyphicon glyphicon-search"></i>Axtarış', ['value' => Url::to(['barcode/find']), 'class' => 'btn btn-warning', 'id' => 'modalButton']) ?>
        </div>

       <!-- <div class="col-md-2">
            <?= Html::button('Печать <i class="glyphicon glyphicon-print"></i>', ['class' => 'btn btn-success', 'onclick' => 'barcodeReceived()']) ?>
        </div>-->
        <div class="col-md-1">
            <?= Html::button('Çapa ver  <i class="glyphicon glyphicon-print"></i>', ['class' => 'btn btn-success', 'onclick' => 'barcodeReceived2()']) ?>
        </div>
		<div class="col-md-2">
            <?= Html::button('Çapa ver (30x50)  <i class="glyphicon glyphicon-print"></i>', ['class' => 'btn btn-info', 'onclick' => 'barcodeReceived3()']) ?>
        </div>
		<?= Html::button('<i class="glyphicon glyphicon-remove"></i> Hamsını sil', ['class' => 'btn btn-danger', 'onclick' => 'deleteAll()']); ?>
    </div>
    <br><br>
    <?php
    Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'kartik-modal',
             'tabindex' => true,
        ],

        'size' => 'modal-lg',

    ]);

    echo '<div id="modalContent"></div>';

    Modal::end();

    ?>
    <?php Pjax::begin(['id' => 'grid-arrival']); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [
            'style' => 'width:800px',
            'class' => 'table-rena table-rena2',

        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            [
                'attribute' =>'id_product',
                'label' =>'Malın adı',
                'value' => 'idProduct.name',
            ],
            [
                'attribute' => 'count',
                'label' =>'Qaliq',
                'format' => 'raw',
               // 'width' => '50px',
                'value' => function ($model, $index, $widget) {
                    return Html::input('text', 'quantity[]', $model->count, ['class' => 'form-control input-sm', 'style' => ' width:50px !important', 'onChange' => "editQuantity($model->id,this.value)"]);
                }
            ],

            ['class' => 'yii\grid\ActionColumn','template' => '{delete}'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
	
</div>

<?php
$script = <<< JS
 $(document).ready(function(){
    $("#barcode").val("");
    $("#barcode").focus();
});
JS;
$this->registerJs($script);
?>