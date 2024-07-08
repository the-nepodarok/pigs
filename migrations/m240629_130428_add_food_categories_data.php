<?php

use yii\db\Migration;

/**
 * Class m240629_130428_add_food_categories_data
 */
class m240629_130428_add_food_categories_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('food_categories',
            [
            'id', 'value',
            ],
            [
                ['1', 'Овощи'],
                ['2', 'Фрукты'],
                ['3', 'Ягоды'],
                ['4', 'Веточный корм'],
                ['5', 'Дикорастущие растения'],
                ['6', 'Садовый растения'],
                ['7', 'Семена'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
//        echo "m240629_130428_add_food_categories_data cannot be reverted.\n";

        $this->truncateTable('food_categories');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240629_130428_add_food_categories_data cannot be reverted.\n";

        return false;
    }
    */
}
