<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Arrival */

$this->title = 'Düzəliş: ' . $product->name;

?>
<div class="arrival-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'product' =>$product,
        'barcode' =>$barcode
    ]) ?>

</div>
