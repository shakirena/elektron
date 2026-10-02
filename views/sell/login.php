<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Store;
$this->title = 'Login::: Merinos';
$sum = isset($_GET['sum']) && $_GET['sum'] ? $_GET['sum'] : 0;
$bonus = isset($_GET['bonus']) && $_GET['bonus'] ? $_GET['bonus'] : 0;
?>
<div class="form-horizontal" style="padding:10px 4px;">

    <div class="form-group">
        <label class="col-sm-5 control-label">Şifrəni daxil edin</label>
        <div class="col-sm-7">
            <?= Html::input('password', 'password', '', ['class' => 'form-control input-sm', 'id' => 'password']) ?>
        </div>
    </div>

    <div class="form-group" style="margin-top:10px;">
        <div class="col-sm-offset-5 col-sm-7">
            <?= Html::button('OK', ['class' => 'btn btn-primary', 'name' => 'login-button', 'onclick' => "returnSellReceived($sum, $bonus)"]) ?>
        </div>
    </div>

</div>

