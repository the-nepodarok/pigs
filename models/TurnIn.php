<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "turn_in".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $age
 * @property string $description
 * @property string|null $owner_name
 * @property int|null $owner_number
 * @property string|null $datetime
 * @property Photo[] $photos
 */
class TurnIn extends EntityWithPhotos
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'turn_in';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        $rules = parent::rules();

        return array_merge($rules, [
            [['description', 'owner_number'], 'required', 'message' => 'Поле обязательно к заполнению'],
            [['name', 'age', 'description', 'owner_name'], 'string'],
            [['owner_number'], 'integer', 'message' => 'Введите действительный номер телефона'],
            [['datetime'], 'safe'],
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
            'age' => 'Возраст свинки',
            'description' => 'Состояние здоровья',
            'owner_name' => 'Имя владельца',
            'owner_number' => 'Данные для связи',
            'datetime' => 'Datetime',
            'files' => 'Фото',
            'main_photo' => 'Фото'
        ];
    }

    /**
     * Gets query for [[Photos]].
     *
     * @return \yii\db\ActiveQuery|PhotoQuery
     */
    public function getPhotos(): ActiveQuery
    {
        return $this->hasMany(Photo::class, ['turn_in_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return TurnInQuery the active query used by this AR class.
     */
    public static function find(): ActiveQuery
    {
        return new TurnInQuery(get_called_class());
    }
}
