<?php

use app\helpers\SlugHelper;
use yii\db\Migration;
use yii\db\Query;

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

        $this->fillSlugs('articles');
        $this->fillSlugs('food_products');
        $this->fillSlugs('food_categories', 'value');

        $this->createIndex('idx-articles-slug', 'articles', 'slug', true);
        $this->createIndex('idx-food-products-slug', 'food_products', 'slug', true);
        $this->createIndex('idx-food-categories-slug', 'food_categories', 'slug', true);
    }

    /**
     * @return void
     */
    public function safeDown(): void
    {
        $this->dropIndex('idx-food-categories-slug', 'food_categories');
        $this->dropIndex('idx-food-products-slug', 'food_products');
        $this->dropIndex('idx-articles-slug', 'articles');
        $this->dropColumn('food_categories', 'slug');
        $this->dropColumn('food_products', 'slug');
        $this->dropColumn('articles', 'slug');
    }

    /**
     * @param string $tableName
     * @param string $sourceColumn
     * @return void
     */
    private function fillSlugs(string $tableName, string $sourceColumn = 'title'): void
    {
        $rows = (new Query())
            ->select(['id', $sourceColumn])
            ->from($tableName)
            ->all();

        foreach ($rows as $row) {
            $slug = SlugHelper::unique($tableName, $row[$sourceColumn], $row['id']);
            $this->update($tableName, ['slug' => $slug], ['id' => $row['id']]);
        }
    }
}
