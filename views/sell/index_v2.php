<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use kartik\grid\GridView;
use kartik\date\DatePicker;
use app\models\Client;
use app\models\Store;
use app\models\Contractor;
use app\models\Product;
use app\models\Arrival;
use app\models\Users;
use kartik\select2\Select2;
use app\models\TypeProduct;
use app\models\Kassa;
use app\components\PushAll;
use app\models\DisplaySettingsForm;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SellSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

if (Yii::$app->user->identity->id_role == 1) $role = 0;
else $role = 1;

$displaySettings = DisplaySettingsForm::current();
$quickAccessProducts = Product::find()->where(['quick_access' => 1])->orderBy('name')->all();

if ($model->id_client) {
    Yii::$app->session->set('id_client', $model->id_client);
    Yii::$app->session->set('client', Client::find()->where(['id_client' => $model->id_client])->one()->fio);
}
if (!$user) $user = Yii::$app->user->identity->id_user;
if (!$model->id_store) $store = 1; else $store = $model->id_store;

$clientName = Yii::$app->session->get('client');
$clientInitials = 'M';
if ($clientName) {
    $parts = preg_split('/\s+/', trim($clientName));
    $clientInitials = mb_strtoupper(mb_substr($parts[0], 0, 1) . (isset($parts[1]) ? mb_substr($parts[1], 0, 1) : ''));
}

