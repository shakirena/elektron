<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Sell */

$this->title = 'Create Sell';
$this->params['breadcrumbs'][] = ['label' => 'Sells', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sell-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
