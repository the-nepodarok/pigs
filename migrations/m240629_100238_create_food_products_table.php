<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%food_products}}`.
 */
class m240629_100238_create_food_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%food_products}}', [
            'id' => $this->primaryKey(),
            'title' => $this->text()->notNull(),
            'description' => $this->text()->null(),
            'category_id' => $this->integer()->notNull(),
            'synonyms' => $this->text()->null(),
            'image' => $this->text()->null(),
            'FOREIGN KEY (category_id) REFERENCES food_categories(id)'
        ]);

        // creates index for column `title`
        $this->createIndex(
            'idx-food-title',
            'food_products',
            'title'
        );

        // creates index for column `synonyms`
        $this->createIndex(
            'idx-food-synonyms',
            'food_products',
            'synonyms'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('PRAGMA foreign_keys = OFF');
        $this->dropTable('{{%food_products}}');
    }
}
