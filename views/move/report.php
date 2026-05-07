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
/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Arrivals';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="arrival-index">
    <table class="table-rena kv-grid-table table table-bordered  kv-table-wrap">
        <thead>
        <th>Дата</th>
        <th>Приход</th>
        <th>Расход</th>
        <th>Конечный остаток</th>
        </thead>
        <tbody>
             <?php foreach($store as $store1) {
                echo "<tr><td colspan='4' style='background-color:red'>$store1->name</td> </tr>";
                 $sum=0;
                foreach($model as $product) {
                    $parent=TypeProduct::find()->where(["id" =>  $product->id_type])->one();
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


                    $name.="/".$product->name;
                    echo "<tr><td colspan='4' class='danger'>$name</td> </tr>";



                  /*  $sell=Sell::find()->select("quantity, datetime")->where(['id_product'=>$product->id,'id_store'=>$store1->id]);
                    $arrival=Arrival::find()->select("quantity, datetime")->where(['id_product'=>$product->id,'id_store'=>$store1->id]);
                    $transfer=Transfer::find()->select("quantity, date")->where(['id_product'=>$product->id])->andWhere("whence=$store1->id or whered=$store1->id");
                  //  $union=$arrival->union($sell)->all();*/
                    $union=Yii::$app->db->createCommand("SELECT * FROM
                    (
                    (SELECT quantity, datetime,number,'0' as flag,'0' as whence,'0' as whered FROM sell WHERE id_product=$product->id AND id_store=$store1->id) UNION
                    (SELECT quantity, datetime,number,'1'as flag,'0' as whence,'0' as whered FROM arrival WHERE id_product=$product->id AND id_store=$store1->id) UNION
                    (SELECT quantity, date as datetime,number,'-1'as flag,whence,whered FROM transfer WHERE id_product=$product->id AND (whence=$store1->id or whered=$store1->id))
                    ) as C  WHERE datetime>='$date1' AND datetime<='$date2'
                     ORDER BY C.datetime ASC")->queryAll();


                        foreach($union as $move) {
                            echo "<tr><td colspan='4' class='success'>$name</td> </tr>";
                            if ($move['flag']==0)
                            { $sum=$sum-$move[quantity];
                                echo "<tr>
                                        <td>Расходная накладная №$move[number] от $move[datetime]</td>
                                        <td></td>
                                        <td>$move[quantity]</td>
                                        <td>$sum</td>";
                            }

                            else  if ($move['flag']==1) {
                                $sum=$sum+$move[quantity];
                                echo "<tr>
                                        <td>Приходная накладная №$move[number] от $move[datetime]</td>
                                        <td>$move[quantity]</td>
                                        <td></td>
                                        <td>$sum</td>";
                            }

                            else  if ($move['whence']==$store1->id) {
                                $sum=$sum-$move[quantity];
                                echo "<tr>
                                        <td>Трансферная накладная №$move[number] от $move[datetime]</td>
                                        <td></td>
                                        <td>$move[quantity]</td>
                                        <td>$sum</td>";
                            }
                            else if ($move['whered']==$store1->id) {
                                $sum=$sum+$move[quantity];
                                echo "<tr>
                                        <td>Трансферная накладная №$move[number] от $move[datetime]</td>
                                        <td>$move[quantity]</td>
                                        <td></td>
                                        <td>$sum</td>";
                            }



}
                }


                }?>
        </tbody>
  </div>
