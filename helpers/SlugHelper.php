<?php

namespace app\helpers;

use Cocur\Slugify\Slugify;
use yii\db\Query;

class SlugHelper
{
    /**
     * @param string $value
     * @return string
     */
    public static function make(string $value): string
    {
        $slugify = new Slugify([
            'separator' => '_',
            'lowercase' => true,
        ]);

        return $slugify->slugify($value);
    }

    /**
     * @param string $tableName
     * @param string $value
     * @param int $currentId
     * @return string
     */
    public static function unique(string $tableName, string $value, int $currentId): string
    {
        $baseSlug = self::make($value);

        if (self::exists($tableName, $baseSlug, $currentId)) {
            $baseSlug = $baseSlug . '_podrobno';

            if (self::exists($tableName, $baseSlug, $currentId)) {
                $baseSlug = $baseSlug . '_' . $currentId;
            }
        }

        return $baseSlug;
    }

    /**
     * @param string $tableName
     * @param string $slug
     * @param int $currentId
     * @return bool
     */
    private static function exists(string $tableName, string $slug, int $currentId): bool
    {
        return (new Query())
            ->from($tableName)
            ->where(['slug' => $slug])
            ->andWhere(['!=', 'id', $currentId])
            ->exists();
    }
}
