<?php

namespace app\models;

use yii\db\ActiveRecord;

class BaseModel extends ActiveRecord
{

    public function alreadyExists(array $where): bool
    {
        return static::find()
            ->where($where)
            ->andWhere(['!=', 'id', $this->id])
            ->exists();
    }
}
