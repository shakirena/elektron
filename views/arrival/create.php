<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Arrival */

$this->title = 'Create Arrival';
$this->params['breadcrumbs'][] = ['label' => 'Arrivals', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="arrival-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
