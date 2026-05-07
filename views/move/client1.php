<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ClientSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::button('<i class="glyphicon glyphicon-plus"></i> Əlavə et', ['value' => Url::to(['client/create1']),'id'=>'client9', 'class' => 'btn btn-danger']) ?>
    </p>
    <?php

    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' =>
            function ($dataProvider, $key, $index, $grid) {
                return ['id' => $dataProvider['id_client'],

                    //'value' => Url::to(['arrival/add']),
                    'onClick'=>'selectClient(this.id)'
                ];
            },
        'tableOptions'=>[
            'style' => 'width:850px;cursor:pointer',
            'class' => 'table-rena table-rena2'
        ],


        'pjax' =>true,
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            //'id_client',
            'fio',
            'phone',
            'adress',
            'mobile',
            'email',
           // ['class' => 'kartik\grid\ActionColumn', 'template' => '{delete}{update}'],
        ],
    ]); ?>
</div>
<?php
$script = <<< JS

$("#client9").click(function(){

$("#client-create").modal("show")
        .find("#clientContent1")
        .load($(this).attr("value"));
});
JS;
$this->registerJs($script);
?>