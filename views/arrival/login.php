<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Store;
$this->title = 'Login::: Merinos';
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
            <?= Html::button('Login', ['class' => 'btn btn-primary', 'name' => 'login-button', 'onclick' => 'returnArrival()']) ?>
        </div>
    </div>

</div>

