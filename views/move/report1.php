<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use app\models\Client;
use app\models\TypeProduct;
use app\models\Users;
use app\models\Sell;
use app\models\Arrival;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Arrivals';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="arrival-index">


    <?php $clientList = ArrayHelper::map(Client::find()->all(), 'id_client', 'fio'); ?>
    <?php $user = ArrayHelper::map(Users::find()->all(), 'id_user', 'fio'); ?>
    <?php $typeList = ArrayHelper::map(TypeProduct::find()->all(), 'id', 'name');
    ?>
    <button id="w3" class="btn btn-default dropdown-toggle" title="Export" data-toggle="dropdown" aria-expanded="true"><i class="glyphicon glyphicon-export"></i>  <span class="caret"></span></button>
    <table class="table-rena kv-grid-table table table-bordered table-striped kv-table-wrap">
        <thead>
             <th>Дата</th>
            <th>Приход</th>
             <th>Расход</th>
             <th>Конечный остаток</th>
        </thead>
        <tbody>
            <?php foreach($store as $row) {
                    foreach($arrival as $row1)
                        if($row[id]==$row1[id_store]) break;
                        else {$row1[quantity]=0;continue;}

                    foreach($sell as $row2)
                        if($row[id]==$row2[id_store]) break;
                        else {$row2[quantity]=0;continue;}

                $rest=$row1[quantity]-$row2[quantity];
                    echo "<tr class='danger'>
                            <td>$row[name]</td>

                            <td>$row1[quantity]</td>
                             <td>$row2[quantity]</td>
                              <td>$rest</td>
                        </tr>";
                $rest=0;
                foreach($type as $id) {


                    echo "<tr class='warning'>
                            <td>$row[name]</td>

                            <td>$row1[quantity]</td>
                             <td>$row2[quantity]</td>
                              <td>$rest</td>
                        </tr>";
                    $arrival1 = Arrival::find()->select(" sum(quantity) as quantity, datetime,number")
                        ->where(["id_store" => $row[id], 'id_type' => $id])
                        ->joinWith("idProduct")
                        ->groupBy("number")->orderBy("datetime ASC")->all();
                    foreach ($arrival1 as $ar) {
                        $rest = $rest + $ar[quantity];
                        echo "<tr>
                            <td>Приходная накладная № $ar[number] от $ar[datetime]</td>

                            <td>$ar[quantity]</td>
                             <td>0</td>
                              <td>$rest</td>
                        </tr>";

                    }
                    $sell1 = Sell::find()->select(" sum(quantity) as quantity, datetime,number")
                        ->where(["id_store" => $row[id], 'id_type' => $id])
                        ->joinWith("idProduct")
                        ->groupBy("number")->orderBy("datetime ASC")->all();
                    foreach ($sell1 as $se) {
                        $rest = $rest - $se[quantity];
                        echo "<tr>
                            <td>Расходная накладная № $se[number] от $se[datetime]</td>

                            <td>0</td>
                             <td>$se[quantity]</td>
                              <td>$rest</td>
                        </tr>";
                    }

                }
                }
            ?>
        </tbody>
    </table>
  </div>
