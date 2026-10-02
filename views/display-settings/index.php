<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\DisplaySettingsForm */

$this->title = 'Görünüş tənzimləmələri';
?>
<div class="display-settings" style="max-width:500px;overflow:hidden;">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(); ?>

    <h4>Satış paneli</h4>
    <?= $form->field($model, 'show_discount')->checkbox() ?>
    <?= $form->field($model, 'show_store')->checkbox() ?>
    <?= $form->field($model, 'show_seller')->checkbox() ?>

    <h4>Malın məlumat cədvəli (Satış / Mal qəbulu / Hesabatlar)</h4>
    <?= $form->field($model, 'show_unit_price')->checkbox() ?>
    <?= $form->field($model, 'show_box_count')->checkbox() ?>
    <?= $form->field($model, 'show_shelf')->checkbox() ?>
    <?= $form->field($model, 'show_article_number')->checkbox() ?>
    <?= $form->field($model, 'show_sn')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('Yaddaşa ver', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
