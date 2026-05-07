<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\Store;
use app\models\TypeBalance;
use app\models\Contractor;
/* @var $this yii\web\View */
/* @var $model app\models\Balance */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="balance-form">

    <?php $form = ActiveForm::begin(); ?>



    <?= $form->field($model, 'datetime')->widget(DatePicker::className(),[

        'name' => 'datetime',
        'id' => 'date',
        'value' => date('Y-m-d'),
        'options' => ['placeholder' => 'Select issue date ...'],
        //'type' => DatePicker::TYPE_INPUT,
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
            'todayHighlight' => false
        ]
    ]);?>

    <?= $form->field($model, 'sum')->textInput() ?>

  <!--  <?=Select2::widget([
        'data' =>  [0=>'Mədaxil',1 => 'Məxaric'],
        'name' => 'tyype',
        'options' => [
            'placeholder' => 'Seçin',

            'id'=>'tyype',
            'onchange'=> 'typeOperation($("#tyype").val())'
        ]
    ]); ?>-->
    <br>


    <?="Type".  Select2::widget([
        'name' => 'Balance[id_type]',
        'data' => ArrayHelper::map(TypeBalance::find()->where(["type" =>1])->all(), 'id', 'name'),
        'options' => [
            'placeholder' => 'Seçin',

            'id'=>'id_type',

        ]
    ]);?>
    <br>
    <?= $form->field($model, 'id_store')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(Store::find()->all(), 'id', 'name'),
        'options' => ['placeholder' => 'Select a state ...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'note')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'user_name')->textInput(['maxlength' => true]) ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Ok' : 'Dəyişmək', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
