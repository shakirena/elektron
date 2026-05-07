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
use app\models\Product;
use app\models\Sell;
use app\models\Arrival;
use app\models\Transfer;
use app\models\ReturnArrival;
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
		<th>Vozvrat</th>
        <th>Yekun qalıq</th>
		
        </thead>
        <tbody>
		<?php 
		$sum=$current->sum;$sum_usd=$current->sum_usd;
							echo "<tr>
								<td>Текущий долг</td>
								<td></td>	
								<td></td>
								<td></td>
								<td>$sum</td>
													
							</tr>";
				foreach($model as $move) {
					if ($move['debt']>0 || $move['sum_usd']>0) {
						foreach (Arrival::find()->where(["number"=>$move[number]])->all() as $arrival)
						{
							if ($move['sum_usd']>0)
							{
								$sum_usd=$sum_usd+$move[sum_usd];
								echo "<tr>
									<td> $arrival->nameProduct( $move[datatime] tarixdən)</td>
									<td></td>	
									<td></td>
									<td></td>
									<td></td>
																
								</tr>";
							}
							else 
							{
								$sum=$sum+$move[debt];
							 echo "<tr>
									<td>$arrival->nameProduct( $move[datatime] tarixdən)</td>
									<td>$move[debt]</td>	
									<td></td>
									<td></td>
									<td>$sum</td>
																
							 </tr>";
							}
						}
					}
				else 
					{
						if ($move['number']>0) {
							$sum=round($sum+$move[debt],2);
							$return=ReturnArrival::find()->where(['id'=>$move[number]])->one();
							$move[debt]=-$move[debt];
								echo "<tr>
								<td><a href='../return-arrival/report?number=$move[number]'> Vozvrat sənədi ($move[number]) $move[datatime] tarixdən ($return->nameProduct, say $return->quantity)</a></td>
								<td></td>	
								<td></td>
								<td>$move[debt]</td>
								<td>$sum</td>
															
							</tr>";
							
							
						}
						
						else 
						{
							if ($move['sum_usd']<0)
							{
								$sum_usd=round($sum_usd+$move[sum_usd],2);
								$move[debt]=-$move[debt];
								echo "<tr>
								<td> Ödənib $move[datatime] tarixdən</td>
								<td></td>	
								<td></td>
								<td></td>
								<td></td>
															
								</tr>";
								
							
							}	
						else 
							{
								$sum=round($sum+$move[debt],2);
								$move[debt]=-$move[debt];
								echo "<tr>
								<td>Ödənib $move[datatime] tarixdən</td>
								<td></td>	
								<td>$move[debt]</td>
								<td></td>
								<td>$sum</td>
															
								</tr>";
							}
						
						}
						
					}
				}
				
					echo "<tr  class='danger'>
								<td>Итог</td>
								<td></td>	
								<td></td>
								<td></td>
								<td>$sum</td>
																
								</tr>";
		?>
        </tbody>
  </div>
