<?php

namespace app\models;

use yii\db\ActiveRecord;

class ModelWithSlug extends ActiveRecord
{
    private const EXTRA_SLUG_ENDING = '_podrobno';

    /**
     * @param string $baseSlug
     * @return string
     */
    public function formatUniqueSlug(string $baseSlug): string
    {
        if ($this->checkSlugExists($baseSlug)) {
            $baseSlug = $baseSlug . self::EXTRA_SLUG_ENDING;

            if ($this->checkSlugExists($baseSlug)) {
                $baseSlug = $baseSlug . '_' . $this->id;
            }
        }

        return $baseSlug;
    }

    /**
     * @param string $slug
     * @return bool
     */
    public function checkSlugExists(string $slug): bool
    {
        return static::find()
            ->where(['slug' => $slug])
            ->andWhere(['!=', 'id', $this->id])
            ->exists();
    }
}
