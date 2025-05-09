<?php

namespace app\controllers;

use app\models\Clinic;

class ClinicController extends ApiController
{
    public string $modelClass = Clinic::class;
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