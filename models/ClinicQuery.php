<?php

namespace app\models;

use yii\db\ActiveQuery;

class ClinicQuery extends ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return Clinic[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Clinic|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}