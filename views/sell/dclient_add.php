<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Client;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $model app\models\Sell */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sell-form">

    <?php $form = ActiveForm::begin(); ?>
	
    <?= $form->field($model, 'id_client')->widget(Select2::className(),[
        'data' =>  ArrayHelper::map(Client::find()->all(), 'id_client', 'fio'),
        'options' => [
            'placeholder' => 'Seçin',

        ]

    ]); ?>



    <?= $form->field($model, 'debt')->textInput()->label("Borc (AZN)") ?>

    <?= $form->field($model, 'usd')->textInput()->label("Borc (USD)") ?>

   

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
