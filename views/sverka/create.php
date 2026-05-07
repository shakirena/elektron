<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Sverka */

$this->title = 'Create Sverka';
$this->params['breadcrumbs'][] = ['label' => 'Sverkas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sverka-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
