<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PasswordSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Passwords';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="password-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

   
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
		'tableOptions'=>[
            'style'=>'width:1000px;',
            'class' => 'table-rena table-rena2 ',

        ]
        ,
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

          
            'name',
            'password',

            ['class' => 'kartik\grid\ActionColumn'],
        ],
    ]); ?>
</div>
