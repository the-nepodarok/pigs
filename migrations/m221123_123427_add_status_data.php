<?php

use yii\db\Migration;

/**
 * Class m240224_123427_add_status_data
 */
class m221123_123427_add_status_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('status', [
            'id' => 1,
            'value' => 'pending',
        ]);

        $this->insert('status', [
            'id' => 2,
            'value' => 'graduated',
        ]);

        $this->insert('status', [
            'id' => 3,
            'value' => 'rainbow',
        ]);

        $this->insert('status', [
            'id' => 4,
            'value' => 'taken',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240224_123427_add_status_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240224_123427_add_status_data cannot be reverted.\n";

        return false;
    }
    */
}
