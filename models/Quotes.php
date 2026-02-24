<?php

namespace app\modules\education\models;

use Yii;
use app\modules\education\models\Sources;
use app\modules\education\models\Votes;

/**
 * This is the model class for table "quotes".
 *
 * @property int $id
 * @property int $source_id
 * @property string $topic
 * @property string $exact_quote_en
 * @property string $translation_uk
 * @property string|null $page_or_section
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_user
 * @property int|null $updated_user
 */
class Quotes extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'quotes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['page_or_section', 'created_at', 'updated_at', 'created_user', 'updated_user'], 'default', 'value' => null],
            [['source_id', 'topic', 'exact_quote_en', 'translation_uk'], 'required'],
            [['source_id', 'created_at', 'updated_at', 'created_user', 'updated_user'], 'integer'],
            [['exact_quote_en', 'translation_uk'], 'string'],
            [['topic'], 'string', 'max' => 32],
            [['page_or_section'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'source_id' => 'Source ID',
            'topic' => 'Тема',
            'exact_quote_en' => 'Exact Quote En',
            'translation_uk' => 'Translation Uk',
            'page_or_section' => 'Сторінка або розділ',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_user' => 'Created User',
            'updated_user' => 'Updated User',
        ];
    }


    public function getSource()
    {
        return $this->hasOne(Sources::class, ['id' => 'source_id']);
    }
}
