<?php

namespace app\models;

use yii\db\ActiveQuery;
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
            [['address', 'title'], 'string'],
            [['latitude', 'longitude'], 'double'],
            [['address', 'title', 'latitude', 'longitude', 'feedback_status_id'], 'safe'],
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

    public function extraFields(): array
    {
        return ['vets'];
    }

    /**
     * {@inheritdoc}
     * @return ClinicQuery the active query used by this AR class.
     */
    public static function find(): ClinicQuery
    {
        return new ClinicQuery(get_called_class());
    }

    /**
     * Gets query for [[Photos]].
     *
     * @return ActiveQuery|ClinicQuery
     */
    public function getVets(): ActiveQuery|ClinicQuery
    {
        return $this->hasMany(Vet::class, ['clinic_id' => 'id']);
    }
}