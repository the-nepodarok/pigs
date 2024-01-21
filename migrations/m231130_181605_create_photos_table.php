<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%photos}}`.
 */
class m231130_181605_create_photos_table extends Migration
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
            'FOREIGN KEY (article_id) REFERENCES articles(id)',
            'FOREIGN KEY (pig_id) REFERENCES pigs(id)',
            'FOREIGN KEY (turn_in_id) REFERENCES turn_in(id)',
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
