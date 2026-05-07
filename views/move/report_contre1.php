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
		$sum=round($current->sum,2);$sum_usd=round($current->sum_usd,2);
							echo "<tr>
								<td>Текущий долг</td>
								<td></td>	
								<td></td>
								<td></td>
								<td>$sum</td>
														
							</tr>";
				foreach($model as $move) {
					if ($move['debt']>0 || $move['sum_usd']>0) {
						if ($move['sum_usd']>0)
						{
							$sum_usd=round($sum_usd+$move[sum_usd],2);
							echo "<tr>
								<td><a href='../arrival/report1?number=$move[number]'> Prixod sənədi ($move[number]) $move[datatime] tarixdən</a></td>
								<td></td>	
								<td></td>
								<td></td>
								<td></td>
													
							</tr>";
						}
						else 
						{
							$sum=round($sum+$move[debt],2);
						 echo "<tr>
								<td><a href='../arrival/report1?number=$move[number]'> Prixod sənədi ($move[number]) $move[datatime] tarixdən</a></td>
								<td>$move[debt]</td>	
								<td></td>
								<td></td>
								<td>$sum</td>
															
						 </tr>";
						}
					}
					else 
					{
						if ($move['number']>0) {
							$sum=round($sum+$move[debt],2);
							$return=ReturnArrival::find()->where(['id'=>$move[number]])->one();
							$move[debt]=-$move[debt];
								echo "<tr>
								<td><a href='../return-arrival/report?id=$move[number]'> Vozvrat sənədi ($move[number]) $move[datatime] tarixdən ($return->nameProduct, say $return->quantity)</a></td>
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
								$move[sum_usd]=-$move[sum_usd];
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
