<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "pigs".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $status_id
 * @property string|null $datetime
 *
 * @property Photo[] $photos
 */
class Pig extends EntityWithPhotos
{
    public static function tableName(): string
    {
        return 'pigs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        $rules = parent::rules();

        return array_merge($rules, [
            [['name', 'description'], 'required', 'message' => '{attribute} не должно быть пустым'],
            [['name', 'description', 'age', 'main_photo'], 'string'],
            [['status_id'], 'integer'],
            [['name', 'description', 'age', 'main_photo', 'files'], 'safe'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Имя свинки',
            'description' => 'Описание',
            'status_id' => 'Нашёл дом',
            'datetime' => 'Дата создания',
            'main_photo' => 'Фото',
            'files' => 'Фото',
        ];
    }

    /**
     * Gets query for [[Photos]].
     *
     * @return \yii\db\ActiveQuery|PhotoQuery
     */
    public function getPhotos(): ActiveQuery
    {
        return $this->hasMany(Photo::class, ['pig_id' => 'id']);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery|StatusQuery
     */
    public function getStatus(): ActiveQuery
    {
        return $this->hasOne(Status::class, ['id' => 'status_id']);
    }

    /**
     * {@inheritdoc}
     * @return PigQuery the active query used by this AR class.
     */
    public static function find(): ActiveQuery
    {
        return new PigQuery(get_called_class());
    }
}
