<?php

namespace app\controllers;

use app\models\Overseer;

class OverseerController extends ApiController
{
    public string $modelClass = Overseer::class;

    public static function allowedMethods(): array
    {
        return ['GET'];
    }

    public function allowedActions(): array
    {
        return ['index'];
    }

    public function actionIndex(): array
    {
        return Overseer::find()->where(['active' => true])->all();
    }
}