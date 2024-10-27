<?php

namespace app\controllers;

use app\models\City;
use app\models\Pig;
use app\models\Status;

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
        return ['index', 'active'];
    }

    public function actionActive(): array
    {
        $activeCities = Pig::find()->select('city_id')->where(['status_id' => Status::AVAILABLE_STATUSES])->groupBy('city_id');
        return City::find()->where(['id' => $activeCities])->all();
    }
}
