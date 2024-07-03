<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%food_categories}}`.
 */
class m240629_091259_create_food_categories_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%food_categories}}', [
            'id' => $this->primaryKey(),
            'value' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%food_categories}}');
    }
}
