<?php

namespace app\helpers;

use Cocur\Slugify\Slugify;

class StringHelper extends \yii\helpers\StringHelper
{
    public static function str_between($string, $start, $end): string
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    /**
     *  <code>
     *      make_slug('Морская свинка'); // 'morskay_svinka'
     *  </code>
     */
    public static function make_slug(string $value): string
    {
        $slugify = new Slugify([
            'separator' => '_',
            'lowercase' => true,
        ]);

        return $slugify->slugify($value);
    }
}