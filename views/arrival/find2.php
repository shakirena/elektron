<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use \app\models\Contractor;
use app\models\TypeProduct;
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

<?php 
echo HTML::a("<i class='glyphicon glyphicon-arrow-left'></i>",['index2'],['class' => 'btn btn-danger']);
?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php $contractor = ArrayHelper::map(Contractor::find()->all(), 'id', 'name'); ?>

  <?php $typeList = ArrayHelper::map(TypeProduct::find()->orderBy('name')->asArray()->all(), 'id', 'name'); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [
            'style' => 'width:100%;cursor:pointer',
            'class' => 'table-rena table-rena2',
        ],
        'pjax' =>true,
        'hover'=>true,
        'striped' =>true,
        'rowOptions' =>
            function ($dataProvider, $key, $index, $grid) {
                return ['id' => $dataProvider['id'],
						'name'=>$dataProvider['id'],
                   //'value' => Url::to(['arrival/add']),
                    'onClick'=>"addArrival(this.id,'$dataProvider[name]')"
                ];
            },

        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            'name',
             
            [
                'attribute' => 'barcode',
                'value' => 'nameBarcode'
            ]
          //  'bar_code',


        ],
    ]); ?>

</div>
<?php
$script = <<< JS


function addArrivalReceived(quantity,price,id,pricesell,proc,pack,pricetop,polka,trade_price,pricesell_min,boxing)
{
   $.get('insert', {quantity:quantity,price:price,id:id,pricesell:pricesell,proc:proc,pack:pack,pricetop:pricetop,polka:polka,trade_price:trade_price,pricesell_min:pricesell_min},function(){
   $("#current").modal("hide");
   //$.pjax.reload({container:"#grid-arrival"});
    });

  //  alert(quantity);
}

$(document).on('pjax:success', function() {
  $(".table-rena2").removeClass("kv-table-wrap" );
 
});
	$(".table-rena2").removeClass("kv-table-wrap" );
  
JS;
$this->registerJs($script);

?>