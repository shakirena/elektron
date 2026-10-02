<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\TypeProduct;
use app\models\DisplaySettingsForm;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$displaySettings = DisplaySettingsForm::current();

$this->title = 'Products';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-index">


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::button('<i class="glyphicon glyphicon-plus"></i>Əlavə et', ['value' => Url::to(['create-product']), 'class' => 'btn btn-danger', 'id' => 'product']) ?>
    </p>
    <?php
    Modal::begin([
        'header' => '<h2>Yeni mal adının açılması</h2>',
        'id' => 'product-create',
        'size' => 'modal-sm',
		  'options' => [
          
            'tabindex' => true,

        ],

    ]);

    echo '<div id="modalContent"></div>';

    Modal::end();
    ?>
    <?php $typeList = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name'); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions'=>[
            'style'=>'width:1000px;',
            'class' => 'table-rena table-rena2 ',

        ]
        ,
        'pjax' => true,
	//	'panel'=>['type'=>'primary','options'=>['style'=>'width:500px']],
        'columns' => [

            ['class' => 'kartik\grid\SerialColumn'],

           // 'id',
           [ 
				 'attribute' =>'name',
				 'width' => '600px',
			],
            ['attribute' => 'article_number', 'visible' => $displaySettings['show_article_number']],
            [
                'attribute' => 'id_type',
                'filter' => $typeList,
                'value' => 'idType.name',
                'filterWidgetOptions' => [
			
                    'pluginOptions' => ['allowClear' => true]
                ],
                'filterType' => GridView::FILTER_SELECT2,
                'width' => '200px',
                'filterInputOptions' => ['placeholder' => 'Any type']
            ],
            [  
				'attribute' => 'barcode',
                'value' => 'nameBarcode',
				'width' => '50px',
				'filterInputOptions' => ['id'=>'barcode','class'=>'form-control']
            ],
			[ 
				 'attribute' =>'boxing',
				 'width' => '40px',
			],
			[
				'label'=>'foto',
				'value'=>'getPhoto',
				'format'=>'raw'
			],
			[
				'label' => 'Sürətli satış',
				'format' => 'raw',
				'width' => '60px',
				'value' => function ($model) {
					return Html::checkbox('quick_access', (bool) $model->quick_access, [
						'onchange' => "$.get('quick-access', {id:$model->id, value:this.checked?1:0})",
					]);
				}
			],

            ['class' => 'kartik\grid\ActionColumn'],
        ],
    ]); ?>
</div>
<?php
$script = <<< JS

$(document).on('pjax:complete', function () {
  
   $("#barcode").focus();
    $("#barcode").select();
});
  

 $(document).ready(function(){
  
    $("#barcode").focus();
	
});
JS;
$this->registerJs($script);
?>