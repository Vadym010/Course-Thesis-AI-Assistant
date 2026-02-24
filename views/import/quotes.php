<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $this yii\web\View */
/** @var $model app\models\ImportQuotesForm */

$this->title = 'Імпорт цитат (JSON)';
?>

<h1><?= Html::encode($this->title) ?></h1>

<?php foreach (['success','error'] as $t): ?>
    <?php if (Yii::$app->session->hasFlash($t)): ?>
        <div class="alert alert-<?= $t === 'success' ? 'success' : 'danger' ?>">
            <?= Html::encode(Yii::$app->session->getFlash($t)) ?>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card" style="padding:16px; max-width: 980px;">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'json')->textarea([
        'rows' => 18,
        'placeholder' => "Встав сюди JSON, який я повертаю (section/source/quotes)...",
        'style' => 'font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;'
    ]) ?>

    <div style="display:flex; gap:10px;">
        <?= Html::submitButton('Імпортувати', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Очистити', ['import/quotes'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>