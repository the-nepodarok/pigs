<?php

namespace app\controllers;

use app\models\Photo;
use app\models\Pig;
use app\models\Status;
use yii\helpers\ArrayHelper;
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

    public function actionIndex(string $graduated = '', int $all = 0): array
    {
        $pigs = Pig::find();
        $order_param = 'datetime';

        if ($graduated and $graduated === 'graduated') {
            $pigs = $pigs->where(['status_id' => Status::GRADUATED_STATUSES]);
            $order_param = 'graduation_date';
        } else {
            $pigs = $pigs->where(['status_id' => Status::AVAILABLE_STATUSES]);
            // отсортировать по статусу, чтобы поместить свинок на карантине в конец списка
            $order_param = 'status_id ASC, ' . $order_param;
        }

        $pigs = $pigs->orderBy($order_param . ' DESC');

        return $all ? ['payload' => $pigs->all()] : $this->paginate($pigs, 10);
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

            $formData['delivery'] = $formData['delivery'] ?? 0;

            $pig->load($formData, '');

            if ($formData and $pig->validate()) {
                // если дата не указана, поставить текущую
                if (!$pig->graduation_date) {
                    $pig->graduation_date = in_array($pig->status_id, Status::AVAILABLE_STATUSES) ? null : date('Y-m-d');
                }

                $pig->save();

                if (array_key_exists('old_photos', $formData)) {
                    // Получаем уже имеющиеся фотографии
                    $oldPhotos = Json::decode($formData['old_photos']);

                    // Сравниваем фотографии с загруженными ранее
                    $difference = $pig->comparePhotos($oldPhotos);

                    if ($difference) {
                         // Удаляем лишние фотографии
                        foreach ($difference as $photo) {
                            $photo = Photo::findOne(['image' => $photo]);
                            $pig->unlinkPhoto($photo);
                        }

                        // обновить модель со связями
                        $pig->refresh();
                    }
                }

                // если одна из старых или новых фотографий должна стать главной
                if (isset($formData['main_photo_name']) || isset($formData['main_photo_index'])) {
                    $mainPhotoName = $formData['main_photo_name'] ?? false;
                    $mainPhotoIndex = $formData['main_photo_index'] ?? false;
                    $pig->rearrangePhotos($mainPhotoName, $mainPhotoIndex);

                } elseif ($pig->files) {
                    // если главной не была отмечена ни одна фотография, просто загрузить файлы
                    $pig->handleNewPhotos();
                }

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

    public function actionGraduate(int $id, int $typeId): Pig
    {
        $pig = Pig::findOne($id);

        if ($pig) {
            $status = Status::findOne($typeId);

            if ($status) {
                $pig->graduation_date = in_array($status->id, Status::AVAILABLE_STATUSES) ? null : date('Y-m-d');
                $pig->status_id = $status->id;
                $pig->save(false);
            }
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

    /**
     * @param int $statusId
     * @return int
     * @throws \HttpInvalidParamException
     */
    public function actionCount(int $statusId): int
    {
        if (!$statusId) {
            throw new \HttpInvalidParamException('Не передано значение');
        }

        $pigsCount = Pig::find()
            ->where(['IN','status_id', $statusId])
            ->count();

        return $pigsCount;
    }
}
