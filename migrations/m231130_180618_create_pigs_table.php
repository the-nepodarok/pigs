<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%pigs}}`.
 */
class m231130_180618_create_pigs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%pigs}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull(),
            'age' => $this->text(),
            'description' => $this->text()->defaultExpression('NULL'),
            'datetime' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'status_id' => $this->integer()->notNull()->defaultValue(1),
            'FOREIGN KEY (status_id) REFERENCES status(id)'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('PRAGMA foreign_keys = OFF');
        $this->dropTable('{{%pigs}}');
    }
}
