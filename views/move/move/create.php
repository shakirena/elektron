<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Move */

$this->title = 'Create Move';
$this->params['breadcrumbs'][] = ['label' => 'Moves', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="move-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
