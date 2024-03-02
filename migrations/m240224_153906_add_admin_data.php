<?php

use yii\db\Migration;

/**
 * Class m240224_153906_add_admin_data
 */
class m240224_153906_add_admin_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('admin', [
            'name' => 'admin',
            'password' => Yii::$app->getSecurity()->generatePasswordHash('finik'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240224_153906_add_admin_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240224_153906_add_admin_data cannot be reverted.\n";

        return false;
    }
    */
}
