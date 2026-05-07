<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Debt */

$this->title = 'Create Debt';
$this->params['breadcrumbs'][] = ['label' => 'Debts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="debt-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
