<?php

namespace app\models;

use app\helpers\StringHelper;

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
            $slug = StringHelper::make_slug($sourceValue);
            $this->slug = $this->formatUniqueSlug($slug);

            static::updateAll(['slug' => $this->slug], ['id' => $this->id]);
        }
    }
}
