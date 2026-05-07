<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Dclient */

$this->title = 'Create Dclient';
$this->params['breadcrumbs'][] = ['label' => 'Dclients', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dclient-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
