<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Kassa;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $model app\models\Finance */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="finance-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'from_kassa')->widget(Select2::className(),[
                'data' =>  ArrayHelper::map(Kassa::find()->all(), 'id', 'name'),
                'options' => [
                    'placeholder' => 'Seçin',


                ],


            ]);
			?>

    <?= $form->field($model, 'to_kassa')->widget(Select2::className(),[
                'data' =>  ArrayHelper::map(Kassa::find()->all(), 'id', 'name'),
                'options' => [
                    'placeholder' => 'Seçin',


                ],


            ]);
			?>

    <?= $form->field($model, 'sum')->textInput() ?>

    <?= $form->field($model, 'note')->textarea(['rows' => 6]) ?>

 

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
