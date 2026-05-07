<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\TypeBalance */

$this->title = 'Create Type Balance';
$this->params['breadcrumbs'][] = ['label' => 'Type Balances', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="type-balance-create">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
