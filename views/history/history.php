    <?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use kartik\grid\GridView;
use kartik\date\DatePicker;
use app\models\Store;
    use app\models\Arrival;
use app\models\Contractor;
use app\models\Product;
use kartik\select2\Select2;
use app\models\TypeProduct;

if (  Yii::$app->user->identity->id_role==1)  $role3=0;

else $role3=1;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ArrivalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = 'Arrivals';
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="arrival-index" style="padding:50px">
  

  Son arxivləşnə <?=$date?>
    <div class="btn-group">
        <?= Html::button('<i class="glyphicon glyphicon-download-alt"></i> Save', [ 'class' => 'btn btn-warning', 'onclick' => 'receivedHistory()']) ?>
    </div>

  

