<?php

namespace app\modules\education\models;

use Yii;
use app\modules\education\models\Sources;
use app\modules\education\models\Quotes;

/**
 * This is the model class for table "votes".
 *
 * @property int $id
 * @property int $user_id
 * @property int $quote_id
 * @property int $decision 1=like, 0=dislike, 2=later
 * @property int|null $created_at
 * @property int|null $created_user
 * @property int|null $updated_at
 * @property int|null $updated_user
 */
class Votes extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'votes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'created_user', 'updated_at', 'updated_user'], 'default', 'value' => null],
            [['user_id', 'quote_id', 'decision'], 'required'],
            [['user_id', 'quote_id', 'decision', 'created_at', 'created_user', 'updated_at', 'updated_user'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'quote_id' => 'Quote ID',
            'decision' => 'Decision',
            'created_at' => 'Created At',
            'created_user' => 'Created User',
            'updated_at' => 'Updated At',
            'updated_user' => 'Updated User',
        ];
    }

    public function getQuote()
    {
        return $this->hasOne(Quotes::class, ['id' => 'quote_id']);
    }
}
