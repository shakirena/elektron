<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\Roles;
use app\models\Store;
/* @var $this yii\web\View */
/* @var $searchModel app\models\UsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="users-index">


    <p>
        <?= Html::button('<i class="glyphicon glyphicon-plus"></i>Əlavə et', ['value' => Url::to(['create']), 'class' => 'btn btn-danger', 'id' => 'users']) ?>
    </p>
    <?php $roleList = ArrayHelper::map(Roles::find()->all(), 'id_role', 'role'); ?>
    <?php $store = ArrayHelper::map(Store::find()->all(), 'id', 'name'); ?>
    <?php
    Modal::begin([
        'header' => '<h2>Yeni istifadəçi adının açılması</h2>',

        'id' => 'users-create',
        'size' => 'modal-sm',

    ]);

    echo '<div id="modalContent"></div>';

    Modal::end();
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions'=>[
            'style'=>'width:800px',
            'class' => 'table-rena table-rena2 ',

        ],
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

           // 'id_user',
            'fio',
            'telephone',

            'login',
            //'password',

            [
                'attribute' => 'id_role',
                'filter' => $roleList,
                'value' => 'idRole.role',
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true]
                ],
                'filterType' => GridView::FILTER_SELECT2,
                'width' => '200px',
                'filterInputOptions' => ['placeholder' => 'Any type']
            ],
            [
                'attribute' => 'id_store',
                'filter' => $store,
                'value' => 'idStore.name',
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true]
                ],
                'filterType' => GridView::FILTER_SELECT2,
                'width' => '200px',
                'filterInputOptions' => ['placeholder' => 'Any type']
            ],
            // 'salary',

            ['class' => 'kartik\grid\ActionColumn'],
        ],
    ]); ?>
</div>
