<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BonusSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Bonuses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bonus-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <div>
       <?php
        echo Html::radio('type[]', $model1->status,['label' => $model1->name,'onchange' => "editStatus(1)"]). " ".Html::input('text','par1',$model1->par1,[ 'size' => 3,'id' =>'par1','onChange' => "editPar('par1')" ])." %<br><br>";
        ?>
    </div>
    <div>
        <?php
        echo Html::radio('type[]',$model2->status,['label' => $model2->name,'onchange' => "editStatus(2)"])."- hər  ".Html::input('text','par2',$model2->par2,[ 'size' => 3,'id' =>'par2','onChange' => 'editPar("par2")'])." manata ".Html::input('text','par2',$model2->par3,[ 'size' => 3,'id' =>'par3','onChange' => 'editPar("par3")'])." AZN";
        ?>
    </div>

