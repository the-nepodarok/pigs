<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "food_queries".
 *
 * @property int $id
 * @property string|null $value
 * @property bool|null $failed
 * @property int|null $count
 * @property string|null $updated_at
 */
class FoodQuery extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'food_queries';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['value'], 'string'],
            [['failed'], 'boolean'],
            [['count'], 'integer'],
            [['updated_at', 'value', 'failed', 'count'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'value' => 'Текст запроса',
            'failed' => 'Успешно',
            'count' => 'Количество запросов',
            'updated_at' => 'Дата последнего запроса',
        ];
    }

    /**
     * {@inheritdoc}
     * @return FoodQueryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FoodQueryQuery(get_called_class());
    }
}
