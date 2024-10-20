<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%articles}}`.
 */
class m240802_181555_create_articles_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%articles}}', [
            'id' => $this->primaryKey(),
            'title' => $this->text()->notNull(),
            'text' => $this->text()->notNull(),
            'author' => $this->text(),
            'origin_link' => $this->text(),
            'datetime' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'type_id' => $this->integer()->notNull(),
            'cover_id' => $this->integer(),
            'FOREIGN KEY (type_id) REFERENCES types(id)',
            'FOREIGN KEY (cover_id) REFERENCES photos(id)'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('PRAGMA foreign_keys = OFF');
        $this->dropTable('{{%articles}}');
    }
}
