<?php

use app\helpers\StringHelper;
use app\models\Article;
use app\models\FoodCategory;
use app\models\FoodProduct;
use app\models\ModelWithSlug;
use yii\db\Migration;

class m260619_000001_add_slug_to_articles_and_food_products extends Migration
{
    /**
     * @return void
     */
    public function safeUp(): void
    {
        $this->addColumn('articles', 'slug', $this->string()->null());
        $this->addColumn('food_products', 'slug', $this->string()->null());
        $this->addColumn('food_categories', 'slug', $this->string()->null());

        $this->fillSlugs(Article::class);
        $this->fillSlugs(FoodProduct::class);
        $this->fillSlugs(FoodCategory::class, 'value');

        $this->createIndex('idx-articles-slug', 'articles', 'slug', true);
        $this->createIndex('idx-food-products-slug', 'food_products', 'slug', true);
        $this->createIndex('idx-food-categories-slug', 'food_categories', 'slug', true);
    }

    /**
     * @return void
     */
    public function safeDown(): void
    {
        // no reverting
    }

    /**
     * @param class-string<ModelWithSlug> $modelClass
     * @param string $sourceColumn
     * @return void
     */
    private function fillSlugs(string $modelClass, string $sourceColumn = 'title'): void
    {
        $models = $modelClass::find()
            ->select(['id', $sourceColumn])
            ->all();

        foreach ($models as $model) {
            $sourceValue = $model->getAttribute($sourceColumn);
            $baseSlug = StringHelper::make_slug($sourceValue);
            $slug = $model->formatUniqueSlug($baseSlug);

            $this->update($modelClass::tableName(), ['slug' => $slug], ['id' => $model->id]);
        }
    }
}
