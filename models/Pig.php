<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "pigs".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $sex
 * @property string|null $age
 * @property int|null $status_id
 * @property int|null $city_id
 * @property int|null $overseer_id
 * @property string|null $datetime
 * @property string|null $graduation_date
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
            [['city_id'], 'required', 'message' => 'Выберите город'],
            [['sex'], 'required', 'message' => 'Укажите пол'],
            [['name', 'description', 'age', 'main_photo', 'sex'], 'string'],
            [['status_id', 'city_id', 'overseer_id'], 'integer'],
            [['id'], 'unique', 'message' => 'ID уже занят'],
            [['id', 'name', 'description', 'age', 'main_photo', 'files', 'city_id', 'overseer_id', 'sex'], 'safe'],
        ]);
    }

    public function fields(): array
    {
        $fields = parent::fields();

        // Скрытие полей с id города и куратора
        unset($fields['city_id'], $fields['overseer_id']);
        return $fields;
    }


    public function extraFields()
    {
        // Добавление полей с фотографиями, куратором и городом
        return ['photos', 'overseer', 'city', 'sex', 'status'];
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
            'overseer_id' => 'Куратор',
            'sex' => 'Пол',
            'city_id' => 'Город',
            'datetime' => 'Дата создания',
            'graduation_date' => 'Дата выпуска',
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
     * Gets query for [[City]].
     *
     * @return \yii\db\ActiveQuery|CityQuery
     */
    public function getCity(): ActiveQuery
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * Gets query for [[Overseer]].
     *
     * @return \yii\db\ActiveQuery|OverseerQuery
     */
    public function getOverseer(): ActiveQuery
    {
        return $this->hasOne(Overseer::class, ['id' => 'overseer_id']);
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
