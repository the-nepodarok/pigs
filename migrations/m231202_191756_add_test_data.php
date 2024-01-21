<?php

use yii\db\Migration;

/**
 * Class m231202_191756_add_test_data
 */
class m231202_191756_add_test_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('pigs', [
            'id' => 1,
            'name' => 'Кьянти',
            'age' => '3 с половиной годика',
            'description' => 'Красотулькин',
        ]);

        $this->insert('pigs', [
            'id' => 2,
            'name' => 'Финик',
            'age' => '???',
            'description' => 'Солнечный Принц',
        ]);

        $this->insert('photos', [
            'image' => 'dYM-2AFAEWU',
            'pig_id' => 1,
        ]);

        $this->insert('photos', [
            'image' => 'CRdxWOEgCZk',
            'pig_id' => 1,
        ]);

        $this->insert('photos', [
            'image' => 'lkw5jkldf',
            'pig_id' => 1,
        ]);

        $this->insert('photos', [
            'image' => 'z1Z5GXHyrhY',
            'pig_id' => 1,
        ]);

        $this->insert('types', [
            'id' => 1,
            'name' => 'Статья'
        ]);

        $this->insert('types', [
            'id' => 2,
            'name' => 'Новость'
        ]);

        $this->insert('articles', [
            'title' => 'Как вытирать носиков',
            'text' => 'Берём салфетку-микрофибру, подносим к свинку и аккуратно...',
            'type_id' => 1
        ]);

        $this->insert('articles', [
            'title' => 'В Домике новый гость',
            'text' => 'Нашли его в посёлке [...], залез на дерево и кричал [...]',
            'type_id' => 2
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231202_191756_add_test_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231202_191756_add_test_data cannot be reverted.\n";

        return false;
    }
    */
}
