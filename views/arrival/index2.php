    <?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use kartik\grid\GridView;
use kartik\date\DatePicker;
use app\models\Store;
    use app\models\Arrival;
use app\models\Contractor;
use app\models\Product;
use kartik\select2\Select2;
use app\models\TypeProduct;

if (  Yii::$app->user->identity->id_role==1)  $role3=0;

else $role3=1;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = 'Arrivals';
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="arrival-index">
  <div class="noprint">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
	<div class="btn-group">
        <?= Html::input("text",'barcode','',['id'=>'barcode','size'=>'10', 'class' => 'form-control','onChange' => 'addSellBarcode($("#barcode").val())'])?>
    </div>
   
    <div class="btn-group">
        <?= Html::button('<i class="glyphicon glyphicon-search"></i>', ['value' => Url::to(['arrival/find2']), 'class' => 'btn btn-warning', 'id' => 'modalButton3']) ?>
    </div>

  <!-- <div class="btn-group">
        <?= Html::button('<i class="glyphicon glyphicon-time"></i> ', ['value' => Url::to(['arrival/postponed']), 'class' => 'btn btn-info', 'id' => 'postponed_dialog']) ?>

    </div>
	-->
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
	Modal::begin([
       // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'login-modal',
            'tabindex' => '-1',
        ],

        'size' => 'modal-sm',

    ]);

    echo '<div id="loginContent"></div>';

    Modal::end();
	
	 Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'chek',
            'tabindex' => true,
			'class'=>'rena_dialog'

        ],



    ]);

    echo '<div id="chekContent"></div>';

    Modal::end();
    ?>

    <?php
    Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'current',
            'tabindex' => '-1',
            'data-backdrop-limit'=>'1'
        ],

        'size' => "250px",

    ]);

    echo '<div id="modalContent1">'.  Html::input('hidden','id','',[' class' =>"form-control", 'id' =>'id'])
        .'
		<div id="name"></div> <br>
<div class="form-horizontal" role="form">
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-2 control-label">Miqdar</label>
    <div class="col-sm-10">'. Html::input('text','quantity','1',[' class' =>"form-control", 'id' =>'quantity']).'</div>
  </div>


<div class="form-group" >
    <label for="inputEmail3" class="col-sm-2 control-label">Gəliş qiyməti </label>
    <div class="col-sm-10">'. Html::input('text','azd','1',[' class' =>"form-control", 'id' =>'price','onchange' => 'editPriceAr()']).'</div>
  </div>
  

  <div class="form-group" >
    <label for="inputEmail3" class="col-sm-2 control-label">Faiz</label>
    <div class="col-sm-10">'.  Html::input("text",'proc','',['id'=>'proc','size'=>'5', 'class' => 'form-control','onChange' => 'editProcent($("#proc").val(),$("#price").val())'])
        .'</div>
  </div>
  
 

<div class="form-group">
    <label for="inputEmail3" class="col-sm-2 control-label">Satış qiyməti </label>
    <div class="col-sm-10">'. Html::input('text','pricesell','0',[' class' =>"form-control", 'id' =>'pricesell','onchange' => 'editPriceAr()']).'</div>
  </div>


 <div class="form-group" >
    <label for="inputEmail3" class="col-sm-2 control-label" >Qutuda ədəd sayi</label>
    <div class="col-sm-10">'. Html::input('text','pack','1',[' class' =>"form-control", 'id' =>'pack','onchange' => 'editPack()']).'</div>
  </div>
<div class="form-group" >
    <label for="inputEmail3" class="col-sm-2 control-label">Ədədin qyiməti</label>
    <div class="col-sm-10">'. Html::input('text','price_top','0',[' class' =>"form-control", 'id' =>'price_top']).'</div>
  </div>
 <div class="form-group" >
    <label for="inputEmail3" class="col-sm-2 control-label">Topdan satış qiyməti</label>
    <div class="col-sm-10">'. Html::input('text','trade_price','0',[' class' =>"form-control", 'id' =>'trade_price']).'</div>
  </div>
<div class="form-group" >
    <label for="inputEmail3" class="col-sm-2 control-label">Minimum  satış  qiyməti</label>
    <div class="col-sm-10">'. Html::input('text','pricesell_min','0',[' class' =>"form-control", 'id' =>'pricesell_min']).'</div>
  </div>  

</div>'.Html::button('<i class="glyphicon glyphicon-ok"></i>  OK', ['class' => 'btn btn-success', 'onclick' => 'addArrivalReceived($("#quantity").val(),$("#price").val(),$("#id").val(),$("#pricesell").val(),$("#proc").val(),$("#pack").val(),$("#price_top").val(),0,$("#trade_price").val(),$("#pricesell_min").val(),$("#boxing").val())']).'</div>';

    Modal::end();
    $i=0;
    ?>

   

    <?php Pjax::begin(['id' => 'grid-arrival']) ?>
