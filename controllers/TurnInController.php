<?php

namespace app\controllers;

use app\models\TurnIn;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class TurnInController extends ApiController
{
    public static function allowedMethods(): array
    {
        return ['GET', 'POST', 'DELETE'];
    }

    public function actionIndex(): array
    {
        return TurnIn::find()->all();
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionGet(int $id): TurnIn
    {
        $request = TurnIn::findOne($id);

        if ($request) {
            return $request;
        }

        throw new NotFoundHttpException('Запись не найдена');
    }

    public function actionCreate(): TurnIn|array
    {
        $formData = \Yii::$app->request->post();

        $newEntry = new TurnIn();
        $newEntry->load($formData, '');
        $files = UploadedFile::getInstancesByName('files');
        $newEntry->files = $files;

        if ($newEntry->validate()) {
            $newEntry->save(false);

            if ($files) {
                $newEntry->linkPhotos($files);
            }

            return $newEntry;
        }

        return $this->validationFailed($newEntry);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id)
    {
        $entry = TurnIn::findOne($id);

        if ($entry) {
            $entry->unlinkPhotos();
            $entry->delete();

            \Yii::$app->response->statusCode = 204;

            return \Yii::$app->response;
        }

        throw new NotFoundHttpException('Запись с таким ID не найдена');
    }
}
