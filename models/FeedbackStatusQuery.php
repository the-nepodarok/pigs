<?php

namespace app\models;

class FeedbackStatusQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return FeedbackStatus[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return FeedbackStatus|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}