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

    <p style="font-family:Times New Roman;margin-bottom:0px;font-size:10pt;"><b>Aydın İnşaat</b></p>
    <p style="font-size:8pt;" >Tarix: <?php echo date("Y-m-d H:i:s"); ?></p>


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
                 [
                'attribute' =>'name_product',
                'label' =>'Malın adı',
               
                'value' => 'getType',

                'width' => '680px',
                'pageSummary' => 'Yekun',
                'pageSummaryOptions' => ['class' => 'text-right text-danger']
				],
                
                [
                'attribute' => 'quantity',
                'label' =>'Miqdar',
                'format'=>'raw',
                'value' => 'quantity',
                'width' =>'100px',
                'pageSummary' => true,
                'hAlign' => 'right',
                
            ],
            [
                'attribute' => 'whence',
                'label' =>'Hardan',
                'format'=>'raw',
                'value' => 'whence0.name',
                'width' =>'100px',
                'filter' => $storeList,

            ],
            [
                'attribute' => 'whered',
                'label' =>'Hara',
                'format'=>'raw',
                'value' => 'whered0.name',
                'width' =>'100px',
                'filter' => $storeList,

            ],



            ],
        ]); ?>
        
    </table>
</div>


<?php
$script = <<< JS
 window.print();

//$("#chek").modal("hide");  
			   

JS;
$this->registerJs($script);
?>