<?php
if (Yii::$app->request->get('name')) {
	$name=Yii::$app->request->get('name');
	$id=Yii::$app->request->get('id');
	
$script = <<< JS

$(".grid-view-mobile").removeClass("kv-table-wrap" );
 $(document).ready(function(){
  
    $("#$name"+$id ).focus();
	$("#$name"+$id).select();


	$(".form-control").click(function(){
		$("#"+this.id ).focus();
		$("#"+this.id).select();
			
	});		
});
JS;
$this->registerJs($script);

}
$script = <<< JS

$(".grid-view-mobile").removeClass("kv-table-wrap" );
JS;
$this->registerJs($script);
?>

	<?php 
		
					echo GridView::widget([
					'dataProvider' => $dataProvider,
					
					
					'tableOptions' => [
						'style'=>'font-size:10pt;width:100%',
						'class' => 'table-rena table-rena2 grid-view-mobile',
						'onLoad'=>"$('.grid-view-mobile').removeClass('kv-table-wrap' )"

					],
					
					  'rowOptions' =>
					[
						//'class' =>'myy'
					],
					'columns' => [
						['class' => 'kartik\grid\SerialColumn'],

					  //  'id',
					   // 'id_user',

						[
							'attribute' =>'id_product',
							'label' =>'Malın adı',
							'value' => 'idProduct.name',
							'width' => '250px',


						],
						[
							'attribute' => 'quantity',
							'label' =>'Miqdar',
							'format' => 'raw',
							'width' =>'35px',
							'value' => function ($model, $index, $widget)  use (&$i) {
								 $i++;
								return Html::input('text', 'quantity[]', $model->quantity, [ 'size' => '2', 'onChange' => "editQuantity($model->id,this.value,$i)"]);
							}
						],
						[
							'attribute' => 'price',
							'format' => 'raw',
							'label' =>'Gəliş ',
							
							'encodeLabel' => false,

							'value' => function ($model, $index, $widget)  use (&$i) {
                               
					            return Html::input('text', 'price[]', $model->price, [ 'size' => '2','id'=>"price".$i, 'onChange' => "editPrice($model->id,this.value,$i)"]);
							}
						],
					
						
						
						['class' => 'kartik\grid\ActionColumn', 'template' => '{delete}',
						'buttons' => [
							'delete' =>function ($url, $model) {
								return Html::button('<i class="glyphicon glyphicon-delete"></i>Silmək',['onclick'=>" deleteArrival2($model->id)"]);}
						],

						],

					],
				]); 

		
		
		?>
			

<div  class='arrivalBraches'>
	<? if ($model->id_store)  $store=$model->id_store;?>
    <?=Html::img('../img/storage.png')." Anbar". Select2::widget([
        'data' => ArrayHelper::map(Store::find()->all(), 'id', 'name'),
        'name' => 'store',
		'value'=>$store,
        'options' => [
            'placeholder' => 'Seçin',
			
            'id'=>'store8',

        ],
		
    ]); ?>
	<? if (!$model->id_contr) $contr= Yii::$app->session->get('contractor'); else $contr=$model->id_contr;?>
    <?=Html::img('../img/contractor.png')." Şirkət". Select2::widget([
        'data' =>  ArrayHelper::map(Contractor::find()->andWhere("id>=1")->all(), 'id', 'name'),
        'name' => 'contractor',
		'value'=> $contr,
        'options' => [
            'placeholder' => 'Seçin',

            'id'=>'contractor',

        ]
    ]); ?>
    <br>
    <p style="color:red;font-size: 30px; padding-left:10px" > <?= round($sum,2)." AZN"?></p>
   <!-- <p style="color:red;font-size: 30px; padding-left:10px" > <?= round($usd,2)." USD"?></p>-->
   <? if (!$model->datetime) $date= date('Y-m-d'); else $date=$model->datetime;?>
    <?=Html::img('../img/calendar.png')." Date" .
    DatePicker::widget([
        'name' => 'check_issue_date',
        'id' => 'date',
        'value' => $date,
        'options' => ['placeholder' => 'Select issue date ...'],
        'type' => DatePicker::TYPE_INPUT,
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
            'todayHighlight' => false
        ]
    ]); ?>

    <br>

 
    <?= Html::button('<i class="glyphicon glyphicon-time"></i>  Gözləmə', ['class' => 'btn btn-info', 'onclick' => 'postponedArrival2($("#store8").val(),$("#contractor").val())']); ?>
<br><br>
   <?= Html::button('<i class="glyphicon glyphicon-remove"></i> Hamsını sil', ['class' => 'btn btn-danger', 'onclick' => 'deleteAll()']); ?>
</br></br>	 
	
</div>
    <?php Pjax::end(); ?>
</div>
  </div>
<?php
$script = <<< JS
//$(".grid-view-mobile").removeClass("kv-table-wrap" );
 $(document).ready(function(){
    $("#barcode").val("");
    $("#barcode").focus();
});
JS;
$this->registerJs($script);
?>