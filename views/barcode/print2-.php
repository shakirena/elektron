<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use app\models\Arrival;
use barcode\barcode\BarcodeGenerator as BarcodeGenerator;
/* @var $this yii\web\View */
/* @var $searchModel app\models\BarcodeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Barcodes';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div id="showBarcode"></div>
<?php


$script = <<< JS
window.print();
JS;
$this->registerJs($script);

$i=1;
  foreach($model as $res)  {
       $count=0;
       for ($j=0;$j<$res->count;$j++) {
           //   print_r($res->id_product);
           foreach (\app\models\Barcodep::find()->where(["id_product" => $res->id_product])->all() as $barcode) {
			   if (strlen($barcode->name)>6) continue;
               preg_match_all('/(?<!\d)(\d+)(?!\d)/', $barcode->name, $m);
$pricesell=Arrival::find()->where(['id_product'=>$res->id_product])->orderBy("id DESC")->one()->pricesell;


				echo "<div class='print'  id='showBarcode$i'></div>";
			
			
				$optionsArray = array(
				'elementId'=> "showBarcode$i", /* div or canvas id*/
				
				'value'=> $barcode->name, /* value for EAN 13 be careful to set right values for each barcode type */
				'type'=>'code39',/*supported types  ean8, ean13, upc, std25, int25, code11, code39, code93, code128, codabar, msi, datamatrix*/
				
				);
				
				$pricesell=Arrival::find()->where(['id_product'=>$res->id_product])->orderBy("id DESC")->one()->pricesell;
				
				if (strlen($res->idProduct->name)>25) $size='6px';
				else $size='13px';
				echo "<div align='center' style='margin-bottom:15px'>".BarcodeGenerator::widget($optionsArray)."<span  style='font-size:$size'>".$res->idProduct->name."<b>$pricesell</b></span><br></div>";
              // $pkgs[$i] = array('name' => $res->idProduct->name, 'sku' => $barcode->name,'price'=>$pricesell);
               $i++;
               //$count++;


           }
       }

    }

?>