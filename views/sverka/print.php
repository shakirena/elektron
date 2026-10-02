<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\models\Store;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SverkaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $sum_fakt double */

$this->title = 'Sverka';

$store = Store::findOne(Yii::$app->session->get("sverka"));
?>
<style>
    @page {
        size: landscape;
    }
    #inf table {
        width: 100%;
    }
</style>
<div id="inf" align="center">

    <p style="font-family:Times New Roman;margin-bottom:0px;font-size:10pt;"><b><?= Html::encode($store ? $store->name : '') ?></b></p>
    <p style="font-size:8pt;">Tarix: <?= date("Y-m-d H:i:s") ?></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => false,
        'showPageSummary' => true,
        'pageSummaryRowOptions' => ['class' => 'text-right danger'],
        'tableOptions' => [
            'style' => 'font-size:8pt',
            'class' => 'table-print',
        ],
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            [
                'label' => 'Malın adı',
                'value' => 'product.name',
                'pageSummary' => 'Yekun',
                'pageSummaryOptions' => ['class' => 'text-right text-danger'],
            ],
            [
                'label' => 'Fakt.qaliq',
                'value' => 'quantity',
                'hAlign' => 'right',
                'pageSummary' => $sum_fakt,
            ],
            [
                'encodeLabel' => false,
                'label' => 'Cəmi hesab-<br>lanış qalıq',
                'value' => 'quantitySklad',
                'hAlign' => 'right',
                'pageSummary' => true,
            ],
            [
                'label' => 'Fərq',
                'value' => 'difference',
                'hAlign' => 'right',
            ],
            [
                'encodeLabel' => false,
                'label' => 'Satış <br> qiymeti',
                'value' => 'priceSell.pricesell',
                'hAlign' => 'right',
            ],
            [
                'encodeLabel' => false,
                'label' => 'Fakt. qal <br> məbləği',
                'value' => 'sum',
                'hAlign' => 'right',
                'pageSummary' => true,
            ],
            [
                'encodeLabel' => false,
                'label' => 'Son aliş <br> qiymeti',
                'value' => 'priceSell.price',
                'hAlign' => 'right',
            ],
            [
                'encodeLabel' => false,
                'label' => 'Fak. Son aliş <br> qiymeti',
                'value' => 'priceSellSum',
                'hAlign' => 'right',
                'pageSummary' => true,
            ],
        ],
    ]) ?>
</div>
<?php
$script = <<< JS
window.print();
JS;
$this->registerJs($script);
?>
