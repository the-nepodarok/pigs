<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%overseer}}`.
 */
class m221123_102254_create_overseer_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%overseer}}', [
            'id' => $this->primaryKey(),
            'overseer_name' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%overseer}}');
    }
}
