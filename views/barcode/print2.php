<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use app\models\Arrival;
use barcode\barcode\BarcodeGenerator as BarcodeGenerator;
use app\models\Barcodep;
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
				$barcode = Barcodep::find()->where(["id_product" => $res->id_product])->one();
			   if (strlen($barcode->name)>6) continue;
               preg_match_all('/(?<!\d)(\d+)(?!\d)/', $barcode->name, $m);
$pricesell=Arrival::find()->where(['id_product'=>$res->id_product])->orderBy("id DESC")->one()->pricesell;


				echo "<div class='print'  id='showBarcode$i'></div>";
			
			
				$optionsArray = array(
				'elementId'=> "showBarcode$i", /* div or canvas id*/
				'settings'=> array(
					'barHeight'=>'35'
				),
				'value'=> $barcode->name, /* value for EAN 13 be careful to set right values for each barcode type */
				'type'=>'code39',/*supported types  ean8, ean13, upc, std25, int25, code11, code39, code93, code128, codabar, msi, datamatrix*/
				
				);
				
				$pricesell=Arrival::find()->where(['id_product'=>$res->id_product])->orderBy("id DESC")->one()->pricesell;
				
				// --- Feature #24: обрезка длинных названий для этикетки 40x20мм ---
				$productName = $res->idProduct->name;
				$maxChars = 28;
				if (mb_strlen($productName, 'UTF-8') > $maxChars) {
				    $displayName = mb_substr($productName, 0, $maxChars, 'UTF-8') . '…';
				    $size = '9px';
				} elseif (strlen($productName) > 30) {
				    $displayName = $productName;
				    $size = '9px';
				} else {
				    $displayName = $productName;
				    $size = '12px';
				}
				// --- конец Feature #24 ---
				echo "<div align='center' style='margin-bottom:16px'>"
				    . BarcodeGenerator::widget($optionsArray)
				    . "<span style='font-size:" . $size . " !important'>" . Html::encode($displayName) . "</span>"
				    . "<br><span style='font-size:15pt !important'><b>" . $pricesell . " AZN</b></span><br>"
				    . "</div>";
              // $pkgs[$i] = array('name' => $res->idProduct->name, 'sku' => $barcode->name,'price'=>$pricesell);
               $i++;
               //$count++;


           
       }

    }

?>