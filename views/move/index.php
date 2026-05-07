<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use kartik\grid\GridView;
use kartik\date\DatePicker;
use app\models\Client;
use app\models\Contractor;
use app\models\Product;
use kartik\select2\Select2;

use app\models\TypeProduct;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SellSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sell-index row" xmlns="http://www.w3.org/1999/html">
<div class="col-md-9">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


        <div class="btn-group" id="div1" style="width: 150px">
            <?= Select2::widget([
                'data' =>  ArrayHelper::map(TypeProduct::find()->where(['id_parent' =>0])->orderBy("name  ASC")->all(), 'id', 'name'),
                'name' => 'type1',
                'options' => [
                    'placeholder' => 'Seçin',
                    'onchange' => 'changeType1($("#type1").val())',
                    'id'=>'type1',

                ]
            ]); ?>
        </div>
        <div class="btn-group" id="div2" style="width: 150px; display:none">
            <?= Select2::widget([
                'name' => 'type2',
                'options' => [
                    'placeholder' => 'Seçin',
                    'onchange' => 'changeType2($("#type2").val())',
                    'id'=>'type2',


                ]
            ]); ?>
        </div>
        <div class="btn-group" id="div3" style="width: 150px;display:none">
            <?= Select2::widget([
                'name' => 'type3',
                'options' => [
                    'placeholder' => 'Seçin',
                    'onchange' => 'changeType3($("#type3").val())',
                    'id'=>'type3',

                ]
            ]); ?>
        </div>

        <div class="btn-group" id="div4" style="width: 150px;display:none">
            <?= Select2::widget([
                'name' => 'type4',
                'options' => [
                    'placeholder' => 'Seçin',
                    'onchange' => 'changeType4($("#type4").val())',
                    'id'=>'type4',

                ]
            ]); ?>
        </div>
        <div class="btn-group" id="div5" style="width: 150px;display:none">
            <?= Select2::widget([
                'name' => 'type5',
                'options' => [
                    'placeholder' => 'Seçin',
                    'onchange' => 'changeType4($("#type5").val())',
                    'id'=>'type5',

                ]
            ]); ?>
        </div>
        <div class="btn-group" id="div6" style="width: 350px;display:none">
            <?= Select2::widget([
                'name' => 'product',
                'options' => [
                    'placeholder' => 'Seçin',
                    'id'=>'product',
                    'multiple'=>true,
                ]
            ]); ?>
        </div>
        <div class="btn-group">
            <?= Html::button('<i class="glyphicon glyphicon-ok"></i>  OK', ['class' => 'btn btn-success','onclick' =>"document.location.replace('report?type1='+$('#type1').val()+'&type2='+$('#type2').val()+'&type3='+$('#type3').val()+'&type4='+$('#type4').val()+'&type5='+$('#type5').val()+'&product='+$('#product').val()+'&date1='+$('#date1').val()+'&date2='+$('#date2   ').val())"]); //?>

        </div>
<br><br>

            <div style="width: 200px">
                <?= DatePicker::widget([
                    'name' => 'check_issue_date',
                    'id' => 'date1',
                    'value' => date('Y-m-d'),
                    'options' => ['placeholder' => 'Select issue date ...'],
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => false
                    ]
                ]); ?>

                <?= DatePicker::widget([
                    'name' => 'check_issue_date',
                    'id' => 'date2',
                    'value' => date('Y-m-d'),
                    'options' => ['placeholder' => 'Select issue date ...'],
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => false
                    ]
                ]); ?>
            </div>


    <div id="contentAjax">

    </div>

    </br>
    <?php
    Modal::begin([
        // 'header' => '<h4>Find device</h4>',
        'options' => [
            'id' => 'sell-modal',
            'tabindex' => true,
          //  'style' => 'width:1200px'

        ],



    ]);

    echo '<div id="modalContent"></div>';

    Modal::end();

    ?>
    <?php $productList = ArrayHelper::map(Product::find()->all(), 'id', 'name'); ?>
</div>

</div>
