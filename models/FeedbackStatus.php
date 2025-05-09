<?php

namespace app\models;

use yii\db\ActiveRecord;

class FeedbackStatus extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'feedback_statuses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['value', 'code'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'value' => 'Значение',
            'code' => 'Код',
        ];
    }

    /**
     * {@inheritdoc}
     * @return FeedbackStatusQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FeedbackStatusQuery(get_called_class());
    }
}