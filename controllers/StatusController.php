<?php

namespace app\controllers;

use app\models\Status;

class StatusController extends ApiController
{
    public string $modelClass = Status::class;
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
