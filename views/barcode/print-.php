<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use app\models\Arrival;
/* @var $this yii\web\View */
/* @var $searchModel app\models\BarcodeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Barcodes';
//$this->params['breadcrumbs'][] = $this->title;
?>
<?php
  foreach ($model as $barcode) {
	  $pricesell=Arrival::find()->where(['id_product'=>$barcode->id_product])->orderBy("id DESC")->one()->pricesell;
	  echo "<div align='center' style='margin-bottom:10px'><span  style='font-size:10pt'>".$barcode->idProduct->name."<br><b>$pricesell AZN</b></span><br></div>";
	  
  }
?>
<?php
$script = <<< JS
window.print();
JS;
$this->registerJs($script);
?>