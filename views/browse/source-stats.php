<?php

use yii\helpers\Html;

/** @var $source app\models\Source */
/** @var $rows array */

$this->title = 'Статистика — Source #' . $source->id;
?>

<h1><?= Html::encode($this->title) ?></h1>

<div style="margin-bottom:10px;">
    <?= Html::a('Перегляд джерела', ['browse/source-view', 'id' => $source->id], ['class'=>'btn btn-default']) ?>
    <?= Html::a('До джерел', ['browse/sources'], ['class'=>'btn btn-link']) ?>
</div>

<?php foreach ($rows as $r): ?>
    <div class="card" style="padding:12px; margin-bottom:10px;">
        <div style="display:flex; justify-content:space-between; gap:10px;">
            <div>
                <b>#<?= (int)$r['id'] ?></b> · <?= Html::encode($r['topic']) ?>
                <?php if (!empty($r['page_or_section'])): ?>
                    · <?= Html::encode($r['page_or_section']) ?>
                <?php endif; ?>
            </div>
            <div>
                <span class="label label-success">✅ <?= (int)$r['likes'] ?></span>
                <span class="label label-danger" style="margin-left:6px;">❌ <?= (int)$r['dislikes'] ?></span>
                <span class="label label-warning" style="margin-left:6px;">⏳ <?= (int)$r['later'] ?></span>
                <span class="label label-default" style="margin-left:6px;">всього <?= (int)$r['total'] ?></span>
            </div>
        </div>

        <div style="margin-top:8px;">
            <div style="font-weight:600;">EN</div>
            <div style="white-space:pre-wrap; border:1px solid #eee; padding:10px; border-radius:10px;">
                <?= Html::encode($r['exact_quote_en']) ?>
            </div>
        </div>

        <div style="margin-top:10px;">
            <div style="font-weight:600;">UA</div>
            <div style="white-space:pre-wrap; border:1px solid #eee; padding:10px; border-radius:10px;">
                <?= Html::encode($r['translation_uk']) ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
