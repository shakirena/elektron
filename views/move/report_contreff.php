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
use app\models\Sell;
use app\models\Arrival;
use app\models\Transfer;
use app\models\Client;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Arrivals';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="arrival-index">
    <table class="table-rena kv-grid-table table table-bordered  kv-table-wrap">
        <thead>
        <th>Tarix</th>
        <th>Borc</th>
        <th>Ödənilib</th>

        <th>Yekun qalıq</th>
		 <th>Borc ($)</th>
		 <th>Yekun qalıq ($)</th>
        </thead>
        <tbody>
        <?php echo "<tr><td colspan='6' class='danger'>Şirket: $contractor</td> </tr>"; ?>
        <?php
        $sum=0;
            foreach($model as $move) {
                if ($move['flag']==0) {
                    if ($move[sum]!=0) $sum=round($move[sum],2);
                    else $sum=round($move[sum1],2);

                    echo "<tr>
                                        <td><a href='../arrival/report1?number=$move[number]'> Приходная накладная (№$move[number]) $move[datetime] tarixdən</a></td>
                                        <td>$sum</td>
                                        <td></td>
                                        <td>$sum</td>";
                }
                if ($move['flag']==1) {
                    $sum=round($sum-$move[sum],2);
                    echo "<tr>
                                        <td><a href='../debt/report?number=$move[number]'>$move[datetime] tarixdən</a></td>
                                        <td></td>
                                        <td>$move[sum]</td>
                                        <td>round($sum,2)</td>";
                }


            }?>
        </tbody>
  </div>
