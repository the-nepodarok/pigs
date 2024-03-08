<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "status".
 *
 * @property int $id
 * @property string|null $value
 *
 * @property Pigs[] $pigs
 */
class Status extends \yii\db\ActiveRecord
{
    const AVAILABLE_STATUSES = [1, 5, 6];
    const GRADUATED_STATUSES = [2, 3, 4];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['value'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'value' => 'Value',
        ];
    }

    /**
     * Gets query for [[Pigs]].
     *
     * @return \yii\db\ActiveQuery|PigsQuery
     */
    public function getPigs()
    {
        return $this->hasMany(Pig::class, ['status_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return StatusQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new StatusQuery(get_called_class());
    }
}
