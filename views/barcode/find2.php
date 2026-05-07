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

    <?php $typeList = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name'); ?>


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
                return ['id' => $dataProvider['id'],
                    //'value' => Url::to(['arrival/add']),
                    'onClick'=>'addTransfer2(this.id)'
                ];
            },

        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            'name',
                [
                'attribute' => 'id_type',
                'filter' => $typeList,
                'value' => 'idType.name',
                    'filterWidgetOptions' =>[
                        'pluginOptions'=>['allowClear'=>true]
                    ],
                    'filterType' =>GridView::FILTER_SELECT2,
                    'width' => '200px',
                    'filterInputOptions' =>['placeholder'=>'Any ']


            ],
           
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
function addArrival(id)
{
  $("#current").modal("show")
        .find("#modalContent1");
      //  .load($(this).attr("arrival/add"));
      $.get('get-pricesell', {id:id},function(date){
        $("#id").val(id);
        $("#quantity").val(1);

        $("#usd").val(0);
        $("#pricesell").val(date);
         $.get('get-price', {id:id},function(date){

                 $("#price").val(date);

   //$.pjax.reload({container:"#grid-arrival"});
            });
   //$.pjax.reload({container:"#grid-arrival"});
    });



}
function addArrivalReceived(quantity,usd,price,id,pricesell)
{
   $.get('insert', {quantity:quantity,usd:usd,price:price,id:id,pricesell:pricesell},function(){
   $("#current").modal("hide");
   //$.pjax.reload({container:"#grid-arrival"});
    });

  //  alert(quantity);
}
JS;
$this->registerJs($script);
?>