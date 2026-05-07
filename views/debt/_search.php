<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\DebtSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="debt-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'id_user') ?>

    <?= $form->field($model, 'debt') ?>

    <?= $form->field($model, 'sum') ?>

    <?= $form->field($model, 'datatime') ?>

    <?php // echo $form->field($model, 'number') ?>

    <?php // echo $form->field($model, 'id_contr') ?>

    <?php // echo $form->field($model, 'discount') ?>

    <?php // echo $form->field($model, 'sum_usd') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
