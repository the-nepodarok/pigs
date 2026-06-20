<?php

namespace app\models\traits;

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

    /**
     * Constants in traits are only allowed since PHP 8.2,
     * so the key for uniqueness is hardcoded
     *
     * @param string $baseSlug
     * @return string
     */
    public function formatUniqueSlug(string $baseSlug): string
    {
        if ($this->alreadyExists(['slug' => $baseSlug])) {
            $baseSlug = $baseSlug . '_podrobno';

            if ($this->alreadyExists(['slug' => $baseSlug])) {
                $baseSlug = $baseSlug . '_' . $this->id;
            }
        }

        return $baseSlug;
    }
}
