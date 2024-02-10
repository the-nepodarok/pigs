<?php

namespace app\controllers;

use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class ApiController extends \yii\rest\Controller
{
    public $layout = false;

    public static function allowedDomains(): array
    {
        return [
            'http://localhost:5173',
        ];
    }

    public static function allowedMethods(): array
    {
        return [
            'GET', 'POST', 'PATCH', 'DELETE',
        ];
    }

    public function behaviors(): array
    {
        $bh = parent::behaviors();

        unset($bh['authenticator']);

        $bh['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => static::allowedDomains(),
                'Access-Control-Request-Method' => static::allowedMethods(),
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Allow-Headers' => ['Origin', 'Content-Type', 'Accept', 'Authorization'],
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [],
            ]
        ];

        $bh['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['get', 'index', 'login', 'randomize']
        ];

        return $bh;
    }

    /**
     * Возврат ошибок валидации модели с кодом 400
     * @param ActiveRecord $model
     * @return array
     */
    protected function validationFailed(ActiveRecord $model): array
    {
        \Yii::$app->response->statusCode = 400;
        return $model->errors;
    }

    /**
     * Получение постраничных данных
     * @param ActiveQuery $query
     * @param int $perPage
     * @return array
     */
    protected function paginate(ActiveQuery $query, int $perPage = 5)
    {
        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => $perPage
        ]);

        $pagination->pageSizeParam = false;

        $payload = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return [
            'payload' => $payload,
            'pagination' => [
                'page' => $pagination->page,
                'pageCount' => $pagination->pageCount
            ]
        ];
    }
}