<?php

namespace app\models;

use app\helpers\StringHelper;
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

    /**
     * Обработка разметки, пришедшей из текстового редактора Quill.js
     * с загрузкой картинок из base64 на сервер
     * @param array $photos
     * @return array Массив фотографий для прикрепления к записи
     */
    public function handleImageMarkup(array $photos): array
    {
        $imgTags = [];
        // все теги с картинками
        preg_match_all('/<img src="[a-z1-9\S\s][^>]+>/', $this->text, $imgTags);

        if (!empty($imgTags)) {
            foreach ($imgTags[0] as $img) {
                // атрибут src
                $src = StringHelper::str_between($img, 'src="', '"');

                if (str_starts_with($src, 'data:image') and str_contains($src, 'base64')) {
                    try {
                        $photo = Photo::uploadFromBase64($src);
                        $photos[] = $photo;
                        $src = $photo->image;
                    } catch (\Exception $e) {
                        $this->addError('files', $e->getMessage());
                    }
                } elseif (str_contains($src, 'domik-article-')) {
                    $src = strstr($src, 'domik-article');
                    $photo = Photo::find($src);
                }

                // подмена адреса картинки в тегах на серверный
                $this->text = str_replace($img, '<img src="' . $src . '" alt="">', $this->text);
            }
        }

        return $photos;
    }
}
