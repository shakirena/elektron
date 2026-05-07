<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use \app\models\Product;
use yii\helpers\ArrayHelper;
use app\models\Client;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Arrivals';
//$this->params['breadcrumbs'][] = $this->title;
?>

<div id="inf" style="width:250px;" align='center' onafterprint="myFunction()">

    <p style="font-family:Times New Roman;margin-bottom:0px;font-size:14pt;"><b>ELEKTRON</b></p>
    <p style="font-size:10pt;" >Tarix: <?php echo $date; ?><br>Çekin nömrəsi: <?php echo $number; ?><br/><?php echo Client::find()->where(["id_client"=>$client])->one()->fio; ?></p>


        <?= GridView::widget([
            'dataProvider' => $dataProvider,

            //'filterModel' => $searchModel,
            'rowOptions' =>
                function ($dataProvider, $key, $index, $grid) {

                    return ['id' => $dataProvider['id']
                    ];
                },
            'headerRowOptions' => ['class' => 'info'],
			'summary' => false,
            //'showFooter' => TRUE,
            'showPageSummary' => true,
            'pageSummaryRowOptions' => ['class' => 'text-right danger'],
            //'footerRowOptions' => ['class' => 'info'],
            'tableOptions' => [

                'style' => 'font-size:8pt',
				 'class' => 'table-print',
            ],
            'columns' => [

              //  ['class' => 'kartik\grid\SerialColumn',
               //     'width' => '10px'],
                [  'attribute' =>'id_product',
                    'label' =>'Malın adı',
                    'value' =>  'idProduct.name',
                    'enableSorting' => false,
                    'pageSummary' => 'Yekun',
                    'width' => '650px'
                ],
                [
                    'attribute' => 'quantity',
                    'format' => 'raw',
                    'label' =>'Say',
                    'value' => 'quantity',
                    'enableSorting' => false,
                    'hAlign' => 'right',
                    'width' => '20px'
                ],
                [
                    'attribute' => 'price',
                    'format' => 'raw',
                    'value' => 'price',
                    'label' =>'Qiy<br>məti',
                    'enableSorting' => false,
                    'hAlign' => 'right',
                    'width' => '10px',
					'encodeLabel' => false,
                ],

                [
                    'attribute' => 'sum',
                    'value' => 'sum',
                    'enableSorting' => false,
                    'pageSummary' => true,
                    'hAlign' => 'right',
                    'width' => '10px'
                ],
			
                //'id_user',
                // 'received',


            ],
        ]); ?>
        
    </table>
<?php
/*
if ($client!=1) {
 $summary = round($itog + $debt,2);
 $sum_debt = round($summary - $money-$virtual,2);
 echo "
		<div>Əvvəlki qalıq: $debt</div>
		<div>Cəmi borc: $summary</div>
		<div>Nəğd odeniş: $money </div>
		<div>Qalıq Borc:  $sum_debt </div>
 ";

}*/
?>	
    <p align='left'  class="test" style="font-size:10pt;">Təşəkkür edirik!
	
	</p>

</div>


