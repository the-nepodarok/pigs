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
            'graduated' => $this->boolean()->defaultValue(false),
            'datetime' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%pigs}}');
    }
}
