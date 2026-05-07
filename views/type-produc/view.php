<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
/* @var $this yii\web\View */
/* @var $model app\models\TypeProduct */

$this->title = $model->name;
//$this->params['breadcrumbs'][] = ['label' => 'Type Products', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="type-product-view updateType">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
		 <?= Html::button('Print <i class="glyphicon glyphicon-print"></i>', ['class' => 'btn btn-success', 'onclick' => "printProduct($model->id)"]) ?>
       
    </p>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
        ],
    ]) ?>
	
	 <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions'=>[
            'style'=>'width:800px;',
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
           
            [  
				'attribute' => 'barcode',
                'value' => 'nameBarcode',
				'width' => '50px',
				'filterInputOptions' => ['id'=>'barcode','class'=>'form-control']
            ],
		
			
            ['class' => 'kartik\grid\ActionColumn'],
        ],
    ]); ?>

</div>
