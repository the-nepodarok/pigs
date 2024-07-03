<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%food_queries}}`.
 */
class m240629_152652_create_food_queries_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%food_queries}}', [
            'id' => $this->primaryKey(),
            'value' => $this->text(),
            'failed' => $this->boolean()->defaultValue(false),
            'count' => $this->integer(),
            'updated_at' => $this->dateTime(),
         ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%food_queries}}');
    }
}
