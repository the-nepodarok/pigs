<?php

use yii\db\Migration;

/**
 * Class m240302_102537_add_city_data
 */
class m221123_122537_add_city_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('city', [
            'id' => 1,
            'city_name' => 'Москва',
        ]);

        $this->insert('city', [
            'id' => 2,
            'city_name' => 'Санкт-Петербург',
        ]);

        $this->insert('city', [
            'id' => 3,
            'city_name' => 'Сочи',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240302_102537_add_city_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240302_102537_add_city_data cannot be reverted.\n";

        return false;
    }
    */
}
