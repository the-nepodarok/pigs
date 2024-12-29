<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\UploadedFile;

/**
 * This is the model class for table "food_products".
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $image
 * @property array $types
 *
 * @property FoodCategory[] $categories
 * @property UploadedFile $file
 */
class FoodProduct extends EntityWithPhotos
{
    const UPLOAD_DIRECTORY = 'img' . DIRECTORY_SEPARATOR . 'info';
    const FILENAME_PREFIX = 'domik-info-';

    public $file = null;
    public $types = [];

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
            [['title', 'types'], 'required', 'message' => '{attribute} не может быть пустым'],
            [['title', 'description', 'synonyms', 'synonyms'], 'string'],
            ['is_banned', 'boolean'],
            [['title', 'synonyms', 'is_banned', 'types'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'title' => 'Наименование',
            'description' => 'Описание',
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
     * @return string[]
     */
    public function extraFields(): array
    {
        return ['categories'];
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
    public function getCategories(): \yii\db\ActiveQuery|FoodCategoryQuery
    {
        return $this->hasMany(FoodCategory::class, ['id' => 'category_id'])->viaTable('food_categories_products', ['product_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return FoodProductQuery the active query used by this AR class.
     */
    public static function find(): FoodProductQuery
    {
        return new FoodProductQuery(get_called_class());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function handleCategories(): void
    {
        $this->unlinkAllCategories();

        foreach ($this->types as $categoryId) {
            $this->link('categories', FoodCategory::findOne(['id' => $categoryId]));
        }
    }

    /**
     * @throws Exception
     * @throws StaleObjectException
     */
    public function unlinkAllCategories(): void
    {
        foreach ($this->categories as $category) {
            $this->unlink('categories', $category, true);
        }
    }
}
