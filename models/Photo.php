<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
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
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'photos';
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
    public function upload(UploadedFile $file): void
    {
        $name = uniqid('domik-');
        if ($file->saveAs('@webroot/img' . DIRECTORY_SEPARATOR . $name . '.jpg')) {
            $this->image = $name;
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
     * Gets query for [[Pig]].
     *
     * @return \yii\db\ActiveQuery|PigQuery
     */
    public function getPig(): ActiveQuery
    {
        return $this->hasOne(Pig::class, ['id' => 'pig_id']);
    }

    /**
     * Gets query for [[TurnIn]].
     *
     * @return \yii\db\ActiveQuery|PigQuery
     */
    public function getTurnIn(): ActiveQuery
    {
        return $this->hasOne(TurnIn::class, ['id' => 'turn_in_id']);
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
