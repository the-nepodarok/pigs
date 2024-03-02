<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Overseer]].
 *
 * @see Overseer
 */
class OverseerQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Overseer[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Overseer|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
