<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "overseer".
 *
 * @property int $id
 * @property string|null $overseer_name
 */
class Overseer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'overseer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['overseer_name'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'overseer_name' => 'Куратор',
        ];
    }

    public function getPigs(): ActiveQuery
    {
        return $this->hasMany(Pig::class, ['overseer_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return OverseerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OverseerQuery(get_called_class());
    }
}
