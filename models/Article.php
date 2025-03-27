<?php

namespace app\models;

use app\helpers\StringHelper;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\StaleObjectException;

/**
 * This is the model class for table "articles".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $text
 * @property string|null $main_photo
 * @property string|null $datetime
 * @property int $type_id
 * @property int $cover_id
 *
 * @property Photo[] $photos
 * @property Type $type
 */
class Article extends EntityWithPhotos
{
    public string $hashtags;
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
            [['title', 'text', 'author', 'origin_link', 'main_photo', 'hashtags', 'meta_title', 'meta_description'], 'string'],
            [['datetime', 'title', 'text', 'author', 'origin_link', 'main_photo', 'hashtags'], 'safe'],
            [['type_id', 'cover_id'], 'integer'],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => Type::class, 'targetAttribute' => ['type_id' => 'id']],
            [['cover_id'], 'exist', 'skipOnError' => true, 'targetClass' => Photo::class, 'targetAttribute' => ['cover_id' => 'id']]
        ]);
    }

    public function fields(): array
    {
        $fields = parent::fields();
        $this->main_photo = $this->cover['image'] ?? null;

        return $fields;
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

    public function extraFields()
    {
        return ['tags'];
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
     * Gets query for [[Photo]].
     * @return ActiveQuery
     */
    public function getCover(): ActiveQuery
    {
        return $this->hasOne(Photo::class, ['id' => 'cover_id']);
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

    public function getTags(): ActiveQuery
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])->viaTable('article_tag', ['article_id' => 'id']);
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
                        $src = $photo->image;
                    } catch (\Exception $e) {
                        $this->addError('files', $e->getMessage());
                    }
                } elseif (str_contains($src, 'domik-article-')) {
                    $src = strstr($src, 'domik-article');
                    $photo = Photo::find()->where(['image' => $src])->one();
                }

                if (isset($photo)) {
                    $photos[] = $photo;
                }

                // подмена адреса картинки в тегах на серверный
                $this->text = str_replace($img, '<img src="' . $src . '" alt="">', $this->text);
            }
        }

        return $photos;
    }

    /**
     * @throws Exception
     * @throws StaleObjectException
     * @throws \Exception
     * @throws \Throwable
     */
    public function setArticleCover(): void
    {
        if ($this->cover) {
            $oldCover = $this->getCover()->one();
            $filename = \Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . static::UPLOAD_DIRECTORY . DIRECTORY_SEPARATOR . $oldCover->image;
            $this->unlink('cover', $oldCover);
            $oldCover->delete();
            unlink($filename);
        }

        $newPhoto = new Photo();
        $newPhoto->upload($this->files[0]);
        $newPhoto->save();
        $this->link('cover', $newPhoto);
    }

    /**
     * @param string $hashtag
     * @return void
     */
    public function attachTag(string $hashtag): void
    {
        $tag = Tag::findOne(['tag_value' => $hashtag]);

        if (!$tag) {
            $tag = new Tag();
            $tag->tag_value = $hashtag;
            $tag->save();
        }

        $this->link('tags', $tag);
    }

    /**
     * @param string $hashtag
     * @return void
     */
    public function detachTag(string $hashtag): void
    {
        $tag = Tag::findOne(['tag_value' => $hashtag]);
        $this->unlink('tags', $tag, true);
    }

    /**
     * @return void
     */
    public function detachAllTags(): void
    {
        foreach ($this->tags as $tag) {
            $this->detachTag($tag->tag_value);
        }
    }
}
