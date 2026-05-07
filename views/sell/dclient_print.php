<?php
use yii\bootstrap\Modal;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use app\models\Client;
use kartik\date\DatePicker;
use yii\widgets\Pjax;
use yii\helpers\Url;
use kartik\select2\Select2;
use app\models\Kassa;
/* @var $this yii\web\View */
/* @var $searchModel app\models\DclientSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = 'Dclients';
//$this->params['breadcrumbs'][] = $this->title;
?>


    <?php
    Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'info',
            'tabindex' => true,
				
        ],

        'size' => '300px',

    ]);

    echo '<div id="modalContent"></div>';

    Modal::end();
    ?>
	<?php
    Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'modal',
            'tabindex' => true,
			'class'=>'rena_dialog'
        ],

        'size' => '300px',

    ]);

    echo '<div id="modalDclient"></div>';

    Modal::end();
    ?>
<div class="dclient-index noprint">

    <?php $clientList = ArrayHelper::map(Client::find()->all(), 'id_client', 'fio'); ?>
  <?= Html::button('<i class="glyphicon glyphicon-plus"></i> Əlavə et',['value' => Url::to(['sell/dclient-add']),'class' => 'btn btn-danger','id' => 'addDclient']); ?>
      
    <br> <br>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [

            'class' => 'table-rena table-rena3',
            'style' => 'font-size:9pt'

        ],
		'showFooter' => true,
        'footerRowOptions' => ['style' => 'font-weight:bold;text-decoration:underline;color:red;'],
		'panel'=>['type'=>'primary'],
        'striped'=>true,
        'hover'=>true,
        'columns' => [


            [

                'attribute'  => 'id_client',
                'value' =>'idClient.fio',
                'filter' => $clientList,
                'filterWidgetOptions' =>[
                    'pluginOptions'=>['allowClear'=>true]
                ],
                'filterType' =>GridView::FILTER_SELECT2,
                'width' => '300px',
                'filterInputOptions' =>['placeholder'=>'Any type']
            ] ,
			[
				'label'=> 'Telefon',
				'value'=> 'getPhone'
			],
			[
                'label'=>'Qalıq <br> Borc',
                'encodeLabel' => false,
                'value' => function($model){
								return round($model['debt_sum'],2);
				},
				 'footer' =>  round($searchModel->getSumDebt($dataProvider->query),2),

            ],

      
            [
                'label' => 'Info',
                'format' => 'raw',
                'value' => function ($model, $index, $widget) {

                    return Html::button('<i class="glyphicon glyphicon-info"></i>  Tarixçə', ['class' => 'btn btn-info',  'onClick' => "infoClient($model->id_client)"]);
                }
            ],
		
        ],
    ]); ?>


</div>
<style>
    @media print {
        .noprint, .modal-header {
            content: " ";
            display: none !important;;visibility: hidden !important;
        }
        .modal-content
        {
            border: none !important;
        }
    }
</style>