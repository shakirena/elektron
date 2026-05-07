<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use kartik\date\DatePicker;
use kartik\dateRange\DateRangePicker;
use kartik\grid\GridView;
use app\models\Contractor;
use app\models\TypeProduct;
use app\models\Store;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Arrivals';
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="arrival-index">
    <table class="table-rena kv-grid-table table table-bordered table-striped kv-table-wrap">
        <thead>
        <th>Malın adı</th>
        <th>Say</th>

        </thead>
        <tbody>
        <?php foreach($model as $product){
            ?><tr>
            <td><?= $product->name?></td>
            <td><?= Html::input('text', 'quantity[]', 1, [ 'size' => '1','id'=>'return_id'.$product->id])?>
           <span id="select">
            <?= Html::checkbox("select",true,['value' =>$product->id ,'style' => 'visibility:  hidden '])?>
</span>
            </td>
            </tr>
        <?php  } ?>
        </tbody>
    </table>
      <br>
    <?= Html::button('<i class="glyphicon glyphicon-ok"></i> <i class="glyphicon glyphicon-send"></i> OK', ['class' => 'btn btn-success', 'onclick' => 'addSellType2()']); ?>



</div>
