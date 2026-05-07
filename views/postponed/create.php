<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Postponed */

$this->title = 'Create Postponed';
$this->params['breadcrumbs'][] = ['label' => 'Postponeds', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="postponed-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
