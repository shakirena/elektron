<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\TypeProduct */

//$this->title = 'Create Type Product';
$this->params['breadcrumbs'][] = ['label' => 'Type Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="type-product-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'type' => $type,
    ]) ?>

</div>
