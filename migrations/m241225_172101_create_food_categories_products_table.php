<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%food_categories_products}}`.
 */
class m241225_172101_create_food_categories_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%food_categories_products}}', [
            'product_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->notNull(),
            'FOREIGN KEY (product_id) REFERENCES food_products(id)',
            'FOREIGN KEY (category_id) REFERENCES food_categories(id)',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->execute('PRAGMA foreign_keys = OFF');
        $this->dropTable('{{%food_categories_products}}');
    }
}
