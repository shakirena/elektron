<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ClientSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
      <?php echo HTML::a("<i class='glyphicon glyphicon-arrow-left'></i>",['index2'],['class' => 'btn btn-danger']);?>
	 <?= HTML::a("<i class='glyphicon glyphicon-plus'></i> Əlavə et",['client/create-mobile'],['class' => 'btn btn-warning']);?>
	 
    </p>
    <?php

    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' =>
            function ($dataProvider, $key, $index, $grid) {
                return ['id' => $dataProvider['id_client'],

                    //'value' => Url::to(['arrival/add']),
                    'onClick'=>'selectClientMobile(this.id)'
                ];
            },
        'tableOptions'=>[
            'style' => 'width:100%;cursor:pointer',
            'class' => 'table-rena table-rena2'
        ],


        'pjax' =>true,
        'columns' => [
            [
				'class' => 'kartik\grid\SerialColumn',
				'width'=>'20px'
			],

            //'id_client',
            'fio',
          
           // ['class' => 'kartik\grid\ActionColumn', 'template' => '{delete}{update}'],
        ],
    ]); ?>
</div>
<?php
$script = <<< JS

$("#client9").click(function(){

$("#client-create").modal("show")
        .find("#clientContent1")
        .load($(this).attr("value"));
});
JS;
$this->registerJs($script);
?>
<?php
$script = <<< JS

 
$(document).on('pjax:success', function() {
  $(".table-rena2").removeClass("kv-table-wrap" );
 
});
	$(".table-rena2").removeClass("kv-table-wrap" );
  


JS;
$this->registerJs($script);
?>