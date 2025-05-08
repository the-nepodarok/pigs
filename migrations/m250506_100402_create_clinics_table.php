<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%clinics}}`.
 */
class m250506_100402_create_clinics_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%clinics}}', [
            'id' => $this->primaryKey(),
            'address' => $this->string()->notNull(),
            'latitude' => $this->float(),
            'longitude' => $this->float(),
            'feedback_status_id' => $this->integer(),
            'FOREIGN KEY (feedback_status_id) REFERENCES feedback_statuses(id) ON UPDATE CASCADE',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->execute('PRAGMA foreign_keys = OFF');
        $this->dropTable('{{%clinics}}');
    }
}
