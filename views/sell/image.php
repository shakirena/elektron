<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use kartik\grid\GridView;
use kartik\date\DatePicker;
use app\models\Client;
use app\models\Contractor;
use app\models\Product;
use kartik\select2\Select2;
use app\models\TypeProduct;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SellSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sell-index" xmlns="http://www.w3.org/1999/html">
<?php
foreach ($model as $sell)
{

    $product=Product::find()->where(["id"=>$sell->id_product])->one();
   echo "<br>".$product->name."<br>".Html::img("../img/images/".$product->id."/".$product->image);
}


?>
</div>
