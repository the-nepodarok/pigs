<?php

namespace app\controllers;

use app\models\FoodQuery;

class FoodQueryController extends ApiController
{
    public string $modelClass = FoodQuery::class;
    public string $sortOption = 'count';

    public static function allowedMethods(): array
    {
        return ['GET'];
    }

    public function allowedActions(): array
    {
        return ['index'];
    }

    public function actionIndex(string $filter = '', string $sort = 'date'): array
    {
        $queries = FoodQuery::find();

        if ($filter === 'failed') {
            $queries->where(['failed' => true]);
        }

        $sortOption = match($sort) {
            'count' => 'count DESC',
            'id' => 'id ASC',
            'date' => 'updated_at DESC',
        };

        $queries->orderBy($sortOption);

        return $this->paginate($queries, 2);
    }
}
