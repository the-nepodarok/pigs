<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%feedback_statuses}}`.
 */
class m250506_100400_create_feedback_statuses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%feedback_statuses}}', [
            'id' => $this->primaryKey(),
            'value' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%feedback_statuses}}');
    }
}
