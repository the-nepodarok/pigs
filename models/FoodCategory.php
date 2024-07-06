<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "food_categories".
 *
 * @property int $id
 * @property string|null $value
 *
 * @property FoodProduct[] $foodProducts
 */
class FoodCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'food_categories';
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
     * Gets query for [[FoodProducts]].
     *
     * @return \yii\db\ActiveQuery|FoodProductQuery
     */
    public function getFoodProducts(): \yii\db\ActiveQuery|FoodProductQuery
    {
        return $this->hasMany(FoodProduct::class, ['category_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return FoodCategoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FoodCategoryQuery(get_called_class());
    }
}