$quickTilePrice = function ($productId) {
    $arrival = Arrival::find()
        ->select('pricesell')
        ->where(['id_product' => $productId, 'received' => 1])
        ->orderBy(['datetime' => SORT_DESC])
        ->one();
    return $arrival ? (float) $arrival->pricesell : 0;
};
?>
<div class="sell-v2">
<style>
    .sell-v2{
        --sv-paper:#FFFFFF;
        --sv-surface:#FFFFE4;
        --sv-line:#EDE0A0;
        --sv-ink:#26230F;
        --sv-ink-2:#726B48;
        --sv-ink-3:#A39C6E;
        --sv-accent:#1FA854;
        --sv-accent-strong:#178F46;
        --sv-accent-soft:#DFF6E7;
        --sv-accent-ink:#FFFFFF;
        --sv-info:#2F9BE0;
        --sv-info-strong:#2380BE;
        --sv-info-soft:#DEF1FC;
        --sv-warn:#F0932B;
        --sv-warn-strong:#D97D18;
        --sv-warn-soft:#FDEAD3;
        --sv-danger:#E5484D;
        --sv-danger-strong:#C93338;
        --sv-danger-soft:#FBE0E1;
        --sv-radius-sm:8px;
        --sv-radius-md:12px;
        --sv-radius-lg:18px;
        --sv-shadow-sm:0 1px 2px rgba(33,31,27,.06);
        --sv-shadow-md:0 10px 26px rgba(31,168,84,.24);
        --sv-font-num:ui-monospace,"SF Mono","Cascadia Mono","Segoe UI Mono",Consolas,"Roboto Mono",monospace;
        background:var(--sv-surface);
        color:var(--sv-ink);
        font-size:15px;
        border-radius:var(--sv-radius-lg);
        box-shadow:0 1px 3px rgba(38,35,15,.08);
        overflow:hidden;
        margin-bottom:20px;
        padding-bottom:104px;
    }
    @media (prefers-color-scheme: dark){
        .sell-v2{
            --sv-paper:#19170D; --sv-surface:#221F13; --sv-line:#3E3A22;
            --sv-ink:#F3F0DE; --sv-ink-2:#ABA483; --sv-ink-3:#837C5C;
            --sv-accent:#33C46F; --sv-accent-strong:#4CD583; --sv-accent-soft:rgba(51,196,111,.18); --sv-accent-ink:#0C1B10;
            --sv-info:#4DB4EE; --sv-info-strong:#6BC2F2; --sv-info-soft:rgba(77,180,238,.18);
            --sv-warn:#F5A94E; --sv-warn-strong:#F7BC71; --sv-warn-soft:rgba(245,169,78,.18);
            --sv-danger:#F16368; --sv-danger-strong:#F58589; --sv-danger-soft:rgba(241,99,104,.18);
            --sv-shadow-sm:0 1px 2px rgba(0,0,0,.3); --sv-shadow-md:0 12px 28px rgba(51,196,111,.28);
        }
    }
    .sell-v2 *{box-sizing:border-box;}
    .sell-v2 .tabular{font-family:var(--sv-font-num); font-variant-numeric:tabular-nums;}
    /* safety net: nothing on this page should ever push the page sideways —
       only affects this view, since the rule lives in its own inline style. */
    html, body{overflow-x:hidden;}

    /* ---------- Header ---------- */
    .sell-v2 .sv-header{background:var(--sv-surface); border-bottom:1px solid var(--sv-line); padding:16px 20px 10px;}
    .sell-v2 .sv-search-row{display:flex; align-items:center; gap:12px; flex-wrap:wrap;}
    .sell-v2 .sv-search-field{
        flex:1 1 320px; display:flex; align-items:center; gap:10px;
        height:56px; padding:0 18px; border-radius:var(--sv-radius-md);
        background:var(--sv-paper); border:1.5px solid var(--sv-line);
        box-shadow:inset 0 1px 2px rgba(38,35,15,.05);
        transition:border-color .15s ease;
    }
    .sell-v2 .sv-search-field:focus-within{border-color:var(--sv-accent);}
    .sell-v2 .sv-search-field svg{flex:0 0 auto; color:var(--sv-ink-3);}
    .sell-v2 .sv-search-field input{
        flex:1 1 auto; border:0; background:transparent; outline:none;
        font-size:18px; color:var(--sv-ink); height:100%;
    }
    .sell-v2 .sv-search-field input::placeholder{color:var(--sv-ink-3);}
    .sell-v2 .sv-kbd{
        font-family:var(--sv-font-num); font-size:11.5px; color:var(--sv-ink-2);
        background:var(--sv-surface); border:1px solid var(--sv-line); border-radius:6px; padding:2px 6px; line-height:1.4;
    }
    .sell-v2 .sv-search-field .sv-kbd{background:var(--sv-surface);}
    .sell-v2 .sv-icon-btn-solo{
        flex:0 0 auto; height:56px; width:56px; border-radius:var(--sv-radius-md);
        background:var(--sv-danger-soft); color:var(--sv-danger); border:0; cursor:pointer;
        display:flex; align-items:center; justify-content:center; transition:background .15s ease,color .15s ease;
    }
    .sell-v2 .sv-icon-btn-solo:hover{background:var(--sv-danger); color:#fff;}
    .sell-v2 .sv-client-chip{
        flex:0 0 auto; display:flex; align-items:center; gap:10px;
        height:56px; padding:0 14px 0 8px; border-radius:var(--sv-radius-md);
        background:var(--sv-warn-soft); border:1px solid transparent; max-width:220px;
        text-decoration:none; transition:background .15s ease,border-color .15s ease;
    }
    .sell-v2 .sv-client-chip:hover{background:var(--sv-warn); border-color:var(--sv-warn);}
    .sell-v2 .sv-client-chip:hover .sv-client-text strong,
    .sell-v2 .sv-client-chip:hover .sv-client-text small{color:#fff;}
    .sell-v2 .sv-avatar{
        width:34px; height:34px; border-radius:50%; background:var(--sv-warn); color:#fff;
        display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex:0 0 auto;
    }
    .sell-v2 .sv-client-text{display:flex; flex-direction:column; align-items:flex-start; min-width:0;}
    .sell-v2 .sv-client-text strong{font-size:13.5px; color:var(--sv-ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:130px;}
    .sell-v2 .sv-client-text small{font-size:11.5px; color:var(--sv-warn-strong); font-weight:700;}
    .sell-v2 .sv-client-chip{cursor:pointer;}
    .sell-v2 .sv-client-remove{margin-left:2px; padding:4px; border-radius:50%; color:var(--sv-ink-3); text-decoration:none; line-height:1;}
    .sell-v2 .sv-client-remove:hover{background:var(--sv-danger); color:#fff;}
    .sell-v2 .sv-header-actions{display:flex; gap:8px; flex:0 0 auto;}
    .sell-v2 .sv-icon-btn{
        height:56px; min-width:56px; border-radius:var(--sv-radius-md);
        background:var(--sv-info-soft); color:var(--sv-info-strong); border:1px solid transparent;
        display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px;
        cursor:pointer; padding:4px 10px; transition:background .15s ease,color .15s ease;
    }
    .sell-v2 .sv-icon-btn:hover{background:var(--sv-info); color:#fff;}
    .sell-v2 .sv-icon-btn.success{background:var(--sv-accent-soft); color:var(--sv-accent-strong);}
    .sell-v2 .sv-icon-btn.success:hover{background:var(--sv-accent); color:#fff;}
    .sell-v2 .sv-icon-btn .lbl{font-size:10.5px; font-weight:700;}

    .sell-v2 .sv-context-row{display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding:10px 0 12px;}
    .sell-v2 .sv-context-chip{
        display:flex; align-items:center; gap:6px; font-size:12px; color:var(--sv-ink-2);
        background:var(--sv-paper); border:1px solid var(--sv-line); border-radius:999px; padding:5px 6px 5px 12px;
    }
    .sell-v2 .sv-context-chip .select2-container{min-width:130px;}
    .sell-v2 .sv-context-date{margin-left:auto; font-size:12px; color:var(--sv-ink-3);}

    /* ---------- Main ---------- */
    .sell-v2 .sv-main{height:68vh; min-width:0; display:grid; grid-template-columns:1fr 400px; gap:16px; padding:16px 20px; overflow:hidden;}
    .sell-v2 .sv-cart-panel, .sell-v2 .sv-quick-panel{
        display:flex; flex-direction:column; min-height:0; min-width:0;
        background:var(--sv-surface); border:1px solid var(--sv-line); border-radius:var(--sv-radius-lg);
        box-shadow:var(--sv-shadow-sm);
    }
    .sell-v2 .sv-panel-head{
        flex:0 0 auto; display:flex; align-items:baseline; justify-content:space-between; padding:16px 18px 10px;
    }
    .sell-v2 .sv-panel-head h2{font-size:15.5px; font-weight:700; margin:0;}
    .sell-v2 .sv-panel-head .sv-count{font-size:12px; font-weight:600; color:var(--sv-accent-strong); margin-left:8px;}
    .sell-v2 .sv-link-btn{
        background:none; border:0; cursor:pointer; color:var(--sv-ink-2); font-size:12.5px; font-weight:600;
        padding:4px 6px; border-radius:6px;
    }
    .sell-v2 .sv-link-btn:hover{color:var(--sv-danger); background:var(--sv-danger-soft);}

    .sell-v2 .sv-cart-scroll{flex:1 1 auto; min-height:0; min-width:0; overflow:auto; padding:0 8px 8px;}
    .sell-v2 table.sv-table{width:100%; min-width:920px; font-size:11.5px; border-collapse:collapse;}
    .sell-v2 table.sv-table thead th{
        font-size:10px; text-transform:uppercase; letter-spacing:.03em; color:var(--sv-ink-3); font-weight:700;
        text-align:right; padding:6px 8px; border-bottom:1px solid var(--sv-line);
        background:var(--sv-surface); position:sticky; top:0; z-index:1; white-space:nowrap;
    }
    .sell-v2 table.sv-table thead th:nth-child(2){text-align:left;}
    .sell-v2 table.sv-table tbody td{
        padding:7px 8px; vertical-align:middle; border-bottom:1px solid var(--sv-line); text-align:right;
    }
    .sell-v2 table.sv-table tbody tr:hover{background:var(--sv-paper);}
    /* silinmə düyməsi (sonuncu sütun) — cədvəl yanlara sürüşəndə də görünən
       qalsın deyə sağ kənara "yapışdırılır"; əks halda dar noutbuklarda malı
       silmək üçün əvvəlcə üfüqi skrolu sürükləmək lazım gəlirdi. */
    .sell-v2 table.sv-table th:last-child,
    .sell-v2 table.sv-table td:last-child{
        position:sticky; right:0; z-index:2;
        background:var(--sv-surface);
        box-shadow:-6px 0 8px -6px rgba(38,35,15,.25);
    }
    .sell-v2 table.sv-table thead th:last-child{z-index:3;}
    .sell-v2 table.sv-table tbody tr:hover td:last-child{background:var(--sv-paper);}
    .sell-v2 .sv-name-cell{font-size:14px; font-weight:600; text-align:left !important; line-height:1.3;}
    .sell-v2 .sv-num{font-size:12px; color:var(--sv-ink-2);}
    .sell-v2 .sv-sum-cell{font-size:14.5px; font-weight:700; color:var(--sv-accent-strong);}
    .sell-v2 .sv-tag{
        display:inline-block; font-size:10.5px; font-weight:700; color:var(--sv-ink-2);
        background:var(--sv-paper); border:1px solid var(--sv-line); border-radius:999px; padding:3px 8px;
    }
    .sell-v2 .sv-cost-btn{
        font-size:10px; font-weight:700; padding:4px 8px; border-radius:999px;
        border:1px solid var(--sv-line); background:var(--sv-paper); color:var(--sv-ink-2);
        cursor:pointer; white-space:nowrap;
    }
    .sell-v2 .sv-cost-btn:hover{border-color:var(--sv-accent); color:var(--sv-accent-strong);}
    .sell-v2 .sv-qty-stepper{
        display:inline-flex; align-items:center; gap:0; background:var(--sv-paper);
        border:1px solid var(--sv-line); border-radius:999px; padding:3px;
    }
    .sell-v2 .sv-qty-stepper button{
        width:22px; height:22px; border-radius:50%; border:0; background:transparent;
        display:flex; align-items:center; justify-content:center; cursor:pointer;
        color:var(--sv-ink-2); font-weight:700; padding:0;
    }
    .sell-v2 .sv-qty-stepper button:hover{background:var(--sv-accent); color:#fff;}
    .sell-v2 .sv-qty-stepper input{
        width:32px; text-align:center; border:0; background:transparent; font-size:13px; font-weight:700;
    }
    .sell-v2 .sv-price-input{
        width:56px; text-align:right; border:0; background:transparent; font-size:13px;
        color:var(--sv-ink); border-radius:6px; padding:2px 4px;
    }
    .sell-v2 .sv-price-input:focus{background:var(--sv-paper); outline:1px solid var(--sv-accent);}
    .sell-v2 .sv-cart-empty{
        display:flex; flex-direction:column; align-items:center; justify-content:center;
        gap:8px; padding:50px 20px; color:var(--sv-ink-3); text-align:center; font-size:13px;
    }

    /* ---------- Quick access ---------- */
    .sell-v2 .sv-quick-grid{
        flex:1 1 auto; min-height:0; overflow-y:auto;
        display:grid; grid-template-columns:repeat(3,1fr); gap:10px; padding:2px 14px 14px; align-content:start;
    }
    .sell-v2 .sv-quick-tile{
        display:flex; flex-direction:column; justify-content:space-between; gap:10px; text-align:left;
        min-height:78px; padding:10px 11px; border-radius:var(--sv-radius-md); border:1.5px solid var(--sv-line);
        background:var(--sv-surface); cursor:pointer;
        transition:transform .12s ease,border-color .12s ease,box-shadow .12s ease,background .12s ease;
    }
    .sell-v2 .sv-quick-tile:hover{border-color:var(--sv-accent); background:var(--sv-accent-soft); box-shadow:var(--sv-shadow-sm); transform:translateY(-1px);}
    .sell-v2 .sv-quick-tile:active{transform:translateY(0);}
    .sell-v2 .sv-quick-tile .qt-name{font-size:12px; font-weight:600; line-height:1.25; color:var(--sv-ink);}
    .sell-v2 .sv-quick-tile .qt-foot{display:flex; align-items:center; justify-content:space-between;}
    .sell-v2 .sv-quick-tile .qt-price{font-size:11.5px; color:var(--sv-accent-strong); font-weight:700;}
    .sell-v2 .sv-quick-tile .qt-add{
        width:18px; height:18px; border-radius:50%; background:var(--sv-accent); color:#fff;
        display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; flex:0 0 auto;
    }
    .sell-v2 .sv-quick-empty{padding:24px 16px; font-size:12.5px; color:var(--sv-ink-3); text-align:center;}

    /* ---------- Footer ---------- */
    /* position:fixed takes the footer out of .sell-v2's box, so its own
       overflow:hidden can't clip it — if the footer's content is ever wider
       than the viewport, it would push the whole page sideways. max-width +
       its own overflow-x scrolls the footer itself instead. */
    .sell-v2 .sv-footer{
        position:fixed; left:0; right:0; bottom:0; z-index:1030;
        max-width:100vw; overflow-x:auto;
        display:flex; align-items:center; gap:22px; padding:14px 20px;
        background:var(--sv-surface); border-top:1px solid var(--sv-line);
        box-shadow:0 -8px 20px rgba(33,31,27,.1); flex-wrap:wrap;
    }
    .sell-v2 .sv-field{display:flex; flex-direction:column; gap:3px;}
    .sell-v2 .sv-field > span{font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; color:var(--sv-ink-3); font-weight:700;}
    .sell-v2 .sv-field .sv-field-row{display:flex; align-items:center; gap:4px;}
    .sell-v2 .sv-field input, .sell-v2 .sv-field select{
        border:1px solid var(--sv-line); background:var(--sv-paper); border-radius:var(--sv-radius-sm);
        padding:7px 9px; font-size:13.5px; width:84px; outline:none; transition:border-color .15s ease;
    }
    .sell-v2 .sv-field select{width:auto; min-width:110px;}
    .sell-v2 .sv-field input:focus, .sell-v2 .sv-field select:focus{border-color:var(--sv-accent);}
    .sell-v2 .sv-field .sv-unit{font-size:12px; color:var(--sv-ink-3);}
    .sell-v2 .sv-change{display:flex; flex-direction:column; gap:3px; padding-left:10px; border-left:1px solid var(--sv-line);}
    .sell-v2 .sv-change span:first-child{font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; color:var(--sv-ink-3); font-weight:700;}
    .sell-v2 .sv-change span:last-child{font-size:18px; font-weight:800; color:var(--sv-accent-strong);}
    .sell-v2 .sv-change.negative span:last-child{color:var(--sv-danger);}
    .sell-v2 .sv-secondary-btns{display:flex; gap:8px; margin-left:auto; order:2;}
    .sell-v2 .sv-btn{
        display:inline-flex; align-items:center; gap:6px; border-radius:var(--sv-radius-sm);
        border:1.5px solid var(--sv-line); background:var(--sv-surface); padding:10px 14px;
        font-size:13px; font-weight:700; cursor:pointer; color:var(--sv-ink-2);
        transition:background .15s ease,color .15s ease,border-color .15s ease;
    }
    .sell-v2 .sv-btn:hover{background:var(--sv-info-soft); color:var(--sv-info-strong); border-color:var(--sv-info);}
    .sell-v2 .sv-btn.sv-danger{color:var(--sv-danger); border-color:var(--sv-danger-soft);}
    .sell-v2 .sv-btn.sv-danger:hover{background:var(--sv-danger); color:#fff; border-color:var(--sv-danger);}
    .sell-v2 .sv-btn.sv-warn{color:var(--sv-warn-strong); border-color:var(--sv-warn-soft);}
    .sell-v2 .sv-btn.sv-warn:hover{background:var(--sv-warn); color:#fff; border-color:var(--sv-warn);}
    .sell-v2 .sv-btn .sv-kbd{margin-left:2px;}
    .sell-v2 .sv-total-figures{
        display:flex; flex-direction:column; align-items:flex-end; padding-left:16px;
        border-left:1px solid var(--sv-line); order:3;
    }
    .sell-v2 .sv-total-figures span:first-child{font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; color:var(--sv-ink-3); font-weight:700;}
    .sell-v2 .sv-total-figures span:last-child{font-size:26px; font-weight:800; letter-spacing:-.01em; color:var(--sv-danger);}
    .sell-v2 .sv-cta{
        display:inline-flex; align-items:center; gap:10px; background:var(--sv-accent); color:var(--sv-accent-ink);
        border:0; border-radius:999px; padding:16px 26px; font-size:15.5px; font-weight:800; cursor:pointer;
        box-shadow:var(--sv-shadow-md); transition:background .15s ease,transform .1s ease; white-space:nowrap; order:4;
    }
    .sell-v2 .sv-cta:hover{background:var(--sv-accent-strong);}
    .sell-v2 .sv-cta:active{transform:scale(.98);}
    .sell-v2 .sv-cta .sv-kbd{background:rgba(0,0,0,.2); border-color:transparent; color:var(--sv-accent-ink);}

    /* Pjax renders a wrapping div#grid-update; make it layout-transparent so its
       children stay direct flex items of .sv-footer (Yekun lives inside this
       container so it refreshes after qty/price edits, same as the original view). */
    .sell-v2 #grid-update{display:contents;}

    @media (max-width:980px){
        .sell-v2 .sv-main{grid-template-columns:1fr;}
        .sell-v2 .sv-quick-grid{grid-template-columns:repeat(4,1fr); max-height:220px;}
        .sell-v2 .sv-footer{flex-direction:column; align-items:stretch;}
        .sell-v2 .sv-total-figures{border-left:0; padding-left:0;}
        .sell-v2 .sv-secondary-btns{margin-left:0;}
    }
</style>

    <div class="noprint sv-header">
        <div class="sv-search-row">
            <div class="sv-search-field">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.2" y2="16.2"/></svg>
                <?= Html::input("text", 'barcode', '', ['id' => 'barcode', 'autocomplete' => 'off', 'placeholder' => 'Barkodu skan edin və ya məhsul axtarın…', 'onChange' => 'addSellBarcode($("#barcode").val())']) ?>
                <span class="sv-kbd">F2</span>
            </div>
            <?= Html::button('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16"/><path d="M7 12h10"/><path d="M10 18h4"/></svg>', ['value' => Url::to(['sell/find']), 'class' => 'sv-icon-btn-solo', 'id' => 'sell_dialog', 'title' => 'Axtarış']) ?>

            <div class="sv-client-chip" role="button" tabindex="0" title="Müştəri seçin" data-value="<?= Url::to(['sell/client']) ?>">
                <span class="sv-avatar"><?= Html::encode($clientInitials) ?></span>
                <span class="sv-client-text">
                    <strong><?= Html::encode($clientName ?: 'Müştəri seçilməyib') ?></strong>
                    <small><?= $bonus ?> bonus</small>
                </span>
                <a href="delete-client-v2" class="sv-client-remove" title="Müştərini sil"><i class="glyphicon glyphicon-remove"></i></a>
            </div>
            <?= Html::button('<i class="glyphicon glyphicon-user"></i>', ['value' => Url::to(['sell/client']), 'class' => 'sv-icon-btn-solo', 'id' => 'client_dialog', 'title' => 'Müştəri']) ?>

            <div class="sv-header-actions">
                <?= Html::button('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5v5l3 2"/></svg><span class="lbl">F6</span>', ['value' => Url::to(['sell/postponed']), 'class' => 'sv-icon-btn', 'id' => 'postponed_dialog', 'title' => 'Gözləmədə']) ?>
                <?php if (Yii::$app->user->identity->id_role != 1): ?>
                    <?= Html::button('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10h18"/><path d="M7 14.5h3"/></svg><span class="lbl">Borc</span>', ['value' => Url::to(['sell/dialog']), 'class' => 'sv-icon-btn success', 'id' => 'dclient', 'title' => 'Borc ödənişi']) ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="sv-context-row">
            <div class="sv-context-chip" style="<?= $displaySettings['show_store'] ? '' : 'display:none' ?>">
                Anbar
                <?= Select2::widget([
                    'data' => ArrayHelper::map(Store::find()->all(), 'id', 'name'),
                    'name' => 'store',
                    'value' => $store,
                    'options' => ['placeholder' => 'Seçin', 'id' => 'store2'],
                ]) ?>
            </div>
            <div class="sv-context-chip" style="<?= $displaySettings['show_seller'] ? '' : 'display:none' ?>">
                Satıcı
                <?= Select2::widget([
                    'data' => ArrayHelper::map(Users::find()->all(), 'id_user', 'fio'),
                    'name' => 'user',
                    'value' => $user,
                    'options' => ['placeholder' => 'Seçin', 'id' => 'user'],
                ]) ?>
            </div>
            <div class="sv-context-date"><?= date('d.m.Y') ?></div>
        </div>
    </div>

    <?php
    Modal::begin(['options' => ['id' => 'login-modal', 'tabindex' => '-1'], 'size' => 'modal-sm']);
    echo '<div id="loginContent"></div>';
    Modal::end();

    Modal::begin(['options' => ['id' => 'sell-modal', 'tabindex' => true]]);
    echo '<div id="modalContent"></div>';
    Modal::end();

    Modal::begin(['options' => ['id' => 'chek', 'tabindex' => true, 'class' => 'rena_dialog']]);
    echo '<div id="chekContent"></div>';
    Modal::end();

    Modal::begin(['options' => ['id' => 'client-modal', 'tabindex' => true], 'size' => 'modal-lg']);
    echo '<div id="clientContent1"></div>';
    echo '<div id="clientContent"></div>';
    Modal::end();

    Modal::begin(['header' => '<h2>Yeni müştəri  yarat</h2>', 'id' => 'client-create', 'size' => 'modal-sm']);
    echo '<div id="clientContent1"></div>';
    Modal::end();

    Modal::begin(['options' => ['id' => 'dclient-modal', 'tabindex' => true], 'size' => 'modal-lg']);
    echo '<div id="dclientContent"></div>';
    Modal::end();

    Modal::begin(['options' => ['id' => 'return-modal', 'tabindex' => true], 'size' => 'modal-lg']);
    echo '<div id="returnContent"></div>';
    Modal::end();

    Modal::begin(['options' => ['id' => 'postponed-modal', 'tabindex' => true], 'size' => 'modal-rena-lg']);
    echo '<div id="postponedContent"></div>';
    Modal::end();

    $i = 0;
    ?>

    <?php Pjax::begin(['id' => 'grid-arrival']); ?>

    <div class="noprint sv-main">
        <div class="sv-cart-panel">
            <div class="sv-panel-head">
                <h2>Səbət <span class="sv-count"><?= $dataProvider->getTotalCount() ?> məhsul</span></h2>
                <button type="button" class="sv-link-btn" onclick="deleteAll()">Hamısını sil</button>
            </div>
            <div class="sv-cart-scroll">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'sv-table'],
                    'emptyText' => '<div class="sv-cart-empty"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.2" y2="16.2"/></svg><div>Səbət boşdur — axtarın və ya sürətli satışdan seçin</div></div>',
                    'columns' => [
                        ['class' => 'kartik\grid\SerialColumn'],
                        [
                            'label' => 'Malın adı',
                            'value' => 'nameProduct',
                            'format' => 'raw',
                            'contentOptions' => ['class' => 'sv-name-cell'],
                        ],
                        [
                            'attribute' => 'quantity',
                            'label' => 'Say',
                            'format' => 'raw',
                            'value' => function ($model, $index, $widget) {
                                $qid = 'qty' . $model->id;
                                return '<div class="sv-qty-stepper">'
                                    . '<button type="button" onclick="var el=document.getElementById(\'' . $qid . '\');el.value=Math.max(1,(parseFloat(el.value)||0)-1);editQuantity(' . $model->id . ',el.value);">−</button>'
                                    . Html::input('text', 'quantity[]', $model->quantity, ['id' => $qid, 'onChange' => "editQuantity($model->id,this.value)"])
                                    . '<button type="button" onclick="var el=document.getElementById(\'' . $qid . '\');el.value=(parseFloat(el.value)||0)+1;editQuantity(' . $model->id . ',el.value);">+</button>'
                                    . '</div>';
                            },
                        ],
                        [
                            'attribute' => 'price',
                            'label' => 'Qiymət',
                            'format' => 'raw',
                            'value' => function ($model, $index, $widget) use (&$i) {
                                $i++;
                                return Html::input('text', 'price[]', $model->price, ['class' => 'sv-price-input tabular', 'id' => "price$i", 'onChange' => "editPrice($model->id,this.value)"]);
                            },
                        ],
                        [
                            'attribute' => 'sum',
                            'label' => 'Sum',
                            'contentOptions' => function ($model, $index, $widget) {
                                return ['id' => 'sum' . $model->id, 'class' => 'sv-sum-cell tabular'];
                            },
                        ],
                        [
                            'label' => 'Topdan',
                            'format' => 'raw',
                            'value' => 'priceOpt',
                            'contentOptions' => ['class' => 'sv-num tabular'],
                        ],
                        [
                            'label' => 'Gəliş',
                            'format' => 'raw',
                            'value' => function ($model, $index, $widget) {
                                return "<span id=$model->id>" . "</span>"
                                    . Html::button('Göstər', ['id' => "show_$model->id", 'class' => 'sv-cost-btn', 'onclick' => "showPrice($model->valuePriceAr,$model->id)"])
                                    . Html::button('Gizlət', ['id' => "hid_$model->id", 'class' => 'sv-cost-btn', 'style' => 'visibility:hidden', 'onclick' => "hidePrice($model->valuePriceAr,$model->id)"]);
                            },
                        ],
                        [
                            'label' => 'Ədədin',
                            'value' => 'priceTop',
                            'contentOptions' => ['class' => 'sv-num tabular'],
                            'visible' => $displaySettings['show_unit_price'],
                        ],
                        [
                            'label' => 'Qutuda',
                            'value' => 'pack',
                            'contentOptions' => ['class' => 'sv-num tabular'],
                            'visible' => $displaySettings['show_box_count'],
                        ],
                        [
                            'label' => 'Anbarda',
                            'value' => 'sumCount',
                            'contentOptions' => ['class' => 'sv-num tabular'],
                        ],
                        [
                            'label' => 'Polka',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return '<span class="sv-tag">' . Html::encode($model->polka) . '</span>';
                            },
                            'visible' => $displaySettings['show_shelf'],
                        ],
                        [
                            'class' => 'kartik\grid\ActionColumn',
                            'template' => '{delete}',
                            'urlCreator' => function ($action, $model, $key, $index) {
                                return Url::to(['sell/delete-v2', 'id' => $model->id]);
                            },
                        ],
                    ],
                    'layout' => '{items}',
                    'showHeader' => true,
                ]) ?>
            </div>
        </div>

        <div class="sv-quick-panel">
            <div class="sv-panel-head">
                <h2>Sürətli satış</h2>
            </div>
            <div class="sv-quick-grid">
                <?php if ($quickAccessProducts): ?>
                    <?php foreach ($quickAccessProducts as $qp): ?>
                        <button type="button" class="sv-quick-tile" onclick="addSellId(<?= (int) $qp->id ?>)">
                            <span class="qt-name"><?= Html::encode($qp->name) ?></span>
                            <span class="qt-foot">
                                <span class="qt-price tabular"><?= number_format($quickTilePrice($qp->id), 2) ?> ₼</span>
                                <span class="qt-add">+</span>
                            </span>
                        </button>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="sv-quick-empty">Sürətli satış üçün mal seçilməyib.<br>Mallar səhifəsində "Sürətli satış" işarəsini qeyd edin.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="noprint sv-footer">
        <?php Pjax::begin(['id' => 'grid-update']); ?>
        <div class="sv-field">
            <span>Verdi (F9)</span>
            <div class="sv-field-row">
                <input type="text" id="money">
                <span class="sv-unit">+</span>
                <input id="virtual" type="text" disabled style="width:56px">
            </div>
        </div>
        <div class="sv-field" style="<?= $displaySettings['show_discount'] ? '' : 'display:none' ?>">
            <span>Güzəşt</span>
            <div class="sv-field-row"><input type="text" id="discount"><span class="sv-unit">₼</span></div>
        </div>
        <div class="sv-field">
            <span>Ödəniş</span>
            <?= Select2::widget([
                'data' => ArrayHelper::map(Kassa::find()->where(["pos" => 1])->all(), 'id', 'name'),
                'name' => 'kassa',
                'options' => ['id' => 'kassa', 'onchange' => 'changePos()', 'placeholder' => 'Ödəniş üsulu'],
            ]) ?>
            <select style="display:none" id="rate"><option value="1">AZN</option></select>
        </div>
        <div class="sv-change" id="sdachaBox">
            <span>Qaytarılmalıdır</span>
            <span id="sdacha" class="tabular">0</span>
        </div>
        <span id="usd1" style="display:none"><?= round($usd ?? 0, 2) ?></span>
        <div class="sv-total-figures">
            <span>Yekun</span>
            <span id="sum" class="tabular"><?= round($sum, 3) ?></span>
        </div>
        <?php Pjax::end(); ?>

        <div class="hid" style="display:none">
            <?= DatePicker::widget([
                'name' => 'check_issue_date',
                'id' => 'date',
                'value' => date('Y-m-d'),
                'type' => DatePicker::TYPE_INPUT,
                'pluginOptions' => ['format' => 'yyyy-mm-dd', 'todayHighlight' => false],
            ]) ?>
        </div>

        <div class="sv-secondary-btns">
            <?= Html::button('<i class="glyphicon glyphicon-time"></i> Gözlə <span class="sv-kbd">F6</span>', ['class' => 'sv-btn', 'id' => 'postponed1', 'onclick' => 'receivedSell2($("#money").val(),$("#date").val(),$("#rate").val(),$("#store2").val(),$("#user").val(),$("#discount").val())']) ?>
            <?= Html::button('<i class="glyphicon glyphicon-remove"></i> Ləğv et', ['class' => 'sv-btn sv-danger', 'onclick' => 'deleteAll()']) ?>
            <?php if (Yii::$app->user->identity->id_role != 1): ?>
                <?= Html::button('<i class="glyphicon glyphicon-share-alt"></i> Vozrat', ['class' => 'sv-btn sv-warn', 'onclick' => 'returnSellPassword()']) ?>
            <?php endif; ?>
        </div>

        <?php
        $okOnclick = 'receivedSell($("#money").val(),$("#date").val(),$("#rate").val(),$("#store2").val(),$("#user").val(),' . (Yii::$app->session->get('id_client') != 1 ? 1 : 0) . ',$("#discount").val(),$("#kassa").val(),$("#virtual").val())';
        echo Html::button('<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12.5l5 5L20 6.5"/></svg><span>Satışı tamamla</span><span class="sv-kbd">F8</span>', ['class' => 'sv-cta', 'id' => 'received', 'onclick' => $okOnclick]);
        ?>
    </div>

    <?php Pjax::end(); ?>

</div>
<?php
$script = <<< JS

$(document).ready(function(){
    $("#barcode").val("");
    $("#barcode").focus();

 $('body').keydown(function(event){
  if ( event.which==120 ) $("#money").focus();
  if ( event.which==118 ) $("#barcode").focus();
  if ( event.which==119 )  { $("#received").click(); event.which=0;}
  if ( event.which==117)  { $("#postponed_dialog").click(); event.which=0;}
  if ( event.which==115)  { $("#postponed1").click(); event.which=0;}
  if ( event.which==113 )  { $("#sell_dialog").click(); event.which=0;}
 });

 $('#money').keyup(function(){
    var sdacha=$("#sum").text() - $("#money").val() - $("#virtual").val();
    sdacha=-sdacha.toFixed(2)
    $("#sdacha").html(sdacha);
    $("#sdachaBox").toggleClass('negative', sdacha < 0);
 });
});
JS;
$this->registerJs($script);
?>
