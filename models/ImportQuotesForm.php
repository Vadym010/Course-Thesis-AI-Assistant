<?php

namespace app\modules\education\models;

use yii;

class ImportQuotesForm extends \yii\db\ActiveRecord
{
    public string $json = '';

    public function rules(): array
    {
        return [
            [['json'], 'required'],
            [['json'], 'string'],
        ];
    }

    /**
     * @return array{data: array}|array{error: string}
     */
    public function decode(): array
    {
        $data = json_decode($this->json, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Невалідний JSON: ' . json_last_error_msg()];
        }
        if (!is_array($data)) {
            return ['error' => 'JSON має бути обʼєктом (верхній рівень).'];
        }

        // мінімальні очікувані поля
        if (empty($data['section']) || empty($data['source']['url']) || empty($data['quotes']) || !is_array($data['quotes'])) {
            return ['error' => 'У JSON бракує section / source.url / quotes[].'];
        }

        return ['data' => $data];
    }
}
