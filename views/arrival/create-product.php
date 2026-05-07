<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\TypeProduct;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use unclead\multipleinput\MultipleInput;
/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */


if ($model->isNewRecord ) $new=1; else $new=0;
?>
<div class="product-create">
<div class="product-form">
	<?php
		if ($error==1) echo "<div class='bg-danger'>Bu barkod artıq mövcuddur!</div>";
	?>
    <?php $form = ActiveForm::begin(["id"=>"product",'enableAjaxValidation' => false]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true,]) ?>


    <?= $form->field($model, 'id_type')->widget(Select2::className(),[
        'data' =>  ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name'),
        'options' => [
            'placeholder' => 'Seçin',

        ]

    ]); ?>

     <?= MultipleInput::widget([
        'model'=>$barcode,
        'attribute' => 'name',


        'rowOptions' => [
           'style' =>"width",
            'id' => 'barcode',
            'class' => 'barcodiki',
        ],

        'data' =>\app\models\Barcodep:: find()->select("name")->where(["id_product"=>$model->id])->all(),//\app\models\Barcode::find()->->where(["id_product" => $model->id_product]),

        //'max'               => 6,
        'min'               => 1, // should be at least 2 rows
        'allowEmptyList'    => false,
        'enableGuessTitle'  => true,
       // 'addButtonPosition' => MultipleInput::POS_HEADER // show add button in the header
    ])?>
	 <?= $form->field($image, 'imageFiles[]')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>
	<? if (!$model->boxing) $model->boxing=0;?>
	<?= $form->field($model, 'boxing')->textInput(['maxlength' => true,]) ?>
	<div class="form-group">
	<?php 
	if( $model->isNewRecord) {
		echo Html::button($model->isNewRecord ? 'Əlavə et' : 'Yaddaşa ver', ['onclick'=>"addProduct($new)",'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']); }
	else 
		echo Html::submitButton(  'Yaddaşa ver', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>	
			
    </div>
    <?php ActiveForm::end(); ?>

</div>
</div>