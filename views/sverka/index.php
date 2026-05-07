<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\models\Store;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use app\models\Product;
use kartik\select2\Select2;
use app\models\TypeProduct;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SverkaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sverkas';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sverka-index">
<br>
   <?php
    Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'sverka-modal',
            'tabindex' => true,

        ],
 'size' => 'modal-lg',


    ]);

    echo '<div id="modalContent"></div>';

    Modal::end();
    ?>
    <div class='row'>
        <div class="col-md-2">
            <?= Html::dropDownList("store", Yii::$app->session->get("sverka"),ArrayHelper::map(Store::find()->all(), 'id', 'name'),["id" => 'update-store','onchange'=>'editStore($("#update-store").val())','class' => 'form-control']) ?>
        </div>


        <div class="col-md-2">
            <?= Html::button('Записать <i class="glyphicon glyphicon-play"></i>', ['class' => 'btn btn-danger', 'onclick' => 'sverkaReceived()']) ?>
        </div>
    </div>
    <br>
    <div class="btn-group">
        <?= Html::input("text",'barcode','',['id'=>'barcode','size'=>'25', 'class' => 'form-control','onChange' => 'addSverkaBarcode($("#barcode").val())'])?>
    </div>
    <div class="btn-group">
        <?= Html::button('<i class="glyphicon glyphicon-search"></i>Axtarış', ['value' => Url::to(['sverka/find']), 'class' => 'btn btn-info', 'id' => 'sverka_dialog']) ?>
    </div>

    <div class="btn-group">
        <?= Html::button('<i class="glyphicon glyphicon-ok"></i>  OK', ['class' => 'btn btn-success', 'onclick' => 'addSellType($("#product").val())']); //addSellType($("#product").val())?>

    </div>


    <br><br>
    <?php Pjax::begin(['id' => 'grid-arrival']); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [

            'class' => 'table-rena table-rena2',

        ],

        'showPageSummary' => true,
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],



            [
                'label' =>'Malın adı',
                'value' => 'product.name',
                'pageSummary' => 'Yekun',
                'pageSummaryOptions' => ['class' => 'text-right text-warning']

            ],
            [
                'attribute' => 'quantity',
                'label' =>'Fakt.qaliq',
                'format' => 'raw',
                'width' => '50px',
                'pageSummary' =>$sum_fakt,
                'value' => function ($model, $index, $widget) {
                    return Html::input('text', 'quantity[]', $model->quantity, ['class' => 'form-control input-sm', 'style' => ' width:50px !important', 'onChange' => "editQuantity($model->id,this.value)"]);
                }
            ],
            [
                'encodeLabel' => false,
                'label' => 'Cəmi hesab-<br>lanış qalıq',
                'value' => 'quantitySklad',
                'pageSummary' => true,
            ],

            [
                'label' => 'Fərq',
                'value' => 'difference'
            ],
            [
                'encodeLabel' => false,
                'label' => 'Satış <br> qiymeti',
                'value' => 'priceSell.pricesell',

            ],
            [
                'encodeLabel' => false,
                'label' => 'Fakt. qal <br> məbləği',
                'value' => 'sum',
                'pageSummary' => true,
            ],
            [
                'encodeLabel' => false,
                'label' => 'Son aliş <br> qiymeti',
                'value' => 'priceSell.price'
            ],
            [
                'encodeLabel' => false,
                'label' => 'Fak. Son aliş <br> qiymeti',
                'value' => 'priceSellSum',
                'pageSummary' => true,
            ],
            ['class' => 'kartik\grid\ActionColumn', 'template' => '{delete}'],
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