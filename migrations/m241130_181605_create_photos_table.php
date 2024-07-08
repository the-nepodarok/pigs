<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%photos}}`.
 */
class m241130_181605_create_photos_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%photos}}', [
            'id' => $this->primaryKey(),
            'image' => $this->text()->unique()->notNull(),
            'article_id' => $this->integer(),
            'pig_id' => $this->integer(),
            'turn_in_id' => $this->integer(),
            'food_product_id' => $this->integer(),
            'FOREIGN KEY (article_id) REFERENCES articles(id) ON UPDATE CASCADE',
            'FOREIGN KEY (pig_id) REFERENCES pigs(id) ON UPDATE CASCADE',
            'FOREIGN KEY (turn_in_id) REFERENCES turn_in(id)',
            'FOREIGN KEY (food_product_id) REFERENCES food_products(id)',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('PRAGMA foreign_keys = OFF');
        $this->dropTable('{{%photos}}');
    }
}
