<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var $provider yii\data\ActiveDataProvider */
/** @var $activeDecision int|null */
/** @var $counts array */

$this->title = 'Мої відповіді';

$css = <<<CSS
.tabs-row{ display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
.tab-btn{
  display:inline-flex; align-items:center; gap:8px;
  padding:10px 12px;
  border:1px solid #ddd;
  border-radius:12px;
  background:#fff;
  text-decoration:none;
}
.tab-btn:hover{ background:#f7f7f7; text-decoration:none; }
.tab-btn.active{ border-color:#222; box-shadow:0 0 0 2px rgba(0,0,0,.05); }
.tab-count{
  font-size:12px; opacity:.75;
  padding:2px 8px;
  border:1px solid #eee;
  border-radius:999px;
}
.vote-card{
  border:1px solid #e7e7e7;
  border-radius:14px;
  padding:12px;
  margin-bottom:10px;
  background:#fff;
}
.vote-top{
  display:flex;
  justify-content:space-between;
  gap:10px;
  align-items:center;
  flex-wrap:wrap;
}
.vote-meta{ font-size:12px; opacity:.75; }
.vote-quote{
  margin-top:10px;
  border:1px solid #eee;
  border-radius:12px;
  padding:10px;
}
.lang-switch{
  border:1px solid #ddd;
  background:#fff;
  padding:6px 10px;
  border-radius:999px;
  font-size:12px;
  cursor:pointer;
}
.lang-switch.active{
  border-color:#000;
  box-shadow:0 0 0 2px rgba(0,0,0,.05);
}
CSS;

$this->registerCss($css);

function decisionBadge($decision) {
    $d = (int)$decision;
    if ($d === 1) return '<span class="label label-success">корисно</span>';
    if ($d === 0) return '<span class="label label-danger">не підходить</span>';
    return '<span class="label label-warning">потім</span>';
}

function topicUa($topic) {
    $map = [
        'definition' => 'Визначення',
        'symptoms' => 'Симптоми',
        'causes' => 'Причини',
        'risk_factors' => 'Фактори ризику',
        'pathogenesis' => 'Патогенез',
        'statistics' => 'Статистика',
        'other' => 'Інше',
    ];
    $t = (string)$topic;
    return isset($map[$t]) ? $map[$t] : $t;
}

$tab = function($decisionValue, $label) use ($activeDecision, $counts) {
    $isActive = ((string)$activeDecision === (string)$decisionValue);
    $class = 'tab-btn' . ($isActive ? ' active' : '');
    $count = isset($counts[$decisionValue]) ? (int)$counts[$decisionValue] : 0;

    return Html::a(
        $label . ' <span class="tab-count">' . $count . '</span>',
        ['browse/my-votes', 'decision' => $decisionValue],
        ['class' => $class, 'encode' => false]
    );
};
?>

<h1><?= Html::encode($this->title) ?></h1>

<div style="margin-bottom:10px;">
    <?= Html::a('Джерела', ['browse/sources'], ['class' => 'btn btn-default']) ?>
</div>

<!-- Вкладки -->
<div class="tabs-row">
    <?= $tab(1, '✅ Корисно') ?>
    <?= $tab(0, '❌ Не підходить') ?>
    <?= $tab(2, '⏳ Потім') ?>
</div>

<?php
$models = $provider->getModels();
if (!$models) {
    echo '<div class="alert alert-info">Немає записів у цьому розділі.</div>';
}
?>

<?php foreach ($models as $m): ?>
    <?php
        // Vote model = $m
        $quote = $m->quote; // важливо щоб був getQuote() relation
        $sid = $quote ? $quote->source_id : null;
        $topic = $quote ? $quote->topic : '';
        $en = $quote ? $quote->exact_quote_en : '';
        $ua = $quote ? $quote->translation_uk : '';
    ?>
    <div class="vote-card">
        <div class="vote-top">
            <div><?= decisionBadge($m->decision) ?></div>
            <div class="vote-meta"><?= Html::encode(date('Y-m-d H:i', $m->created_at)) ?></div>
        </div>

        <div class="vote-meta" style="margin-top:6px;">
            <?php if ($sid): ?>
                <?= Html::a('Source #' . $sid, ['browse/source-view', 'id' => $sid]) ?>
            <?php else: ?>
                Source: -
            <?php endif; ?>

            <?php if ($topic): ?>
                · Тема: <b><?= Html::encode(topicUa($topic)) ?></b>
            <?php endif; ?>
        </div>

        <?php if ($ua || $en): ?>
            <div class="vote-quote">

                <!-- Перемикач -->
                <div style="display:flex; gap:8px; margin-bottom:8px;">
                    <button type="button" class="lang-switch active" onclick="switchLang(this, 'ua')">UA</button>
                    <button type="button" class="lang-switch" onclick="switchLang(this, 'en')">EN</button>
                </div>

                <!-- UA блок -->
                <div class="lang-block lang-ua">
                    <div style="font-weight:600; margin-bottom:6px;">UA</div>
                    <?= Html::encode($ua) ?>
                </div>

                <!-- EN блок -->
                <div class="lang-block lang-en" style="display:none;">
                    <div style="font-weight:600; margin-bottom:6px;">EN</div>
                    <?= Html::encode($en) ?>
                </div>

            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<div style="margin-top:10px;">
    <?= LinkPager::widget(['pagination' => $provider->pagination]) ?>
</div>

<?php
$this->registerJs(<<<JS
window.switchLang = function(btn, lang) {
    const card = btn.closest('.vote-quote');

    const ua = card.querySelector('.lang-ua');
    const en = card.querySelector('.lang-en');
    const buttons = card.querySelectorAll('.lang-switch');

    buttons.forEach(b => b.classList.remove('active'));

    if (lang === 'ua') {
        ua.style.display = 'block';
        en.style.display = 'none';
    } else {
        ua.style.display = 'none';
        en.style.display = 'block';
    }

    btn.classList.add('active');
};
JS
, \yii\web\View::POS_END);
?>
