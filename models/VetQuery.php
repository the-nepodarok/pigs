<?php

namespace app\models;

use yii\db\ActiveQuery;

class VetQuery extends ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return Vet[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Vet|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}