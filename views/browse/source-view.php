<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/** @var $source app\models\Source */
/** @var $quotesProvider yii\data\ActiveDataProvider */
/** @var $decisionMap array<int,int> */

$this->title = 'Джерело #' . $source->id;
?>

<h1><?= Html::encode($this->title) ?></h1>

<div class="card" style="padding:12px; max-width: 1100px;">
    <div><b>Підпункт:</b> <?= Html::encode($source->section) ?></div>
    <div><b>Назва:</b> <?= Html::encode($source->title ?: '(без назви)') ?></div>
    <div><b>Рік:</b> <?= Html::encode($source->year ?: '-') ?></div>
    <div><b>URL:</b> <?= Html::a(Html::encode($source->url), $source->url, ['target'=>'_blank', 'rel'=>'noopener']) ?></div>

    <div style="margin-top:10px; display:flex; gap:10px;">
        <?= Html::a('Свайпати це джерело', ['swipe/index', 'source_id' => $source->id], ['class'=>'btn btn-success']) ?>
        <?= Html::a('Статистика по джерелу', ['browse/source-stats', 'id' => $source->id], ['class'=>'btn btn-default']) ?>
        <?= Html::a('Назад до списку', ['browse/sources'], ['class'=>'btn btn-link']) ?>
    </div>
</div>

<h3 style="margin-top:16px;">Цитати</h3>

<?php
$badge = function($decision) {
    if ($decision === null) return '<span class="label label-default">не оцінено</span>';
    if ($decision === 1) return '<span class="label label-success">корисно</span>';
    if ($decision === 0) return '<span class="label label-danger">не підходить</span>';
    return '<span class="label label-warning">потім</span>';
};
?>

<?= GridView::widget([
    'dataProvider' => $quotesProvider,
    'columns' => [
        'id',
        'topic',
        'page_or_section',
        [
            'label' => 'Моє рішення',
            'format' => 'raw',
            'value' => function($q) use ($decisionMap, $badge) {
                $d = $decisionMap[$q->id] ?? null;
                return $badge($d);
            }
        ],
        [
            'label' => 'EN',
            'format' => 'ntext',
            'value' => fn($q) => $q->exact_quote_en,
            'contentOptions' => ['style' => 'max-width:520px; white-space:pre-wrap;'],
        ],
        [
            'label' => 'UA',
            'format' => 'ntext',
            'value' => fn($q) => $q->translation_uk,
            'contentOptions' => ['style' => 'max-width:520px; white-space:pre-wrap;'],
        ],
    ],
]); ?>
