<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use kartik\grid\GridView;
use kartik\date\DatePicker;
use app\models\Client;
use app\models\Store;
use app\models\Contractor;
use app\models\Product;
use app\models\Users;
use kartik\select2\Select2;
use app\models\TypeProduct;
use app\components\PushAll;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SellSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->params['breadcrumbs'][] = $this->title;
if (  Yii::$app->user->identity->id_role==1)  $role=0;

else $role=1;
?>
<div class="sell-index" xmlns="http://www.w3.org/1999/html">
    <br>
    <div class="noprint">
    <div class="btn-group">
    <?= Html::input("text",'barcode','',['id'=>'barcode','size'=>'15', 'class' => 'form-control','onChange' => 'addSellBarcodeMobile($("#barcode").val())'])?>
    </div>
    <div class="btn-group">
    <?= Html::button('<i class="glyphicon glyphicon-search"></i>', ['value' => Url::to(['sell/find-mobile']), 'class' => 'btn btn-danger', 'id' => 'sell_dialog_mobile']) ?>
     </div>

     <div class="btn-group">
        <?= Html::button('<i class="glyphicon glyphicon-time"></i>', ['value' => Url::to(['sell/postponed2']), 'class' => 'btn btn-info', 'id' => 'postponed_dialog']) ?>

    </div>


    <div class="btn-group">
        <?= HTML::a("<i class='glyphicon glyphicon-user'></i>",['sell/client2'],['class' => 'btn btn-warning']);
		 ?>
    </div>  

		<?php if ($model->id_client) {
										Yii::$app->session->set('id_client',$model->id_client);
										Yii::$app->session->set('client',Client::find()->where(['id_client'=>$model->id_client])->one()->fio);
									}?>
	
    <span style="margin-left:10pt;font-size: 16pt; text-decoration: double"><?= Yii::$app->session->get('client')?></span>
    </div>
    <br>
    </div>
    <?php
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
            'id' => 'sell-modal-mobile',
            'tabindex' => true,

        ],

 'size' => 'modal-sm',

    ]);

    echo '<div id="modalContent"></div>';

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

    Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'client-modal',
            'tabindex' => true,
        ],

        'size' => 'modal-lg',

    ]);

    echo '<div id="clientContent1"></div>';
   echo '<div id="clientContent"></div>';
    Modal::end();
	

	
    Modal::begin([
        'header' => '<h2>Yeni müştəri  yarat</h2>',
        'id' => 'client-create',
        'size' => 'modal-sm',

    ]);

    echo '<div id="clientContent1"></div>';

    Modal::end();
    Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'dclient-modal',
            'tabindex' => true,
        ],

        'size' => 'modal-lg',

    ]);

    echo '<div id="dclientContent"></div>';

    Modal::end();
    //--------------------------------
    Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'return-modal',
            'tabindex' => true,
        ],

        'size' => 'modal-lg',

    ]);

    echo '<div id="returnContent"></div>';

    Modal::end();

    Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'postponed-modal',
            'tabindex' => true,


        ],
        'size' => 'modal-rena-lg',



    ]);

    echo '<div id="postponedContent"></div>';

    Modal::end();
	  $i=0;
    ?>
    <div class="row noprint" style="padding:15px">
        <?php Pjax::begin(['id' => 'grid-arrival' ]); ?>
 


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
       // 'filterModel' => $searchModel,
        'tableOptions' => [
            'style'=>'font-size:10pt;width:100%',
            'class' => 'table-rena table-rena2 grid-view-mobile',
			'onLoad'=>"$('.grid-view-mobile').removeClass('kv-table-wrap' )"
			

        ],
		'options' => ['class' =>  ' grid-view-mobile' ],


        'columns' => [
            ['class' => 'kartik\grid\SerialColumn',
			'width'=>'20px'],
        /*    [

                'label' =>'Info',
                'value' => 'getImage',
                'format' => 'raw',

            ],*/
            [

                'label' =>'Malın adı',

                'value' => 'nameProduct',
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true]
                ],
                'filterType' => GridView::FILTER_SELECT2,
                
				 'format' => 'raw',
                'filterInputOptions' => ['placeholder' => 'Any type']
            ],
            [
                'attribute' => 'quantity',
                'label' =>'Say',
            
                'format' => 'raw',
                'value' => function ($model, $index, $widget){
                          
                    return Html::input('text', 'quantity[]', $model->quantity, ['class' => 'form-control input-sm','style' => 'width:50px !important', 'size' => '2', 'onChange' => "editQuantity($model->id,this.value)"]);
                }
            ],

      
          [
                'attribute' => 'price',
                'label' => 'azn',
				'encodeLabel' => false,
             
                'format' => 'raw',
                'value' => function ($model, $index, $widget)  use (&$i)  {
					   $i++;
                    return Html::input('text', 'price[]', $model->price, ['class' => 'form-control', 'style' => 'width:50px !important','size' => '2','id'=>"price".$i, 'onChange' => "editPrice($model->id,this.value)"]);
                }
            ],
			[
                'attribute' => 'sum',
               
            ] ,
		

           

            [
				'class' => 'kartik\grid\ActionColumn', 
				'template' => '{delete}',
				'buttons'=>[

                              'delete' => function ($url, $model) {	

										return Html::a('<span class="glyphicon glyphicon-trash"></span>', 'delete2?id='.$model->id, [
															'title' => Yii::t('yii', 'Delete2'),

																]);                                

									}
 ]    
			
			],
        ],
            // 'debt',

    ]); ?>




 
  <div style="width:40%; float:right">
	<? if (!$model->id_store) $store= 1; else $store=$model->id_store;?>
	    <?=" <b>Anbar</b>". Select2::widget([
        'data' => ArrayHelper::map(Store::find()->all(), 'id', 'name'),
        'name' => 'store',
		'value'=>$store,
        'options' => [
            'placeholder' => 'Seçin',

            'id'=>'store2',

        ]
    ]); ?>
	</div>
	<div style="width:40%">
	<?php  if (!$user) $user=Yii::$app->user->identity->id_user;?>
	    <?=" <b>Satıcı</b>". Select2::widget([
        'data' => ArrayHelper::map(Users::find()->all(), 'id_user', 'fio'),
        'name' => 'user',
		'value'=>$user,
        'options' => [
            'placeholder' => 'Seçin',

            'id'=>'user',

        ]
    ]); ?>
	</div>

      
  <!--      <?=Select2::widget([
            'data' =>  [0=>'Malar aparılır',1 => 'Malar aparılmır'],
            'name' => 'postponed',
            //'hideSearch' =>true,
            'options' => [
                'placeholder' => 'Seçin',

                'id'=>'postponed',

            ]
        ]); ?>-->
      
        <?="<div class='hid'> Date" .
        DatePicker::widget([
            'name' => 'check_issue_date',
            'id' => 'date',
            'value' => date("Y-m-d"),
            'options' => ['placeholder' => 'Select issue date ...'],
            'type' => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'format' => 'yyyy-mm-dd',
                'todayHighlight' => false
            ]
        ])."</div>" ;?>
