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
use app\models\Arrival;
use kartik\date\DatePicker;
use app\models\Master;
/* @var $this yii\web\View */
/* @var $searchModel app\models\DeviceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


?>
<div>
    <table class="table-rena kv-grid-table table table-bordered table-striped kv-table-wrap">
        <thead>
        <th>Malın adı</th>
        <th>Say</th>
        <th><?= Html::checkbox("",false,['onclick'=>"toggle(this)"])?></th>
        <th>Usta</th>
        </thead>
        <tbody>
        <?php foreach($model as $product){
            $arrival = Arrival::find()
                ->select('sum(rest) as rest')
                ->where(['id_product' => $product->id_product, 'id_store'=> Yii::$app->session->get("store")])
                ->groupBy('id_product')
                ->one();

            ?><tr>
            <?php
            $parent=TypeProduct::find()->where(["id" =>  $product->id_client])->one();
            if ($parent) $name2=$parent->name;

            $parent=TypeProduct::find()->where(["id" =>  $parent->id_parent])->one();
            if ($parent) $name1=$parent->name."/";

            $parent=TypeProduct::find()->where(["id" =>  $parent->id_parent])->one();
            if ($parent) $name1=$parent->name."/";

            $parent=TypeProduct::find()->where(["id" =>  $parent->id_parent])->one();
            if ($parent) $name1=$parent->name."/";

            $parent=TypeProduct::find()->where(["id" =>  $parent->id_parent])->one();
            if ($parent) $name1=$parent->name."/";


            $name=$name1.$name2;


            $name.="/".$product->name_product;?>
            <td><?= $name?></td>
            <?php if ($arrival->rest > 0)  { ?>
            <td><?= Html::input('text', 'quantity[]', $product->quantity, [ 'size' => '1','id'=>'return_id'.$product->id])?></td>
            <td><?= Html::checkboxList("select",null,[$product->id=>""],["id" => "select"])?></td>
            <td><?=Html::dropDownList("",false,ArrayHelper::map(Master::find()->all(), 'id', 'name'),[
                    'prompt' =>'',"id" => "master".$product->id
                ])?></td>
            <?php }
            else
            {
                echo "<td></td><td></td><td></td>";
            }?>
             </tr>
        <?php  } ?>
        </tbody>
    </table>
    <?=" Tarixi" .
    DatePicker::widget([
        'name' => 'check_issue_date',
        'id' => 'date_issue',

        'options' => ['placeholder' => 'Select issue date ...'],
        'type' => DatePicker::TYPE_INPUT,
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
            'todayH
            ighlight' => false
        ]
    ]); ?>
  <!--  <?= Html::dropDownList("",1,ArrayHelper::map(Store::find()->all(), 'id', 'name'),["id"=>store,'class' => 'form-control'])?>-->
    <br>
    <?= Html::button('<i class="glyphicon glyphicon-ok"></i> <i class="glyphicon glyphicon-send"></i> OK', ['class' => 'btn btn-success', 'onclick' => 'receivedSelectPostponed()']); ?>


</div>
