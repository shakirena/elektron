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
use app\models\Costs;
use app\models\Returnp;
use app\models\Product;
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
			<th >Tarix</th>
			<th  width="50%">Qeyd</th>
			<th>Alıb</th>
			<th>Ödənilib</th>
			<th>İadə</th>
			<th>Yekun borc<br> qalığı</th>
			<th>Qeyd</th>
        </thead>
        <tbody>
        <?php echo "<tr><td colspan='10' class='danger'>Müştəri: $client</td> </tr>"; ?>
        <?php
        $debt=round($current,2);$sum=0;
		$sum_debt=0;$sum_voz=0;
		$bonus_sum = 0;
		$bonus_count = 0;
		
							echo "<tr>
									<td colspan=2>Carı borc</td>	
									<td></td>
									<td></td>
									<td></td>
									<td>$debt</td>
									<td></td>			
							</tr>";
				foreach($model as $move) {
					if ($move['debt']>0 || $move['sum']>0) {
							$sum=round($sum+$move[sum],2);
							if ($move[debt]==0) $move[debt]=$move[sum];
							else $move[debt]=$move[debt]+$move[sum]+$move[bonus];
							$sum_debt=$sum_debt+$move[debt];
							
							foreach(Sell::find()->where(['number' => $move->number])->all() as $sell)
							{
							$c=$debt;
							$debt=round($debt+$sell[sum],2);	
								$product = Product::find()->where(['id' => $sell->id_product])->one()->name;
								 echo "<tr>
								<td>$move[datetime] </td>
								<td> $product (Satış №$move[number])</td>
								<td>$sell[sum]</td>	
								<td></td>
								<td></td>
								<td>$debt</td>
								<td></td>

							
						 </tr>";
								
								
								
							}
							
							if ($move[sum]!=0) {
								$debt=round($debt - $move[sum],2);
								 echo "<tr>
									<td>$move[datetime] </td>
									<td>Ödənib </td>
									<td></td>	
									<td>$move[sum]</td>
									<td></td>
									<td>$debt</td>
									<td></td>

								
							 </tr>";
							}
							if (!$move['number']) {
								$debt=round($debt +$move[debt],2);
									  echo "<tr>
									<td>$move[datetime] </td>
									<td> Pul vesayti</td>
									<td>$move[debt]</td>	
									<td></td>
									<td></td>
									<td>$debt</td>
									<td>$move[note]</td>
								
							</tr>";}
						
						
					}
					else 
					{
						if ($move['number']>0) {
								
									$debt=round($debt+$move[debt],2);
									$return=Returnp::find()->where(['number'=>$move[number]])->one();
									$move[debt]=-$move[debt];
									$sum_voz=$sum_voz+$move[debt];
									$move[pos] = -$move[pos];
									$bonus_sum = $bonus_sum + $move[pos];
									$bonus_current = $bonus_current + $move[bonus]-$move[pos];
									$bonus_count = $bonus_count + $move[bonus];
									
										echo "<tr>
										<td>$move[datetime] </td>
										<td><a href='../returnp/report?number=$move[number]'> Iyadə sənədi ($move[number])  tarixdən ($return->nameProduct, say $return->quantity)</a></td>
										<td></td>	
										<td></td>
										<td>$move[debt]</td>
										<td>$debt</td>
										<td>$move[note]</td>				
									</tr>";
								
							
							
							
						}
						
						else 
						{
							
							{
								$debt=round($debt+$move[debt],2);
								$move[debt]=-$move[debt];
								$sum=round($sum+$move[debt],2);
								echo "<tr>
								<td>$move[datetime] </td>
								<td>Ödənib</td>
								<td></td>	
								<td>$move[debt]</td>
								<td></td>	
								<td>$debt</td>
								<td>$move[note]</td>				
								</tr>";
							}
						
						}
						
					}
				}
				
					echo "<tr  class='danger'>
								<td colspan=2>Итог</td>
								<td>$sum_debt</td>	
								<td>$sum</td>
								<td>$sum_voz</td>
								<td>$debt</td>
								<td></td>		
								</tr>";
		?>
        </tbody>
  </div>
