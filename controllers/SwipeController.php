<?php

namespace app\modules\education\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\db\Expression;
use app\modules\education\models\Quotes;
use app\modules\education\models\Votes;

class SwipeController extends Controller
{
    public function behaviors(): array
    {
        $this->layout = \app\helpers\UniversalHelper::getLayout();

        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'vote'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // тільки логін
                    ],
                ],
            ],
        ];
    }

    /**
     * Показує 1 наступну цитату, яку юзер ще не оцінював.
     * Можна фільтрувати по source_id або section (за потреби).
     */
    public function actionIndex($source_id = null, $section = null)
    {
        $userId = (int)Yii::$app->user->id;

        $q = Quotes::find()->alias('q')
            ->leftJoin('{{%votes}} v', 'v.quote_id = q.id AND v.user_id = :uid', [':uid' => $userId])
            ->andWhere(['v.id' => null]) // ще не голосував
            ->orderBy(new Expression('RAND()')); // або ->orderBy(['q.id' => SORT_ASC])

        if ($source_id !== null) {
            $q->andWhere(['q.source_id' => (int)$source_id]);
        }

        if ($section !== null) {
            // section зберігається в sources, тому join:
            $q->innerJoin('{{%sources}} s', 's.id = q.source_id')
              ->andWhere(['s.section' => (string)$section]);
        }

        $quote = $q->one();

        return $this->render('index', [
            'quote' => $quote,
            'source_id' => $source_id,
            'section' => $section,
        ]);
    }

    /**
     * Приймає POST: quote_id + decision(0/1/2), зберігає і повертає json.
     */
    public function actionVote()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $request = Yii::$app->request;

            if (!$request->isPost) {
                return ['ok' => false, 'error' => 'POST required'];
            }

            if (Yii::$app->user->isGuest) {
                return ['ok' => false, 'error' => 'Unauthorized'];
            }

            $userId = (int)Yii::$app->user->id;
            $quoteId = (int)$request->post('quote_id');
            $decision = (int)$request->post('decision');

            if (!$quoteId) {
                return ['ok' => false, 'error' => 'quote_id missing'];
            }

            if (!in_array($decision, [0, 1, 2], true)) {
                return ['ok' => false, 'error' => 'Invalid decision'];
            }

            $quote = Quotes::findOne($quoteId);
            if (!$quote) {
                return ['ok' => false, 'error' => 'Quote not found'];
            }

            $vote = Votes::findOne(['user_id' => $userId, 'quote_id' => $quoteId]) ?? new Votes();
            $vote->user_id = $userId;
            $vote->quote_id = $quoteId;
            $vote->decision = $decision;
            $vote->created_at = time();

            if (!$vote->save()) {
                return ['ok' => false, 'error' => $vote->getErrors()];
            }

            return ['ok' => true];

        } catch (\Throwable $e) {
            Yii::error($e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);

            // В DEV можна тимчасово вертати message, щоб швидко пофіксити:
            return [
                'ok' => false,
                'error' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

}
