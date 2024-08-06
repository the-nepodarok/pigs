<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tags".
 *
 * @property int $id
 * @property string $tag_value
 */
class Tag extends ActiveRecord
{
    public static function tableName()
    {
        return 'tags';
    }

    public function rules()
    {
        return [
            [['tag_value'], 'string'],
            ['tag_value', 'unique'],
            [['tag_value'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tag_value' => 'Хэштег',
        ];
    }

    public function getArticles(): ActiveQuery
    {
        return $this->hasMany(Article::class, ['id' => 'article_id'])->viaTable('article_tag', ['tag_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return TagQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TagQuery(get_called_class());
    }
}