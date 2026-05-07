<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
/* @var $this yii\web\View */
/* @var $model app\models\TypeProduct */

$this->title = $model->name;
//$this->params['breadcrumbs'][] = ['label' => 'Type Products', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="type-product-view updateType">

    <center><h1><?= Html::encode($this->title) ?></h1></center>

	
	<table  class = 'table-print'>
		<thead>
			<tr>
				<td>Malın adı<td>
				<td>Barkodu<td>
			</tr>
		</thead>
		<tbody>
		<?php
			foreach ($products as $product)
			{
				echo "<tr><td>$product[name]</td>";
				echo "<td>$product[nameBarcode]</td></tr>";
				
			}
		
		?>
		</tbody>
	</table>
	
</div>
<?php
$script = <<< JS
window.print();
JS;
$this->registerJs($script);
?>