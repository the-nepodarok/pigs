<?php

namespace app\controllers;

use app\models\Overseer;
use app\models\Status;

class OverseerController extends ApiController
{
    public string $modelClass = Overseer::class;
    public string $sortOption = 'overseer_name';

    public static function allowedMethods(): array
    {
        return ['GET'];
    }

    public function allowedActions(): array
    {
        return ['index'];
    }

    public function actionIndex(string $active = ''): array
    {
        $query = Overseer::find();

        if ($active) {
            $query = $query->where(['overseer.active' => 1])
                ->andWhere(['pigs.status_id' => Status::AVAILABLE_STATUSES])
                ->innerJoin('pigs', 'pigs.overseer_id = overseer.id');
        }


        return $query->orderBy($this->sortOption)->all();
    }
}