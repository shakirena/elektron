<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PostponedSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="postponed-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'id_product') ?>

    <?= $form->field($model, 'quantity') ?>

    <?= $form->field($model, 'id_store') ?>

    <?= $form->field($model, 'id_user') ?>

    <?php // echo $form->field($model, 'id_sell') ?>

    <?php // echo $form->field($model, 'date') ?>

    <?php // echo $form->field($model, 'received') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
