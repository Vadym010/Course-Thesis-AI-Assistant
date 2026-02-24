<?php

use yii\grid\GridView;
use yii\helpers\Html;

/** @var $provider yii\data\ActiveDataProvider */

$this->title = 'Джерела';
?>

<h1><?= Html::encode($this->title) ?></h1>

<div style="margin-bottom:10px;">
    <?= Html::a('Імпорт JSON', ['import/quotes'], ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Мої відповіді', ['browse/my-votes'], ['class' => 'btn btn-default']) ?>
</div>

<?= GridView::widget([
    'dataProvider' => $provider,
    'columns' => [
        'id',
        'section',
        [
            'attribute' => 'title',
            'value' => fn($m) => $m->title ?: '(без назви)',
        ],
        [
            'attribute' => 'url',
            'format' => 'url',
            'value' => fn($m) => $m->url,
        ],
        'year',
        [
            'label' => 'Цитати',
            'value' => fn($m) => $m->getQuotes()->count(),
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view} {stats}',
            'buttons' => [
                'view' => fn($url, $m) => Html::a('Перегляд', ['browse/source-view', 'id' => $m->id]),
                'stats' => fn($url, $m) => Html::a('Статистика', ['browse/source-stats', 'id' => $m->id], ['style'=>'margin-left:8px;']),
            ],
        ],
    ],
]); ?>
