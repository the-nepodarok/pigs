<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%pigs}}`.
 */
class m231130_180618_create_pigs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%pigs}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull(),
            'age' => $this->text(),
            'sex' => $this->text()->notNull(),
            'description' => $this->text()->defaultValue('NULL'),
            'datetime' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'graduation_date' => $this->date()->defaultValue('NULL'),
            'status_id' => $this->integer()->notNull()->defaultValue(1),
            'overseer_id' => $this->integer()->null(),
            'city_id' => $this->integer()->notNull()->defaultValue(1),
            'FOREIGN KEY (status_id) REFERENCES status(id)',
            'FOREIGN KEY (overseer_id) REFERENCES overseer(id)',
            'FOREIGN KEY (city_id) REFERENCES city(id)',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('PRAGMA foreign_keys = OFF');
        $this->dropTable('{{%pigs}}');
    }
}
