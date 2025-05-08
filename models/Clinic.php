<?php

namespace app\models;

use yii\db\ActiveRecord;

class Clinic extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'clinics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['address'], 'string'],
            [['latitude', 'longitude'], 'double'],
            [['address', 'latitude', 'longitude', 'feedback_status_id'], 'safe'],
            [['feedback_status_id'], 'exist', 'skipOnError' => true, 'targetClass' => FeedbackStatus::class, 'targetAttribute' => ['feedback_status_id' => 'id']]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'address' => 'Адрес',
            'latitude' => 'Широта',
            'longitude' => 'Долгота',
        ];
    }

    /**
     * {@inheritdoc}
     * @return ClinicQuery the active query used by this AR class.
     */
    public static function find(): ClinicQuery
    {
        return new ClinicQuery(get_called_class());
    }
}