<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use kartik\grid\GridView;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use app\models\Product;
use app\models\Store;
use app\models\Client;
use app\models\Contractor;
use app\models\ProductMovementSearch;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductMovementSearch */
/* @var $dataProvider yii\data\ArrayDataProvider */

$this->title = 'Mal hərəkəti';

?>
<div class="product-movement-report">

    <?php
    $productList    = ArrayHelper::map(Product::find()->orderBy('name')->all(), 'id', 'name');
    $storeList      = ArrayHelper::map(Store::find()->all(), 'id', 'name');
    $contractorMap  = ArrayHelper::map(Contractor::find()->andWhere('id >= 1')->all(), 'id', 'name');
    $clientMap      = ArrayHelper::map(Client::find()->all(), 'id_client', 'fio');
    $operationList  = ProductMovementSearch::operationLabels();
    ?>

    <?php $form = \yii\widgets\ActiveForm::begin([
        'method'  => 'get',
        'action'  => Url::to(['/product-movement/report']),
        'options' => [
            'class' => 'well well-sm',
            'style' => 'margin-bottom:10px; padding:10px;',
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-5">
            <?= $form->field($searchModel, 'id_product')->widget(Select2::class, [
                'data'          => $productList,
                'options'       => ['placeholder' => 'Malı seçin... (məcburi)'],
                'pluginOptions' => ['allowClear' => true],
            ])->label('Mal <span class="text-danger">*</span>', ['encode' => false]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($searchModel, 'date_from')->widget(DatePicker::class, [
                'type'          => DatePicker::TYPE_COMPONENT_APPEND,
                'pluginOptions' => ['format' => 'yyyy-mm-dd', 'autoClose' => true],
            ])->label('Tarixdən') ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($searchModel, 'date_to')->widget(DatePicker::class, [
                'type'          => DatePicker::TYPE_COMPONENT_APPEND,
                'pluginOptions' => ['format' => 'yyyy-mm-dd', 'autoClose' => true],
            ])->label('Tarixə qədər') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($searchModel, 'id_store')->widget(Select2::class, [
                'data'          => $storeList,
                'options'       => ['placeholder' => 'Bütün filiallar'],
                'pluginOptions' => ['allowClear' => true],
            ])->label('Filial') ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($searchModel, 'operation_type')->widget(Select2::class, [
                'data'          => $operationList,
                'options'       => ['placeholder' => 'Bütün əməliyyat növləri'],
                'pluginOptions' => ['allowClear' => true],
            ])->label('Əməliyyat növü') ?>
        </div>
        <div class="col-md-2" style="padding-top:25px">
            <?= Html::submitButton(
                '<i class="glyphicon glyphicon-search"></i> Axtar',
                ['class' => 'btn btn-primary']
            ) ?>
        </div>
    </div>

    <?php \yii\widgets\ActiveForm::end(); ?>

    <?php if (!$searchModel->id_product): ?>
        <div class="alert alert-info">
            <i class="glyphicon glyphicon-info-sign"></i>
            Hesabatı görmək üçün mal seçin.
        </div>
    <?php else: ?>

        <?php
        $selectedProductName = isset($productList[$searchModel->id_product])
            ? Html::encode($productList[$searchModel->id_product])
            : 'ID ' . (int) $searchModel->id_product;
        ?>

        <?= GridView::widget([
            'dataProvider'    => $dataProvider,
            'tableOptions'    => [
                'class' => 'table-rena table-rena2',
                'style' => 'font-size:9pt',
            ],
            'striped'         => true,
            'hover'           => true,
            'showFooter'      => true,
            'footerRowOptions' => ['style' => 'font-weight:bold;text-decoration:underline;color:red;'],
            'panel'           => [
                'type'    => 'primary',
                'heading' => 'Mal hərəkəti: ' . $selectedProductName,
            ],
            'columns' => [
                ['class' => 'kartik\grid\SerialColumn'],

                [
                    'label' => 'Tarix / Saat',
                    'value' => function ($row) {
                        return Html::encode($row['event_datetime'] ?? '');
                    },
                    'format' => 'raw',
                    'width'  => '130px',
                ],

                [
                    'label' => 'Əməliyyat',
                    'value' => function ($row) use ($operationList) {
                        $type  = $row['operation_type'] ?? '';
                        $label = $operationList[$type] ?? $type;
                        $class = [
                            'arrival'          => 'label-success',
                            'sell'             => 'label-danger',
                            'sell2'            => 'label-danger',
                            'return_client'    => 'label-warning',
                            'return_supplier'  => 'label-warning',
                            'sverka'           => 'label-info',
                        ][$type] ?? 'label-default';
                        return '<span class="label ' . $class . '">' . Html::encode($label) . '</span>';
                    },
                    'format' => 'raw',
                    'width'  => '130px',
                ],

                [
                    'label'  => 'Miqdar',
                    'value'  => function ($row) {
                        $qty = (float) ($row['quantity'] ?? 0);
                        return number_format($qty, 2, '.', '');
                    },
                    'format'      => 'raw',
                    'hAlign'      => 'right',
                    'width'       => '80px',
                    'pageSummary' => true,
                    'footer'      => array_sum(array_column($dataProvider->allModels, 'quantity')),
                    'footerOptions' => ['style' => 'text-align:right'],
                ],

                [
                    'label' => 'Qiymət',
                    'value' => function ($row) {
                        $price = $row['price'];
                        if ($price === null || $price === '') {
                            return '—';
                        }
                        return number_format((float) $price, 2, '.', '');
                    },
                    'format' => 'raw',
                    'hAlign' => 'right',
                    'width'  => '80px',
                ],

                [
                    'label' => 'Filial',
                    'value' => function ($row) use ($storeList) {
                        $id = $row['id_store'] ?? null;
                        return Html::encode($storeList[$id] ?? ($id ? 'ID ' . $id : '—'));
                    },
                    'format' => 'raw',
                    'width'  => '120px',
                ],

                [
                    'label' => 'Kontragent / Müştəri',
                    'value' => function ($row) use ($contractorMap, $clientMap) {
                        if (!empty($row['counterparty_id'])) {
                            $name = $contractorMap[$row['counterparty_id']] ?? ('Kontr. #' . $row['counterparty_id']);
                            return Html::encode($name);
                        }
                        if (!empty($row['client_id'])) {
                            $name = $clientMap[$row['client_id']] ?? ('Müştəri #' . $row['client_id']);
                            return Html::encode($name);
                        }
                        return '—';
                    },
                    'format' => 'raw',
                    'width'  => '180px',
                ],

                [
                    'label' => 'Sənəd №',
                    'value' => function ($row) {
                        $doc = $row['document_number'] ?? null;
                        return $doc !== null ? Html::encode($doc) : '—';
                    },
                    'format' => 'raw',
                    'width'  => '70px',
                ],
            ],
        ]); ?>

    <?php endif; ?>

</div>
