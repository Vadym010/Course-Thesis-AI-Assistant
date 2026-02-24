<?php

namespace app\modules\education\models;

use Yii;
use app\modules\education\models\Quotes;
use app\modules\education\models\Votes;

/**
 * This is the model class for table "sources".
 *
 * @property int $id
 * @property string $section
 * @property string $url
 * @property string|null $title
 * @property string|null $authors_json
 * @property int|null $year
 * @property string|null $raw_json
 * @property int $created_at
 * @property int|null $created_user
 * @property int $updated_at
 * @property int|null $updated_user
 */
class Sources extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sources';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'authors_json', 'year', 'raw_json', 'created_user', 'updated_user'], 'default', 'value' => null],
            [['section', 'url'], 'required'],
            [['url', 'authors_json', 'raw_json'], 'string'],
            [['year', 'created_at', 'created_user', 'updated_at', 'updated_user'], 'integer'],
            [['section'], 'string', 'max' => 255],
            [['title'], 'string', 'max' => 512],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'section' => 'Section',
            'url' => 'Url',
            'title' => 'Title',
            'authors_json' => 'Authors Json',
            'year' => 'Year',
            'raw_json' => 'Raw Json',
            'created_at' => 'Created At',
            'created_user' => 'Created User',
            'updated_at' => 'Updated At',
            'updated_user' => 'Updated User',
        ];
    }
        public function getQuotes()
    {
        return $this->hasMany(Quotes::class, ['source_id' => 'id']);
    }

}
