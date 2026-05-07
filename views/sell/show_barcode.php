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
		
		<?php 
			$i=0;
			foreach ($products as $product) {
			$i++;
			echo "<button id=$i onclick='addSellId($product->id)'>$product->name</button><br><br>";
			
		}
		?>
    </div>
    <div class="col-md-4"></div>
</div>
<?php
$script = <<< JS

 $(document).ready(function(){
   setTimeout(function(){
										 $("#1").focus();
								
									},500);	
   
});
JS;
$this->registerJs($script);
?>
