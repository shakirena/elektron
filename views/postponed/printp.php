<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use \app\models\Product;
use yii\helpers\ArrayHelper;

use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Arrivals';
//$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="arrival-index"  style="margin-top:-15px">
        <table >
            <tr>
                <td style="font-size:9pt;width: 350px;padding-left:50px">

                    Satıcı Şirkət: MERINOS MEBEL
                    <br>
                    Ünvan: Bakı şəhəri, Babək prospekti 64
                    <br>
                    Tel: <span>012 570 24 78; 070 217 20 70</span>    </td>
                <td><?= Html::img("img/Merinos.jpg")?></td>

                <td  style="font-size:9pt;width: 350px;padding-left:40px"> www.merinos.az; www.merinos.com.tr  <br>www.facebook.com/merinosbaku www.instagram.com/merinos_azerbaycan<br>e-mail:info@merinos.az
                </td>



            </tr>
        </table>
    <br>
    <table style="width: 100%;text-align: center;font-size:8pt" class="table table-bordered">
        <thead>
        <tr  class="info" >
            <td >Təhvil vermə tarixi</td>
            <td >Təhvil təslim aktı</td>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td > <?php echo date("Y-m-d H:i:s"); ?></td>
            <td >№ <?= $number ?></td>
        </tr>
        </tbody>

    </table>
    <table style="width: 100%;font-size: pt">
        <tr ><td colspan="2" style="border-bottom: 1px solid"><b>Təhvil verən: </b> <?=Yii::$app->user->identity->fio ?></td></tr>
        <tr><td colspan="2" style="border-bottom: 1px solid"><b>Muştəri:<?= $client->fio?> </b> </td></tr>
        <tr><td colspan="2" style="border-bottom: 1px solid"><b>Ünvan: </b><?= $client->adress?></td></tr>

        <tr><td style="border-bottom: 1px solid"><b>Tel:</b> <?= $client->phone?></td>
            <td style="border-bottom: 1px solid"><b>Mob: </b><?= $client->mobile?></td></tr>
        <tr><td  colspan="2" style="border-bottom: 1px solid"><b>e-mail: <?= $client->email?></b></td></tr>

    </table>


<br>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,


        // 'pjax'=>true,
        'striped'=>true,
        'hover'=>true,
        'tableOptions' => [

            'class' => 'table-rena table-rena2',
            'style' => 'font-size:9pt'

        ],

        'showPageSummary' => true,
        'rowOptions' =>
            function ($dataProvider, $key, $index, $grid) {
                return ['id' => $dataProvider['id']
                ];
            },

        'columns' => [
            ['class'=>'kartik\grid\SerialColumn'],


            [
                'attribute' => 'date',
                'format'=>'raw',
                'label' => 'Satış tarixi',
                'value' => 'date','width' => '250px',



                // 'group'=>true,

            ],
            [
                'attribute' =>'name_product',
                'label' =>'Malın adı',
                //'filter' => $productList,m
                'value' => 'getType',
                'pageSummary' => 'Yekun',
                'pageSummaryOptions' => ['class' => 'text-right text-danger'],
                'width' => '650px'

            ],

            [
                'attribute' => 'quantity',
                'label' =>'Miqdar',
                'format'=>'raw',
                'value' => 'quantity',
                'width' =>'20px',
                'pageSummary' => true,
                'hAlign' => 'right',

            ],
          /*  [
                'attribute' =>  'price',
                'label' => 'Satış <br> qiyməti',
                'format'=>'raw',
                'value' => 'price',
                'width' =>'20px',
                'encodeLabel' => false,
                'pageSummary' => true,
                'hAlign' => 'right',
            ],
            [
                'attribute' => 'sum',
                'value' =>'sum',
                'width' =>'20px',
                'hAlign' => 'right',
                'pageSummary' => true,

            ],*/

            /*    [   'attribute' => 'id_client',
                   'label' => 'Müştəri',
                   'value' => 'idClient.fio',
                   'filter' => $clientList,
                   'filterWidgetOptions' =>[
                       'pluginOptions'=>['allowClear'=>true]
                   ],
                   'filterType' =>GridView::FILTER_SELECT2,
                   'width' => '400px',
                   'filterInputOptions' =>['placeholder'=>'Any type']
               ],
                         [   'attribute' => 'id_user',
                             'label' => 'Satıcı',
                             'value' => 'idUser.fio',
                             'filter' => $user,
                             'filterWidgetOptions' =>[
                                 'pluginOptions'=>['allowClear'=>true]
                             ],
                             'filterType' =>GridView::FILTER_SELECT2,
                             'width' => '400px',
                             'filterInputOptions' =>['placeholder'=>'Hər hansi']
                         ],
                         /* [   'attribute' => 'type',
                              'value' => 'idType'
                          ],*

              [   'attribute' => 'id_store',
                   'label' => 'Filial',
                   'value' => 'idStore.name',
                   'filter' => $storeList,
                   'filterWidgetOptions' =>[
                       'pluginOptions'=>['allowClear'=>true]
                   ],
                   'filterType' =>GridView::FILTER_SELECT2,
                   'width' => '400px',
                   'filterInputOptions' =>['placeholder'=>'Hər hansi']
               ]
               ,*/
            // 'received',

        ],
    ]); ?>
        <table class="table table-bordered" style="width: 250px;font-size:8pt">
            <thead>
            <tr  class="info" >
                <td colspan="2">Ödəniləcək məbləğ: <?= $sum ?></td>

            </tr>
            <tr  class="info" >
                <td >Beh: <?=$sum- $money ?></td>
                <td >Qalıq məbləğ: <?= $money ?></td>

            </tr>
            </thead>


        </table>

    <table style="width: 100%;margin-top:80px">
        <tr>
            <td>
                Təhvil verdim:__________________________
            </td>
            <td>
                Təhvil aldım:__________________________
            </td>

        </tr>
    </table>




</div>
<?php
$script = <<< JS
 $(document).ready(function(){
 var PDF = document.getElementById("plugin");

   PDF.focus();
      PDF.contentWindow.print();
});


JS;
$this->registerJs($script);
?>