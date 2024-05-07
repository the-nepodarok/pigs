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

    public function actionIndex(string $graduated = ''): array
    {
        $pigs = Pig::find();
        $order_param = 'datetime';

        if ($graduated and $graduated === 'graduated') {
            $pigs = $pigs->where(['status_id' => Status::GRADUATED_STATUSES]);
            $order_param = 'graduation_date';
        } else {
            $pigs = $pigs->where(['status_id' => Status::AVAILABLE_STATUSES]);
        }

        return $this->paginate($pigs->orderBy($order_param . ' DESC'), 15);
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

                    // обновить модель со связями
                    $pig->refresh();
                }

                // если одна из старых или новых фотографий должна стать главной
                if (isset($formData['main_photo_name']) || isset($formData['main_photo_index'])) {

                    // получаем список текущих фотографий
                    $current_photos = $pig->photos;

                    foreach ($current_photos as $current_photo) {
                        // отвязываем их
                        $pig->unlink('photos', $current_photo, true);
                    }

                    // если нужно выбрать из новых фотографий
                    if (isset($formData['main_photo_index'])) {
                        // меняем порядок файлов, чтобы главная фотография была первой
                        $pig->changePhotoOrder($formData['main_photo_index']);

                        // загружаем фотографии
                        $pig->handlePhotos();
                    }

                    // получаем массив из имён фотографий, что уже были в системе
                    $current_photos = ArrayHelper::getColumn($current_photos, 'image');

                    // если главной фотографией должна быть одна из старых
                    if (isset($formData['main_photo_name'])) {

                        // находим её индекс в массиве
                        $index = array_search ($formData['main_photo_name'], $current_photos);

                        // переносим на первое место
                        $main_photo = ArrayHelper::remove($current_photos, $index);
                        array_unshift($current_photos, $main_photo);
                    }

                    // возвращаем в бд старые фотографии
                    foreach ($current_photos as $photo) {
                        $new_photo = new Photo();
                        $new_photo->image = $photo;
                        $new_photo->link('pig', $pig);
                    }

                    // если помимо главной из старых были переданые новые фото,
                    // загрузить их
                    if (isset($main_photo) && $pig->files) {
                        $pig->handlePhotos();
                    }

                } elseif ($pig->files) {
                    // если главной не была отмечена ни одна фотография, просто загрузить файлы
                    $pig->handlePhotos();
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

    public function actionGraduate(int $id, int $type_id): Pig
    {
        $pig = Pig::findOne($id);

        if ($pig) {
            $status = Status::findOne($type_id);

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
}
