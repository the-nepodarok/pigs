<?php

use yii\db\Migration;

/**
 * Class m240302_102547_add_overseer_data
 */
class m221123_122547_add_overseer_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('overseer', [
            'id' => 1,
            'overseer_name' => 'Юлия, Домик Лисы',
        ]);

        $this->insert('overseer', [
            'id' => 2,
            'overseer_name' => 'Любовь, Домик Лотти',
        ]);

        $this->insert('overseer', [
            'id' => 3,
            'overseer_name' => 'Светлана, Домик Лютика',
        ]);

        $this->insert('overseer', [
            'id' => 4,
            'overseer_name' => 'Таисия, Домик Белки',
        ]);

        $this->insert('overseer', [
            'id' => 5,
            'overseer_name' => 'Лера, Домик Булочки',
        ]);

        $this->insert('overseer', [
            'id' => 6,
            'overseer_name' => 'Марина, Домик Малевича',
        ]);

        $this->insert('overseer', [
            'id' => 7,
            'overseer_name' => 'Маргарита, Домик Брауни',
        ]);

        $this->insert('overseer', [
            'id' => 8,
            'overseer_name' => 'Марина, Домик Луны',
        ]);

        $this->insert('overseer', [
            'id' => 9,
            'overseer_name' => 'Надежда, Домик Элли',
        ]);

        $this->insert('overseer', [
            'id' => 10,
            'overseer_name' => 'Анастасия, Домик Куси',
        ]);

        $this->insert('overseer', [
            'id' => 11,
            'overseer_name' => 'Александра, Домик Ёлки',
        ]);

        $this->insert('overseer', [
            'id' => 12,
            'overseer_name' => 'Анна, Домик Пикселя',
        ]);

        $this->insert('overseer', [
            'id' => 13,
            'overseer_name' => 'Куратор Анастасия Ткаченко',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240302_102547_add_overseer_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240302_102547_add_overseer_data cannot be reverted.\n";

        return false;
    }
    */
}
