<?php

namespace app\models;

use yii\db\ActiveRecord;

class Vet extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'vets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name'], 'string'],
            [['feedback_status_id'], 'exist', 'skipOnError' => true, 'targetClass' => FeedbackStatus::class, 'targetAttribute' => ['feedback_status_id' => 'id']],
            [['clinic_id'], 'exist', 'skipOnError' => true, 'targetClass' => Clinic::class, 'targetAttribute' => ['clinic_id' => 'id']]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Имя',
        ];
    }

    /**
     * {@inheritdoc}
     * @return VetQuery the active query used by this AR class.
     */
    public static function find(): VetQuery
    {
        return new VetQuery(get_called_class());
    }
}