<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\web\UploadedFile;

/**
 * This is the model class for table "food_products".
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $image
 * @property int $category_id
 *
 * @property FoodCategory $category
 * @property UploadedFile $file
 */
class FoodProduct extends EntityWithPhotos
{
    public $file = null;

    private const DESCRIPTION_FIELDS = ['desc', 'doses', 'allowed', 'restrictions', 'notes'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'food_products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['title', 'unique', 'message' => '{attribute} уже существует!'],
            [['title', 'category_id'], 'required', 'message' => '{attribute} не может быть пустым'],
            [['title', 'description', 'synonyms', 'synonyms'], 'string'],
            [['category_id'], 'integer'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => FoodCategory::class, 'targetAttribute' => ['category_id' => 'id']],
            [['title', 'synonyms'], 'safe'],
            [['file'], 'image', 'maxFiles' => 1, 'maxSize' => 4e+6, 'skipOnEmpty' => true, 'extensions' => ['jpg', 'jpeg'],
                'wrongExtension' => 'Неверный формат файла. Принимаются только картинки с расширением JPG',
                'wrongMimeType' => 'Неверный формат файла. Принимаются только картинки с расширением JPG',
                'tooBig' => 'Файл слишком большой. Максимально допустимый размер: 4MB'
            ],
        ];
    }

    public function beforeSave($insert): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Наименование',
            'description' => 'Описание',
            'category_id' => 'ID категории',
            'synonyms' => 'Синонимы',
        ];
    }

    public function load($data, $formName = null): bool
    {
        $file = UploadedFile::getInstanceByName('file');

        if (!empty($file) && $file->size) {
            $this->file = $file;
        }

        return parent::load($data, $formName);
    }

    public function fields(): array
    {
        $stringArray = preg_split('/\|/', $this->description);

        $fields = parent::fields() + [
            'info' => function () use ($stringArray) {
                return array_combine(self::DESCRIPTION_FIELDS, $stringArray);
            }
        ];

        unset($fields['description']);

        return $fields;
    }

    /**
     * Gets query for [[Photos]].
     *
     * @return ActiveQuery|PhotoQuery
     */
    public function getPhotos(): ActiveQuery|PhotoQuery
    {
        return $this->hasMany(Photo::class, ['food_product_id' => 'id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery|FoodCategoryQuery
     */
    public function getCategory(): \yii\db\ActiveQuery|FoodCategoryQuery
    {
        return $this->hasOne(FoodCategory::class, ['id' => 'category_id']);
    }

    /**
     * {@inheritdoc}
     * @return FoodProductQuery the active query used by this AR class.
     */
    public static function find(): FoodProductQuery
    {
        return new FoodProductQuery(get_called_class());
    }

//    /**
//     * Загрузка фотографии в файловую систему
//     * @param UploadedFile $file
//     * @return void
//     * @throws \Exception
//     */
//    public function uploadImage(UploadedFile $file): void
//    {
//        $name = uniqid('domik-info-');
//        $extension = ".$file->extension";
//        if ($file->saveAs('@webroot/img/info' . DIRECTORY_SEPARATOR . $name . $extension)) {
//            $this->image = $name . $extension;
//        } else {
//            throw new \Exception('Не удалось записать файл');
//        }
//    }
//
//    /**
//     * @param string $filename
//     * @return void
//     */
//    public function unlinkImage(): void
//    {
//        $filename = \Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'info' . DIRECTORY_SEPARATOR . $this->image;
//        unlink($filename);
//    }
}
