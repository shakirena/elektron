<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Client */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="client-form">
    <?php echo HTML::a("<i class='glyphicon glyphicon-arrow-left'></i>",['/sell/client2'],['class' => 'btn btn-danger']);?>
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'fio')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'adress')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
  <?= $form->field($model, 'barcode')->textInput(['maxlength' => true, "id" => "bonus"]) ?>
   


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Əlavə et' : 'Yaddaşa ver', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
