<?php

namespace app\controllers;

use app\models\Photo;
use app\models\Pig;
use app\models\Status;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
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
            $pigs = $pigs->where(['status_id' => Status::GRADUATED_STATUSES]);
        } else {
            $pigs = $pigs->where(['status_id' => Status::AVAILABLE_STATUSES]);
        }

        $pigs = $pigs->Joinwith('city');
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

                if (isset($formData['old_photos'])) {
                    // Получаем уже имеющиеся фотографии
                    $old_photos = Json::decode($formData['old_photos']);

                    // Сравниваем фотографии с загруженными ранее
                    $difference = $pig->comparePhotos($old_photos);

                    // Удаляем лишние фотографии
                    foreach ($difference as $photo) {
                        $photo = Photo::findOne(['image' => $photo]);
                        $pig->unlinkPhoto($photo);
                    }
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

    public function actionGraduate(int $id, string $type): Pig
    {
        $pig = Pig::findOne($id);
        $status = Status::find()->where(['value' => $type])->one();

        if ($pig) {
            $pig->status_id = $status->id;
            $pig->graduation_date = date('Y-m-d H:i:s');
            $pig->save(false);
        }

        return $pig;
    }

    public function actionRandomize(int $number, string $graduated = ''): Pig|array|null
    {
        $graduatedStatus = 1; // looking-for-home

        if ($graduated === 'graduated') {
            $graduatedStatus = 2; // graduated
        }

        $pigs = Pig::find()
            ->select('pigs.id, name, image')
            ->joinWith('photos', false,'INNER JOIN')
            ->where(['IN','status_id', $graduatedStatus])
            ->groupBy('pigs.id')
            ->orderBy('RANDOM()')->limit($number);

        return ['payload' => $pigs->all(), 'count' => $pigs->count()];
    }
}