<br>
        
         <?= Html::button(' <i class="glyphicon glyphicon-send"></i> Gözləməyə', ['class' => 'btn btn-info','id'=>'postponed1', 'onclick' => 'receivedSellMobile(0,$("#date").val(),1,$("#store2").val(),$("#user").val())']); ?> 
&nbsp &nbsp <br><br>
        <?= Html::button('<i class="glyphicon glyphicon-remove"></i> Ləğv Et', ['class' => 'btn btn-danger', 'onclick' => 'deleteAll()']); ?>
       </br></br>
	 


        <?php
        $script = <<< JS

$(".grid-view-mobile").removeClass("kv-table-wrap" );
 $(document).ready(function(){

 $('body').keydown(function(event){
//alert(event.which);
 if ( event.which==120 ) $("#money").focus();
 if ( event.which==118 ) $("#barcode").focus();	 


  if ( event.which==119 )  { $("#received").click(); event.which=0;}
  if ( event.which==117)  { $("#postponed_dialog").click(); event.which=0;}
  if ( event.which==115)  { $("#postponed1").click(); event.which=0;}
  
		  if ( event.which==113 )  { $("#sell_dialog").click(); event.which=0;}
		//if ( event.which==17 )  { $("#postponed1").click(); event.which=0;}
    });


   $('#money').keyup(function(){

if ($("#rate").val()==1)
    var sdacha=$("#money").val()-$("#sum").text()  ;
    else  var sdacha=$("#money").val()-$("#usd1").text()  ;
    sdacha=sdacha.toFixed(2)
    $("#sdacha").html(sdacha);

});
   $('#rate').change(function(){

if ($("#rate").val()==1)
    var sdacha=$("#money").val()-$("#sum").text()  ;
    else  var sdacha=$("#money").val()-$("#usd1").text()  ;
    sdacha=sdacha.toFixed(2)
    $("#sdacha").html(sdacha);

});
});
JS;
        $this->registerJs($script);
        ?>
        <?php Pjax::end(); ?>
    </div>
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

<style>
    @media print {
        .noprint, .modal-header {
            content: " ";
            display: none !important;;visibility: hidden !important;
        }
        .modal-content
        {
            border: none !important;
        }
    }
</style>