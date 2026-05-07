<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Finance */

$this->title = 'Create Finance';

?>
<div class="finance-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
