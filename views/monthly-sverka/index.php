<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $snapshots app\models\MonthlySverka[] */
/* @var $baseModel app\models\MonthlySverka|null */
/* @var $now array */
/* @var $flows array|null */
/* @var $compare array|null */
/* @var $backdated array */
/* @var $manual app\models\MonthlySverka */

$this->title = 'Aylıq sverka';
$f = function ($v) { return number_format((float) $v, 2, '.', ' '); };
$sign = function ($v) use ($f) { return ($v > 0 ? '+' : '') . $f($v); };
$cls = function ($v, $tolerance = 50) { return abs($v) <= $tolerance ? 'text-success' : 'text-danger'; };
$labels = ['stok' => 'Stok (təsdiqlənmiş mədaxil)', 'portfel' => 'Portfel (müştəri borcu)', 'kassa' => 'Kassa', 'borc' => 'Borc (verəcək)'];
?>
<style>
    .ms-wrap{max-width:1100px;}
    .ms-wrap h3{margin-top:28px;}
    .ms-big{font-size:22px;font-weight:700;}
    .ms-card{border:1px solid #ddd;border-radius:6px;padding:12px 16px;margin-bottom:12px;}
    .ms-wrap .table td.num, .ms-wrap .table th.num{text-align:right;white-space:nowrap;}
    .ms-muted{color:#777;font-size:12px;}
</style>
<div class="ms-wrap">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="ms-muted">Nəticə = Stok + Portfel + Kassa − Borc. Gözlənilən = əvvəlki snapshot + mənfəət − vozvrat mənfəəti − xərclər + digər mədaxil.</p>

    <?php foreach (['success', 'error'] as $type): ?>
        <?php if (Yii::$app->session->hasFlash($type)): ?>
            <div class="alert alert-<?= $type === 'error' ? 'danger' : 'success' ?>"><?= Html::encode(Yii::$app->session->getFlash($type)) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <h3>Cari vəziyyət</h3>
    <div class="row">
        <div class="col-md-6">
            <table class="table table-condensed table-bordered">
                <tr><td><?= $labels['stok'] ?></td><td class="num"><?= $f($now['stok']) ?></td></tr>
                <tr><td><?= $labels['portfel'] ?></td><td class="num"><?= $f($now['portfel']) ?></td></tr>
                <tr><td><?= $labels['kassa'] ?></td><td class="num"><?= $f($now['kassa']) ?></td></tr>
                <tr><td><?= $labels['borc'] ?></td><td class="num">−<?= $f($now['borc']) ?></td></tr>
                <tr class="info"><td><b>Nəticə</b></td><td class="num ms-big"><?= $f($now['netice']) ?></td></tr>
            </table>
            <p class="ms-muted">
                Nəticəyə daxil deyil:
                təsdiqlənməmiş mədaxil <b><?= $f($now['stok_draft']) ?></b> (şirkət borcu yoxdur),
                müştəri artıq ödənişləri <b><?= $f($now['portfel_minus']) ?></b>.
            </p>
        </div>
        <div class="col-md-6">
            <table class="table table-condensed table-bordered">
                <tr><th>Kassa</th><th class="num">Qalıq</th></tr>
                <?php foreach ($now['kassa_list'] as $k): ?>
                    <tr><td><?= Html::encode($k['name']) ?></td><td class="num"><?= $f($k['sum']) ?></td></tr>
                <?php endforeach; ?>
            </table>
            <?= Html::beginForm(['snapshot'], 'post', ['class' => 'form-inline']) ?>
                <?= Html::textInput('note', '', ['class' => 'form-control', 'placeholder' => 'Qeyd (məs. Sentyabr 2026 sonu)', 'maxlength' => 255, 'style' => 'width:260px']) ?>
                <?= Html::submitButton('<i class="glyphicon glyphicon-camera"></i> Cari vəziyyəti yadda saxla', ['class' => 'btn btn-primary']) ?>
            <?= Html::endForm() ?>
            <p class="ms-muted">Ayın sonunda, xərcləri daxil etdikdən sonra yadda saxlayın — növbəti ay bununla müqayisə olunacaq.</p>
        </div>
    </div>

    <h3>Gəlir və xərc</h3>
    <?= Html::beginForm(['index'], 'get', ['class' => 'form-inline', 'style' => 'margin-bottom:12px']) ?>
        <?php if ($baseModel): ?><?= Html::hiddenInput('base', $baseModel->id) ?><?php endif; ?>
        <?php $fromTs = strtotime($periodFrom); $toTs = $periodTo ? strtotime($periodTo) : null; ?>
        <?= Html::input('date', 'from', date('Y-m-d', $fromTs), ['class' => 'form-control']) ?>
        <?= Html::input('time', 'from_time', date('H:i', $fromTs) === '00:00' ? '' : date('H:i', $fromTs), ['class' => 'form-control', 'title' => 'Saat (istəyə görə)']) ?>
        —
        <?php // Конец без времени = весь день: показываем предыдущий день, т.к. граница хранится как начало следующего ?>
        <?= Html::input('date', 'to', $toTs ? date('Y-m-d', date('H:i', $toTs) === '00:00' ? $toTs - 86400 : $toTs) : '', ['class' => 'form-control']) ?>
        <?= Html::input('time', 'to_time', $toTs && date('H:i', $toTs) !== '00:00' ? date('H:i', $toTs) : '', ['class' => 'form-control', 'title' => 'Saat (istəyə görə)']) ?>
        <?= Html::submitButton('Göstər', ['class' => 'btn btn-default']) ?>
        <a href="<?= Url::to(['index'] + ($baseModel ? ['base' => $baseModel->id] : [])) ?>" class="btn btn-link">sıfırla</a>
    <?= Html::endForm() ?>
    <p class="ms-muted">
        Dövr: <?= Html::encode($periodFrom) ?> — <?= $periodTo ? Html::encode($periodTo) : 'indi' ?>.
        <?= $baseModel ? 'Default: seçilmiş snapshot-dan bu günə.' : 'Default: keçən ayın 1-dən bu günə (snapshot yoxdur).' ?>
        Saat boş olarsa — başlanğıc günün əvvəlindən, son gün isə tam daxil edilir. Xərclər daxil edilmə tarixinə görə götürülür.
    </p>
    <div class="row">
        <div class="col-md-5">
            <table class="table table-condensed table-bordered">
                <tr><td>Satış dövriyyəsi</td><td class="num"><?= $f($periodFlows['revenue']) ?></td></tr>
                <tr><td><b>Satışdan mənfəət</b></td><td class="num"><b><?= $f($periodFlows['profit']) ?></b></td></tr>
                <tr><td>Vozvrat mənfəəti (<?= $f($periodFlows['returns_sum']) ?> − maya <?= $f($periodFlows['returns_cost']) ?>)</td><td class="num">−<?= $f($periodFlows['returns_margin']) ?></td></tr>
                <?php foreach ($periodFlows['expense_list'] as $e): ?>
                    <tr><td>Xərc: <?= Html::encode($e['name']) ?> (<?= (int) $e['n'] ?>)</td><td class="num">−<?= $f(-$e['sum']) ?></td></tr>
                <?php endforeach; ?>
                <tr><td><b>Xərclər cəmi</b></td><td class="num"><b>−<?= $f($periodFlows['expenses']) ?></b></td></tr>
                <?php foreach ($periodFlows['income_list'] as $e): ?>
                    <tr><td>Digər mədaxil: <?= Html::encode($e['name']) ?> (<?= (int) $e['n'] ?>)</td><td class="num">+<?= $f($e['sum']) ?></td></tr>
                <?php endforeach; ?>
                <?php $periodNet = $periodFlows['profit'] - $periodFlows['returns_margin'] - $periodFlows['expenses'] + $periodFlows['other_income']; ?>
                <tr class="info"><td><b>Xalis mənfəət</b></td><td class="num ms-big"><?= $sign($periodNet) ?></td></tr>
            </table>
        </div>
        <div class="col-md-7">
            <table class="table table-condensed table-striped">
                <tr><th>Tarix</th><th>Növ</th><th>Qeyd</th><th>Kassa</th><th class="num">Məbləğ</th></tr>
                <?php foreach (array_merge($periodFlows['expense_items'], $periodFlows['income_items']) as $it): ?>
                    <tr>
                        <td style="white-space:nowrap"><?= Html::encode(substr($it['datetime'], 0, 16)) ?></td>
                        <td><?= Html::encode($it['name']) ?></td>
                        <td><?= Html::encode($it['note']) ?></td>
                        <td><?= Html::encode($it['kassa']) ?></td>
                        <td class="num"><?= $sign($it['sum']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$periodFlows['expense_items'] && !$periodFlows['income_items']): ?>
                    <tr><td colspan="5" class="ms-muted">Bu dövrdə xərc yoxdur.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <h3>Müqayisə</h3>
    <?php if (!$snapshots): ?>
        <div class="alert alert-info">Hələ snapshot yoxdur. Cari vəziyyəti yadda saxlayın və ya əvvəlki ayın rəqəmlərini aşağıda əl ilə daxil edin.</div>
    <?php else: ?>
        <?= Html::beginForm(['index'], 'get', ['class' => 'form-inline', 'style' => 'margin-bottom:12px']) ?>
            <?= Html::dropDownList('base', $baseModel ? $baseModel->id : null, ArrayHelper::map($snapshots, 'id', 'title'), ['class' => 'form-control', 'onchange' => 'this.form.submit()']) ?>
        <?= Html::endForm() ?>
    <?php endif; ?>

    <?php if ($compare): ?>
        <div class="row">
            <div class="col-md-4"><div class="ms-card">Gözlənilən nəticə<div class="ms-big"><?= $f($compare['expected']) ?></div></div></div>
            <div class="col-md-4"><div class="ms-card">Faktiki nəticə<div class="ms-big"><?= $f($compare['actual']) ?></div></div></div>
            <div class="col-md-4"><div class="ms-card">Fərq<div class="ms-big <?= $cls($compare['diff']) ?>"><?= $sign($compare['diff']) ?></div>
                <span class="ms-muted"><?= $compare['diff'] < 0 ? 'çatışmır' : ($compare['diff'] > 0 ? 'artıqdır' : '') ?></span></div></div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h4>Mənfəət (<?= Html::encode($baseModel->datetime) ?> — indi)</h4>
                <table class="table table-condensed table-bordered">
                    <tr><td>Satışdan mənfəət</td><td class="num"><?= $f($flows['profit']) ?></td></tr>
                    <tr><td>Vozvrat mənfəəti (<?= $f($flows['returns_sum']) ?> − maya <?= $f($flows['returns_cost']) ?>)</td><td class="num">−<?= $f($flows['returns_margin']) ?></td></tr>
                    <?php foreach ($flows['expense_list'] as $e): ?>
                        <tr><td>&nbsp;&nbsp;Xərc: <?= Html::encode($e['name']) ?> (<?= (int) $e['n'] ?>)</td><td class="num">−<?= $f(-$e['sum']) ?></td></tr>
                    <?php endforeach; ?>
                    <?php foreach ($flows['income_list'] as $e): ?>
                        <tr><td>&nbsp;&nbsp;Digər mədaxil: <?= Html::encode($e['name']) ?> (<?= (int) $e['n'] ?>)</td><td class="num">+<?= $f($e['sum']) ?></td></tr>
                    <?php endforeach; ?>
                    <tr class="info"><td><b>Xalis</b></td><td class="num"><b><?= $sign($compare['net_profit']) ?></b></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h4>Komponentlər</h4>
                <table class="table table-condensed table-bordered">
                    <tr><th></th><th class="num">Snapshot</th><th class="num">İndi</th><th class="num">Dəyişmə</th></tr>
                    <?php foreach ($compare['components'] as $key => $c): ?>
                        <tr>
                            <td><?= $labels[$key] ?></td>
                            <td class="num"><?= $f($c['base']) ?></td>
                            <td class="num"><?= $f($c['now']) ?></td>
                            <td class="num"><?= $sign($key === 'borc' ? -$c['delta'] : $c['delta']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="info"><td><b>Nəticə</b></td><td class="num"><?= $f($baseModel->netice) ?></td><td class="num"><?= $f($now['netice']) ?></td><td class="num"><b><?= $sign($now['netice'] - $baseModel->netice) ?></b></td></tr>
                </table>
                <p class="ms-muted">Borc üçün dəyişmə nəticəyə təsiri ilə göstərilir (borc azalanda +).</p>
            </div>
        </div>

        <h4>Stok yoxlaması</h4>
        <table class="table table-condensed table-bordered" style="max-width:640px">
            <tr><td>+ Təsdiqlənmiş mədaxil</td><td class="num"><?= $f($flows['arrivals']) ?></td></tr>
            <tr><td>− Satılan malın mayası</td><td class="num">−<?= $f($flows['cogs']) ?></td></tr>
            <tr><td>− Şirkətə vozvrat</td><td class="num">−<?= $f($flows['supplier_returns']) ?></td></tr>
            <tr><td>+ Müştəri vozvratı (maya)</td><td class="num">+<?= $f($flows['returns_cost']) ?></td></tr>
            <tr class="info"><td>Gözlənilən dəyişmə</td><td class="num"><?= $sign($compare['stock_expected_delta']) ?></td></tr>
            <tr><td>Faktiki dəyişmə</td><td class="num"><?= $sign($compare['stock_actual_delta']) ?></td></tr>
            <tr><td><b>Fərq</b></td><td class="num <?= $cls($compare['stock_diff']) ?>"><b><?= $sign($compare['stock_diff']) ?></b></td></tr>
        </table>
        <p class="ms-muted">Stokda fərq varsa — malın qiyməti/qalığı pul hərəkəti olmadan dəyişib. Stokda fərq yoxdursa, səbəbi kassa, müştəri və ya şirkət borclarındadır.</p>

        <?php if ($backdated): ?>
            <div class="alert alert-warning">
                <b>Snapshot-dan sonra keçmiş tarixlə daxil edilənlər:</b>
                <ul>
                    <?php foreach ($backdated as $b): ?>
                        <li><?= Html::encode($b['label']) ?>: <?= $b['count'] ?> yazı, <?= $f($b['sum']) ?></li>
                    <?php endforeach; ?>
                </ul>
                Bu yazılar snapshot-da yox idi, amma tarixi snapshot-dan əvvəldir — ona görə mənfəətdə görünmür, qalıqları isə dəyişir.
            </div>
        <?php elseif ($baseModel && $baseModel->is_manual): ?>
            <p class="ms-muted">Əl ilə daxil edilmiş snapshot üçün keçmiş tarixli yazıları yoxlamaq mümkün deyil.</p>
        <?php endif; ?>
    <?php endif; ?>

    <h3>Əl ilə snapshot</h3>
    <p class="ms-muted">Əvvəlki ayın rəqəmləri proqramda saxlanmayıbsa, burada daxil edin. Stok — yalnız təsdiqlənmiş mədaxil.</p>
    <?= Html::beginForm(['manual'], 'post', ['class' => 'form-inline']) ?>
        <input type="datetime-local" name="MonthlySverka[datetime]" class="form-control" required>
        <?php foreach (['stok' => 'Stok', 'portfel' => 'Portfel', 'kassa' => 'Kassa', 'borc' => 'Borc'] as $attr => $ph): ?>
            <?= Html::input('number', "MonthlySverka[$attr]", '', ['class' => 'form-control', 'placeholder' => $ph, 'step' => '0.01', 'required' => true, 'style' => 'width:120px']) ?>
        <?php endforeach; ?>
        <?= Html::textInput('MonthlySverka[note]', '', ['class' => 'form-control', 'placeholder' => 'Qeyd', 'maxlength' => 255]) ?>
        <?= Html::submitButton('Əlavə et', ['class' => 'btn btn-default']) ?>
    <?= Html::endForm() ?>

    <?php if ($snapshots): ?>
        <h3>Snapshot-lar</h3>
        <table class="table table-condensed table-striped">
            <tr><th>Tarix</th><th class="num">Stok</th><th class="num">Portfel</th><th class="num">Kassa</th><th class="num">Borc</th><th class="num">Nəticə</th><th>Qeyd</th><th></th></tr>
            <?php foreach ($snapshots as $s): ?>
                <tr>
                    <td><?= Html::encode($s->datetime) ?><?= $s->is_manual ? ' <span class="label label-default">əl ilə</span>' : '' ?></td>
                    <td class="num"><?= $f($s->stok) ?></td>
                    <td class="num"><?= $f($s->portfel) ?></td>
                    <td class="num"><?= $f($s->kassa) ?></td>
                    <td class="num"><?= $f($s->borc) ?></td>
                    <td class="num"><b><?= $f($s->netice) ?></b></td>
                    <td><?= Html::encode($s->note) ?></td>
                    <td>
                        <a href="<?= Url::to(['index', 'base' => $s->id]) ?>">müqayisə</a>
                        <?= Html::beginForm(['delete', 'id' => $s->id], 'post', ['style' => 'display:inline']) ?>
                            <?= Html::submitButton('sil', ['class' => 'btn btn-link btn-xs text-danger', 'data-confirm' => 'Snapshot silinsin?']) ?>
                        <?= Html::endForm() ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
