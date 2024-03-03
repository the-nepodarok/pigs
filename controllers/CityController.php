<?php

namespace app\controllers;

use app\models\City;

class CityController extends ApiController
{
    public string $modelClass = City::class;
    public string $sortOption = 'id';

    public static function allowedMethods(): array
    {
        return ['GET'];
    }

    public function allowedActions(): array
    {
        return ['index'];
    }
}
