<?php

namespace app\modules\education\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\db\Transaction;
use app\modules\education\models\ImportQuotesForm;
use app\modules\education\models\Sources;
use app\modules\education\models\Quotes;

class ImportController extends Controller
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


    public function actionIndex(){
        echo "Це сторінка імпорту цитат. Використовуй /import/quotes для форми імпорту.";
        die;
    }
    public function actionQuotes()
    {
        $model = new ImportQuotesForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $decoded = $model->decode();
            if (isset($decoded['error'])) {
                Yii::$app->session->setFlash('error', $decoded['error']);
                return $this->render('quotes', ['model' => $model]);
            }

            $data = $decoded['data'];
            $db = Yii::$app->db;

            $tx = $db->beginTransaction(Transaction::SERIALIZABLE);
            try {
                $now = time();

                $source = new Sources();
                $source->section = (string)$data['section'];
                $source->url = (string)$data['source']['url'];
                $source->title = isset($data['source']['title']) ? (string)$data['source']['title'] : null;
                $source->year = isset($data['source']['year']) && $data['source']['year'] !== null ? (int)$data['source']['year'] : null;
                $source->authors_json = json_encode(
                    $data['source']['authors'] ?? [],
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );

                $source->raw_json = json_encode(
                    $data,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
                // $source->created_at = $now;
                // $source->updated_at = $now;

                if (!$source->save()) {
                    throw new \RuntimeException('Не вдалося зберегти source: ' . json_encode($source->errors, JSON_UNESCAPED_UNICODE));
                }

                $inserted = 0;
                foreach ($data['quotes'] as $q) {
                    // мінімальна валідація структури
                    if (empty($q['topic']) || empty($q['exact_quote_en']) || empty($q['translation_uk'])) {
                        continue; // або throw, якщо хочеш строго
                    }

                    $quote = new Quotes();
                    $quote->source_id = $source->id;
                    $quote->topic = (string)$q['topic'];
                    $quote->exact_quote_en = (string)$q['exact_quote_en'];
                    $quote->translation_uk = (string)$q['translation_uk'];
                    $quote->page_or_section = isset($q['page_or_section']) ? (string)$q['page_or_section'] : null;
                    // $quote->created_at = $now;
                    // $quote->updated_at = $now;

                    if (!$quote->save()) {
                        throw new \RuntimeException('Не вдалося зберегти quote: ' . json_encode($quote->errors, JSON_UNESCAPED_UNICODE));
                    }

                    $inserted++;
                }

                $tx->commit();

                Yii::$app->session->setFlash('success', "Імпортовано: джерело #{$source->id}, тез/цитат: {$inserted}");
                return $this->redirect(['import/quotes']); // або на список
            } catch (\Throwable $e) {
                $tx->rollBack();
                Yii::$app->session->setFlash('error', 'Помилка імпорту: ' . $e->getMessage());
            }
        }

        return $this->render('quotes', ['model' => $model]);
    }
}
