<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use \app\models\TypeProduct;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use kartik\select2\Select2;
use app\models\Store;

use kartik\date\DatePicker;
/* @var $this yii\web\View */
/* @var $searchModel app\models\DeviceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


?>
<div>
    <table class="table-rena kv-grid-table table table-bordered table-striped kv-table-wrap">
        <thead>
        <th>Malın adı</th>
        <th>Say</th>
        <th>Ok</th>
        </thead>
        <tbody>
        <?php foreach($model as $product){
            ?><tr>
            <td><?= $product->name_product?></td>
            <td><?= Html::input('text', 'quantity[]', $product->quantity, [ 'size' => '1','id'=>'return_id'.$product->id])?></td>
            <td><?= Html::checkboxList("select",null,[$product->id=>""],["id" => "select"])?></td>
            </tr>
        <?php  } ?>
        </tbody>
    </table>
    <?='Причина'. Html::input("text",'',null,["id" => "reason",'size'=>'35'])?>

    <?='Возврат суммы'. Html::input("text",'',0,["id" => "money",'size'=>'3'])?>
    <br> <br>
    <?=" Satış nöqtəsi". Select2::widget([
        'data' => ArrayHelper::map(Store::find()->all(), 'id', 'name'),
        'name' => 'store',
        'value' => 1,
        'options' => [
            'placeholder' => 'Seçin',
            'style' => 'width:100px !important',
            'id'=>'store',

        ]
    ]); ?>

    <?=" Date" .
    DatePicker::widget([
        'name' => 'check_issue_date',
        'id' => 'date',
        'value' => date('Y-m-d'),
        'options' => ['placeholder' => 'Select issue date ...'],
        'type' => DatePicker::TYPE_INPUT,
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
            'todayHighlight' => false
        ]
    ]); ?>
    <br>
    <?= Html::button('<i class="glyphicon glyphicon-ok"></i> <i class="glyphicon glyphicon-send"></i> OK', ['class' => 'btn btn-success', 'onclick' => 'receivedSelectReturn()']); ?>


</div>
