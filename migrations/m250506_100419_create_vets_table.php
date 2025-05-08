<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vets}}`.
 */
class m250506_100419_create_vets_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%vets}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'clinic_id' => $this->integer(),
            'feedback_status_id' => $this->integer(),
            'FOREIGN KEY (clinic_id) REFERENCES clinics(id) ON UPDATE CASCADE',
            'FOREIGN KEY (feedback_status_id) REFERENCES feedback_statuses(id) ON UPDATE CASCADE',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->execute('PRAGMA foreign_keys = OFF');
        $this->dropTable('{{%vets}}');
    }
}
