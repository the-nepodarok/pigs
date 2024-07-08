<?php

namespace app\models;

use finfo;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "photos".
 *
 * @property int $id
 * @property string $image
 * @property int|null $article_id
 * @property int|null $pig_id
 * @property int|null $turn_in_id
 *
 * @property Article $article
 * @property Pig $pig
 * @property TurnIn $turnIn
 */
class Photo extends \yii\db\ActiveRecord
{

    const DEFAULT_UPLOAD_DIRECTORY = 'img';
    const DEFAULT_FILENAME_PREFIX = 'domik-';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'photos';
    }

    /**
     * Загрузка картинки по веб-адресу или из base64
     * @throws \Exception
     */
    public static function uploadFromBase64(string $src): Photo
    {
        $b64 = explode('base64,', $src)[1];
        $imageData = base64_decode($b64);
        $mime = getimagesizefromstring($imageData)['mime'];

        if (!str_starts_with($mime, 'image')) {
            throw new \Exception('Неверный формат файла');
        }

        $extension = FileHelper::getExtensionByMimeType($mime);
        $filename = uniqid('domik-article-') . ".$extension";
        $fullpath = Yii::getAlias('@webroot') . "/img/$filename";
        file_put_contents($fullpath, $imageData);

        $photo = new self();
        $photo->image = $filename;
        $photo->save();

        return $photo;
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['image'], 'required'],
            [['image'], 'string'],
            [['article_id', 'pig_id', 'turn_in_id'], 'integer'],
            [['image'], 'unique'],
            [['turn_in_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pig::class, 'targetAttribute' => ['turn_in_id' => 'id']],
            [['pig_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pig::class, 'targetAttribute' => ['pig_id' => 'id']],
            [['article_id'], 'exist', 'skipOnError' => true, 'targetClass' => Article::class, 'targetAttribute' => ['article_id' => 'id']],
            [['image'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'image' => 'Image',
            'article_id' => 'Article ID',
            'pig_id' => 'Pig ID',
            'turn_in_id' => 'Turn In ID',
        ];
    }

    /**
     * Загрузка фотографии в файловую систему
     * @param UploadedFile $file
     * @return void
     * @throws \Exception
     */
    public function upload(UploadedFile $file, string $path = self::DEFAULT_UPLOAD_DIRECTORY, string $prefix = self::DEFAULT_FILENAME_PREFIX): void
    {
        $name = uniqid($prefix);
        $extension = ".$file->extension";
        if ($file->saveAs(\Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $name . $extension)) {
            $this->image = $name . $extension;
        } else {
            throw new \Exception('Не удалось записать файл');
        }
    }

    /**
     * Gets query for [[Article]].
     *
     * @return \yii\db\ActiveQuery|ArticleQuery
     */
    public function getArticle(): ActiveQuery
    {
        return $this->hasOne(Article::class, ['id' => 'article_id']);
    }

    /**
     * Gets query for [[FoodProduct]].
     *
     * @return ActiveQuery
     */
    public function getFoodProduct(): ActiveQuery
    {
        return $this->hasOne(FoodProduct::class, ['id' => 'food_product_id']);
    }

    /**
     * Gets query for [[Pig]].
     *
     * @return \yii\db\ActiveQuery|PigQuery
     */
    public function getPig(): ActiveQuery
    {
        return $this->hasOne(Pig::class, ['id' => 'pig_id']);
    }

    /**
     * {@inheritdoc}
     * @return PhotoQuery the active query used by this AR class.
     */
    public static function find(): ActiveQuery
    {
        return new PhotoQuery(get_called_class());
    }
}
