<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;
use \app\models\Store;
use \app\models\TypeProduct;
use yii\widgets\Pjax;
use kartik\date\DatePicker;

use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TransferSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transfer-index">
    <?php // echo $this->render('_search', ['model' => $searchModel]);
     $dataProvider->pagination=false;?>


    <p>
	
	<div class="btn-group">
		<?= Html::input("text",'barcode','',['id'=>'barcode','size'=>'15', 'class' => 'form-control','onChange' => 'addSellBarcode($("#barcode").val())'])?>
	</div>
	<div class="btn-group">
        <?= Html::button('<i class="glyphicon glyphicon-search"></i>Axtarış', ['value'=>Url::to(['transfer/find2']), 'class' => 'btn btn-info','id'=>'modalButton3']) ?>
	</div>
	</p>
    <?php
    Modal::begin([
      
        'id' => 'kartik-modal',
        'size' => 'modal-lg',
		'options' => [
           
            'tabindex' => true,

        ],
    ]);

    echo '<div id="modalContent"></div>';

    Modal::end();
    ?>
    <?php
    $store=Store::find()->all();



    ?>
    <?php Pjax::begin(['id'=>'grid-arrival','options'=>['style'=>'float:left']]); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
       
		   'tableOptions' => [
            'style'=>'font-size:10pt;width:100%',
            'class' => 'table-rena table-rena2 grid-view-mobile',
			'onLoad'=>"$('.grid-view-mobile').removeClass('kv-table-wrap' )"
			

        ],


       'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            ['attribute'=>'id_product',
             'value'=>'idProduct.name',
             'label' =>'Malın adı',
            ],

            [
                'attribute' => 'quantity',
                'label' =>'Miqdar',
                'format' => 'raw',
                'value' => function ($model, $index, $widget) {
                    return Html::input('text', 'quantity[]', $model->quantity, ['class' => 'form-control input-sm', 'size' => '3', 'onChange' => "editQuantity($model->id,this.value)"]);
                }
            ],

            ['class' => 'yii\grid\ActionColumn', 'template' => '{delete}'],
        ],
    ]); ?>



    <?php Pjax::end();?>
</div>


<div style='margin:30px;width:200px;float:right'>
    <?="Hansı Anbardan".Select2::widget([
        'data' => ArrayHelper::map(Store::find()->all(),'id','name'),
        'name' => 'whence',
        'options' => [
            'placeholder' => 'Seçin',

            'id'=>'whence',

        ]
    ]);
?>

    <?="Hansı Anbara".Select2::widget([
        'data' => ArrayHelper::map(Store::find()->all(),'id','name'),
        'name' => 'whence',
        'options' => [
            'placeholder' => 'Seçin',

            'id'=>'where',

        ]
    ]);
?>
	
   <br>


    <br>
    <?= Html::button('<i class="glyphicon glyphicon-ok"></i>Təsdiqlə',['class' => 'btn btn-success','onclick'=>'receivedTransfer($("#whence").val(),$("#where").val(),0)']);?>
	 <br> <br>
	 <?= Html::button('<i class="glyphicon glyphicon-ok"></i>Təsdiqlə + Gözləmeye',['class' => 'btn btn-info','onclick'=>'receivedTransfer($("#whence").val(),$("#where").val(),1)']);?>
    <br> <br>
	<?= Html::button('<i class="glyphicon glyphicon-remove"></i>  Ləğv et', ['class' => 'btn btn-danger','onclick'=>'deleteAllTransferMobile()']);?>
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