<?php

namespace app\controllers;

use app\models\EntityWithPhotos;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

class ApiController extends \yii\rest\Controller
{
    public $layout = false;
    public string $modelClass;
    public string $sortOption = 'datetime';

    public static function allowedDomains(): array
    {
        return [
            'http://localhost:5173',
            'https://piggy-hus.vercel.app',
            'https://domiksvinok.ru'
        ];
    }

    public static function allowedMethods(): array
    {
        return [
            'GET', 'POST', 'PATCH', 'DELETE',
        ];
    }

    protected function allowedActions(): array
    {
        return ['get', 'index', 'randomize'];
    }

    public function afterAction($action, $result)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::afterAction($action, $result);
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
            'except' => $this->allowedActions()
        ];

        return $bh;
    }

    /**
     * Возврат ошибок валидации модели
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
    protected function paginate(ActiveQuery $query, int $perPage = 5): array
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

    public function actionIndex(): array
    {
        return $this->modelClass::find()->orderBy($this->sortOption)->all();
    }

    /**
     * @param int $id ID записи
     * @throws NotFoundHttpException Ошибка 404 при неверном ID
     */
    public function actionGet(int $id): EntityWithPhotos|array|null
    {
        $entry = $this->modelClass::findOne($id);

        if ($entry) {
            return $entry;
        } else {
            throw new NotFoundHttpException('Объект не найден');
        }
    }

    public function actionCreate(): EntityWithPhotos|array
    {
        $formData = \Yii::$app->request->post();

        $newEntry = new $this->modelClass();
        $newEntry->load($formData, '');

        if ($newEntry->validate()) {
            $newEntry->save(false);

            $newEntry->handlePhotos();

            return $newEntry;
        }

        return $this->validationFailed($newEntry);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id): \yii\web\Response
    {
        $entry = $this->modelClass::findOne($id);

        if ($entry) {
            $entry->unlinkAllPhotos();
            $entry->delete();

            \Yii::$app->response->statusCode = 204;

            return \Yii::$app->response;
        }

        throw new NotFoundHttpException('Запись с таким ID не найдена');
    }
}