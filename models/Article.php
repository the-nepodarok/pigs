<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "articles".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $text
 * @property string|null $main_photo
 * @property string|null $datetime
 * @property int $type_id
 *
 * @property Photo[] $photos
 * @property Type $type
 */
class Article extends EntityWithPhotos
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'articles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        $rules = parent::rules();

        return array_merge($rules, [
            [['type_id', 'title', 'text'], 'required', 'message' => 'Поле «{attribute}» обязательно к заполнению'],
            [['title', 'text', 'author', 'origin_link', 'main_photo'], 'string'],
            [['datetime', 'title', 'text', 'author', 'origin_link', 'main_photo'], 'safe'],
            [['type_id'], 'integer'],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => Type::class, 'targetAttribute' => ['type_id' => 'id']],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'title' => 'Заголовок',
            'text' => 'Текст',
            'main_photo' => 'Main Photo',
            'datetime' => 'Datetime',
            'type_id' => 'Type ID',
            'files' => 'Фото',
            'author' => 'Автор статьи',
            'origin_link' => 'Ссылка на источник',
        ];
    }

    /**
     * Gets query for [[Photos]].
     *
     * @return \yii\db\ActiveQuery|PhotoQuery
     */
    public function getPhotos(): ActiveQuery
    {
        return $this->hasMany(Photo::class, ['article_id' => 'id']);
    }

    /**
     * Gets query for [[Type]].
     *
     * @return \yii\db\ActiveQuery|TypeQuery
     */
    public function getType(): ActiveQuery
    {
        return $this->hasOne(Type::class, ['id' => 'type_id']);
    }

    /**
     * {@inheritdoc}
     * @return ArticleQuery the active query used by this AR class.
     */
    public static function find(): ActiveQuery
    {
        return new ArticleQuery(get_called_class());
    }
}
