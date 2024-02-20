<?php

namespace app\controllers;

use app\models\Photo;
use app\models\Pig;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\helpers\Json;

class PigsController extends ApiController
{
    public string $modelClass = Pig::class;

    public function actionError(): array
    {
        \Yii::$app->response->setStatusCode(501);
        return [];
    }

    public function actionIndex(string $graduated = ''): array
    {
        $pigs = Pig::find();

        if ($graduated and $graduated === 'graduated') {
            $pigs = $pigs->where('graduated');
        } else {
            $pigs = $pigs->where(['graduated' => false]);
        }

        return $this->paginate($pigs->orderBy('datetime DESC'), 15);
    }

    /**
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate(int $id): Pig|array
    {
        $pig = Pig::findOne($id);

        if ($pig) {
            $formData = \Yii::$app->request->getBodyParams();

            $pig->load($formData, '');

            if ($formData and $pig->validate()) {

                // Получаем уже имеющиеся фотографии
                $old_photos = Json::decode($formData['old_photos']);

                // Сравниваем фотографии с загруженными ранее
                $difference = $pig->comparePhotos($old_photos);

                // Удаляем лишние фотографии
                foreach ($difference as $photo) {
                    $photo = Photo::findOne(['image' => $photo]);
                    $pig->unlinkPhoto($photo);
                }

                $pig->handlePhotos();

                $pig->save(false);
                $pig->refresh();
                return $pig;
            }

            return $this->validationFailed($pig);
       }

        throw new NotFoundHttpException('Объект не найден');
    }

    /**
     * Свинок удалять нельзя!
     * @throws MethodNotAllowedHttpException
     */
    public function actionDelete(int $id): \yii\web\Response
    {
        throw new MethodNotAllowedHttpException('Свинок удалять нельзя!');
    }

    public function actionGraduate(int $id): Pig
    {
        $pig = Pig::findOne($id);

        if ($pig) {
            $pig->graduated = true;
            $pig->save(false);
        }

        return $pig;
    }

    public function actionRandomize(int $number, string $graduated = ''): Pig|array|null
    {
        $isGraduated = false;

        if ($graduated === 'graduated') {
            $isGraduated = true;
        }

        $pigs = Pig::find()
            ->select('pigs.id, name, image')
            ->joinWith('photos', false,'INNER JOIN')
            ->where(['IN','graduated', $isGraduated])
            ->groupBy('pigs.id')
            ->orderBy('RANDOM()')->limit($number);

        return ['payload' => $pigs->all(), 'count' => $pigs->count()];
    }
}
