<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%articles}}`.
 */
class m231130_181555_create_articles_table extends Migration
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
            'FOREIGN KEY (type_id) REFERENCES types(id)'
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
