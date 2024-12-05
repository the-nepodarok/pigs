<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%articles}}`.
 */
class m241205_160812_add_meta_columns_to_articles_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('articles', 'meta_title', $this->text());
        $this->addColumn('articles', 'meta_description', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('articles', 'meta_title');
        $this->dropColumn('articles', 'meta_description');
    }
}
