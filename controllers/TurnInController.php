<?php

namespace app\controllers;

use app\models\TurnIn;

class TurnInController extends ApiController
{
    public string $modelClass = TurnIn::class;

    public static function allowedMethods(): array
    {
        return ['GET', 'POST', 'DELETE'];
    }

    public function allowedActions(): array
    {
        return ['create'];
    }
}
