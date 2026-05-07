<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ArrivalSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="arrival-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'id_user') ?>

    <?= $form->field($model, 'id_product') ?>

    <?= $form->field($model, 'quantity') ?>

    <?= $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'usd') ?>

    <?php // echo $form->field($model, 'pricesell') ?>

    <?php // echo $form->field($model, 'sum') ?>

    <?php // echo $form->field($model, 'id_store') ?>

    <?php // echo $form->field($model, 'received') ?>

    <?php // echo $form->field($model, 'datetime') ?>

    <?php // echo $form->field($model, 'id_contr') ?>

    <?php // echo $form->field($model, 'number') ?>

    <?php // echo $form->field($model, 'discount') ?>

    <?php // echo $form->field($model, 'returnp') ?>

    <?php // echo $form->field($model, 'rest') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
