<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var $this View */
/** @var $quote app\models\Quote|null */
/** @var $source_id mixed */
/** @var $section mixed */

$this->title = 'Відбір цитат';

$voteUrl = Url::to(['swipe/vote']);
$nextUrl = Url::to(['swipe/index', 'source_id' => $source_id, 'section' => $section]);
$csrf = Yii::$app->request->getCsrfToken();

$css = <<<CSS
/* Fullscreen mobile layout */
.swipe-wrap{
  max-width: 980px;
  margin: 0 auto;
  padding: 10px;
}

/* На телефоні робимо повний екран */
@media (max-width: 768px){
  body{ background:#f5f5f5; }
  .swipe-wrap{
    max-width: none;
    padding: 0;
    height: calc(100vh - 60px);
  }
  .swipe-card{
    height: calc(100vh - 60px);
    border-radius: 0;
  }
}

/* Card */
.swipe-card{
  background:#fff;
  border:1px solid #e7e7e7;
  border-radius: 16px;
  overflow: hidden;
  height: 75vh;
  display:flex;
  flex-direction:column;
}

/* Header inside card */
.swipe-head{
  padding: 12px 14px;
  border-bottom:1px solid #f0f0f0;
  font-size: 12px;
  opacity: .75;
}

/* Scrollable content area */
.swipe-content{
  padding: 14px;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  flex: 1 1 auto;
}

.swipe-block-title{
  font-weight:600;
  margin-bottom:6px;
}

.swipe-text{
  white-space: pre-wrap;
  line-height: 1.35;
}

/* Bottom action bar */
.swipe-actions{
  padding: 12px 14px;
  border-top:1px solid #f0f0f0;
  display:flex;
  justify-content: center;
  gap: 16px;
  align-items:center;
}

/* Round buttons */
.round-btn{
  width: 64px;
  height: 64px;
  border-radius: 50%;
  border: 1px solid #e0e0e0;
  background: #fff;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size: 26px;
  cursor:pointer;
  box-shadow: 0 8px 18px rgba(0,0,0,.08);
}
.round-btn:active{
  transform: scale(.98);
}

/* Optional: color accents (легкі, але можна прибрати) */
.round-like{ border-color: rgba(40,167,69,.35); }
.round-dislike{ border-color: rgba(220,53,69,.35); }
.round-later{ border-color: rgba(255,193,7,.45); }

.swipe-msg{
  text-align:center;
  font-size:12px;
  opacity:.75;
  padding: 6px 14px 10px;
}

CSS;

$this->registerCss($css);
?>

<h1 style="margin:10px 10px 8px;"><?= Html::encode($this->title) ?></h1>

<div class="swipe-wrap">
<?php if (!$quote): ?>
    <div class="alert alert-success" style="margin:10px;">
        Все ✅ Немає більше цитат для оцінювання (по цих фільтрах).
    </div>
<?php else: ?>

    <div class="swipe-card">
        <div class="swipe-head">
            Тема: <b><?= Html::encode($quote->topic) ?></b>
            <?php if ($quote->page_or_section): ?>
                · <?= Html::encode($quote->page_or_section) ?>
            <?php endif; ?>
        </div>

        <div class="swipe-content" id="swipeContent">
            <div class="swipe-block-title">UA (переклад)</div>
            <div class="swipe-text"><?= Html::encode($quote->translation_uk) ?></div>
        </div>

        <div class="swipe-actions">
            <button class="round-btn round-dislike" type="button" onclick="vote(0)" aria-label="Не підходить">✕</button>
            <button class="round-btn round-later" type="button" onclick="vote(2)" aria-label="Потім">⏳</button>
            <button class="round-btn round-like" type="button" onclick="vote(1)" aria-label="Корисно">♥</button>
        </div>

        <div class="swipe-msg" id="msg"></div>
    </div>

    <script>
        async function vote(decision) {
            const msg = document.getElementById('msg');
            msg.textContent = 'Зберігаю...';

            const formData = new URLSearchParams();
            formData.append('_csrf', <?= json_encode($csrf) ?>);
            formData.append('quote_id', <?= (int)$quote->id ?>);
            formData.append('decision', decision);

            try {
                const res = await fetch(<?= json_encode($voteUrl) ?>, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'Accept': 'application/json'
                    },
                    body: formData.toString()
                });

                const text = await res.text();

                if (!res.ok) {
                    msg.textContent = 'HTTP ' + res.status + '. Дивись Network → Response.';
                    console.error(text);
                    return;
                }

                let data = null;
                try { data = JSON.parse(text); } catch (e) {
                    msg.textContent = 'Сервер повернув не-JSON (дивись консоль).';
                    console.error(text);
                    return;
                }

                if (!data.ok) {
                    msg.textContent = 'Помилка: ' + (data.error ? JSON.stringify(data.error) : 'unknown');
                    return;
                }

                window.location.href = <?= json_encode($nextUrl) ?>;
            } catch (e) {
                msg.textContent = 'Помилка мережі: ' + e;
            }
        }

        // хоткеї (для ПК)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight') vote(1);
            if (e.key === 'ArrowLeft')  vote(0);
            if (e.key === 'ArrowDown')  vote(2);
        });

        // зробимо так, щоб при завантаженні контент був зверху
        (function(){
            const el = document.getElementById('swipeContent');
            if (el) el.scrollTop = 0;
        })();
    </script>

<?php endif; ?>
</div>
