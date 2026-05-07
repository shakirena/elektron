<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Returnp */

$this->title = 'Update Returnp: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Returnps', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="returnp-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
