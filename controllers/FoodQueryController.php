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

    public function actionIndex(string $filter = '', string $sort = ''): array
    {
        $queries = FoodQuery::find();

        if ($filter === 'failed') {
            $queries->where(['failed' => true]);
        }

        if ($sort === 'count') {
            $queries->orderBy('count DESC');
        } elseif ($sort === 'id') {
            $queries->orderBy('id ASC');
        } else {
            $queries->orderBy('updated_at DESC');
        }

        return $this->paginate($queries, 2);
    }
}
