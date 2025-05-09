<?php

use yii\db\Migration;

class m250507_111209_fill_feedback_statuses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('feedback_statuses', ['id', 'value'], [
            [1, 'none'],
            [2, 'insufficient'],
            [3, 'good'],
            [4, 'mostly_bad'],
            [5, 'bad'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250507_111209_fill_feedback_statuses_table cannot be reverted.\n";

        return false;
    }
}
