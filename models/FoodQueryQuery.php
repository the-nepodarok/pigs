<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[FoodQuery]].
 *
 * @see FoodQuery
 */
class FoodQueryQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return FoodQuery[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return FoodQuery|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
