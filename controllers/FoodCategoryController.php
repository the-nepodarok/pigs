<?php

namespace app\controllers;

use app\models\FoodCategory;

class FoodCategoryController extends ApiController
{
    public string $modelClass = FoodCategory::class;
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
