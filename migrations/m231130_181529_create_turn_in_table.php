<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%turn_in}}`.
 */
class m231130_181529_create_turn_in_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%turn_in}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text(),
            'age' => $this->text(),
            'description' => $this->text()->notNull(),
            'owner_name' => $this->text(),
            'owner_number' => $this->integer(),
            'datetime' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%turn_in}}');
    }
}
