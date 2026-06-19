<?php

namespace app\models;

use app\helpers\SlugHelper;

trait HasSlug
{
    /**
     * @return string
     */
    abstract protected function slugSourceAttribute(): string;

    /**
     * @param $insert
     * @param $changedAttributes
     * @return void
     */
    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);

        $sourceAttribute = $this->slugSourceAttribute();

        if ($insert || array_key_exists($sourceAttribute, $changedAttributes)) {
            $sourceValue = $this->getAttribute($sourceAttribute);
            $this->slug = SlugHelper::unique(static::tableName(), $sourceValue, $this->id);

            static::updateAll(['slug' => $this->slug], ['id' => $this->id]);
        }
    }
}
