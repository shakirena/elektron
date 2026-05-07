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
<div class="container  ">
   
    <div class="col-md-2">
    


		<div class='form-horizontal'>

        <?="Password".Html::input('password','password', '', ['class' => 'form-control input-sm', 'size' => '3','id'=>'password']) ?>

    
        <div class="form-group">
            <div class="col-lg-offset-8 col-lg-2">
                <?= Html::button('Login', ['class' => 'btn btn-primary', 'name' => 'login-button','onclick'=>'returnArrival()']) ?>
            </div>
        </div>

        </div>
    </div>
    <div class="col-md-4"></div>
</div>

