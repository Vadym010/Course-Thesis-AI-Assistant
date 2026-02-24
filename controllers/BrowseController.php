<?php

namespace app\modules\education\controllers;

use Yii;
use yii\web\Controller;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use app\modules\education\models\Sources;
use app\modules\education\models\Quotes;
use app\modules\education\models\Votes;

class BrowseController extends Controller
{
    public function behaviors(): array
    {

        $this->layout = \app\helpers\UniversalHelper::getLayout();

        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['sources', 'source-view', 'my-votes', 'source-stats'],
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    /** Список джерел */
    public function actionSources()
    {
        $provider = new ActiveDataProvider([
            'query' => Sources::find()->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('sources', [
            'provider' => $provider,
        ]);
    }

    /** Перегляд одного джерела + список цитат */
    public function actionSourceView($id)
    {
        $source = Sources::findOne((int)$id);
        if (!$source) {
            throw new NotFoundHttpException('Source not found');
        }

        $quotesProvider = new ActiveDataProvider([
            'query' =>  Quotes::find()->where(['source_id' => $source->id])->orderBy(['id' => SORT_ASC]),
            'pagination' => ['pageSize' => 50],
        ]);

        // швидко підтягнемо рішення поточного юзера по цим quote
        $userId = (int)Yii::$app->user->id;
        $votes = Votes::find()
            ->select(['quote_id', 'decision'])
            ->where(['user_id' => $userId])
            ->andWhere(['quote_id' => Quotes::find()->select('id')->where(['source_id' => $source->id])])
            ->asArray()
            ->all();

        $decisionMap = [];
        foreach ($votes as $v) {
            $decisionMap[(int)$v['quote_id']] = (int)$v['decision'];
        }

        return $this->render('source-view', [
            'source' => $source,
            'quotesProvider' => $quotesProvider,
            'decisionMap' => $decisionMap,
        ]);
    }

    /** Мої відповіді (всі голоси поточного юзера) */
    public function actionMyVotes($decision = null)
    {
        $userId = (int)Yii::$app->user->id;

        // decision: 1=like, 0=dislike, 2=later, null=all (але ми будемо робити 3 вкладки)
        if ($decision !== null) {
            $decision = (int)$decision;
            if (!in_array($decision, [0,1,2], true)) {
                $decision = null;
            }
        }

        $query = Votes::find()->alias('v')
            ->innerJoin('{{%quotes}} q', 'q.id = v.quote_id')
            ->innerJoin('{{%sources}} s', 's.id = q.source_id')
            ->where(['v.user_id' => $userId]);

        if ($decision !== null) {
            $query->andWhere(['v.decision' => $decision]);
        }

        $provider = new ActiveDataProvider([
            'query' => $query->orderBy(['v.created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 30],
        ]);

        // Лічильники для вкладок
        $countsRows = (new Query())
            ->from('{{%votes}}')
            ->where(['user_id' => $userId])
            ->groupBy(['decision'])
            ->select(['decision', 'cnt' => 'COUNT(*)'])
            ->all();

        $counts = [0 => 0, 1 => 0, 2 => 0];
        foreach ($countsRows as $r) {
            $counts[(int)$r['decision']] = (int)$r['cnt'];
        }

        return $this->render('my-votes', [
            'provider' => $provider,
            'activeDecision' => $decision, // 0/1/2/null
            'counts' => $counts,
        ]);
    }


    /** (Опційно) Статистика по джерелу: скільки лайків/дизлайків/потім по кожній цитаті */
    public function actionSourceStats($id)
    {
        $source = Sources::findOne((int)$id);
        if (!$source) {
            throw new NotFoundHttpException('Source not found');
        }

        $rows = (new \yii\db\Query())
            ->from(['q' => '{{%quotes}}'])
            ->leftJoin(['v' => '{{%votes}}'], 'v.quote_id = q.id')
            ->where(['q.source_id' => $source->id])
            ->groupBy(['q.id'])
            ->select([
                'q.id',
                'q.topic',
                'q.page_or_section',
                'q.exact_quote_en',
                'q.translation_uk',
                'likes' => "SUM(CASE WHEN v.decision=1 THEN 1 ELSE 0 END)",
                'dislikes' => "SUM(CASE WHEN v.decision=0 THEN 1 ELSE 0 END)",
                'later' => "SUM(CASE WHEN v.decision=2 THEN 1 ELSE 0 END)",
                'total' => "COUNT(v.id)",
            ])
            ->orderBy(['q.id' => SORT_ASC])
            ->all();

        return $this->render('source-stats', [
            'source' => $source,
            'rows' => $rows,
        ]);
    }
}
