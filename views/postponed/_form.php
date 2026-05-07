<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

use kartik\select2\Select2;
use app\models\Master;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\Postponed */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="postponed-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_master')->widget(Select2::className(),[
        'data' =>  ArrayHelper::map(Master::find()->all(), 'id', 'name'),
        'options' => [
            'placeholder' => 'Seçin',

        ]

    ]); ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
