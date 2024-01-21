<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[TurnIn]].
 *
 * @see TurnIn
 */
class TurnInQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TurnIn[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TurnIn|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
