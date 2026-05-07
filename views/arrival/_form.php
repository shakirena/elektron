<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Arrival;
use app\models\TypeProduct;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;

use unclead\multipleinput\MultipleInput;
$model1=Arrival::find()->where(["id_product"=>$model->id_product])->orderBy("datetime DESC")->one();
$model2=Arrival::find()->select("sum(rest) as rest")->where(["id_product"=>$model->id_product,"id_store"=>$model->id_store])->groupBy("id_product")->one()->rest;
/* @var $this yii\web\View */
/* @var $model app\models\Arrival */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="arrival-form">

    <?php $form = ActiveForm::begin(); ?>



    <?= $form->field($product, 'name')->textInput(["value" => $product->name]) ?>
    <?= $form->field($product, 'id_type')->widget(Select2::className(),[
        'data' =>  ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name'),
        'options' => [
            'placeholder' => 'Seçin',

        ]

    ]); ?>

    <?= MultipleInput::widget([
        'model'=>$barcode,
        'attribute' => 'name',


        'rowOptions' => [
            'style' =>"width"
        ],

        'data' =>\app\models\Barcodep:: find()->select("name")->where(["id_product"=>$model->id_product])->all(),//\app\models\Barcode::find()->->where(["id_product" => $model->id_product]),

        //'max'               => 6,
        'min'               => 1, // should be at least 2 rows
        'allowEmptyList'    => false,
        'enableGuessTitle'  => true,
        // 'addButtonPosition' => MultipleInput::POS_HEADER // show add button in the header
    ])?>

  <!--  <?= $form->field($model, 'price')->textInput(["value" => $model1->price]) ?>-->

  
    <?= $form->field($model, 'pricesell')->textInput(["value" => $model1->pricesell]) ?>


   <!-- <?= $form->field($model, 'rest')->textInput(["value" => $model2]) ?>-->
	<?= $form->field($model, 'price_top')->label("Ədədin qyiməti")->textInput(["value" => $model1->price_top]) ?>
	<?= $form->field($model, 'pack')->label("Qutuda ədəd sayi")->textInput(["value" => $model1->pack]) ?>
	<?= $form->field($model, 'trade_price')->label("Topdan satış qiyməti")->textInput(["value" => $model1->trade_price]) ?>
	<?= $form->field($model, 'pricesell_min')->label("Minimum satış qiyməti")->textInput(["value" => $model1->pricesell_min]) ?>
	<!--<?= $form->field($model, 'polka')->textInput(["value" => $model1->polka]) ?>-->
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Yaddaşa ver', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
