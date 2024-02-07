<?php

namespace app\controllers;

use app\models\Pig;
use http\Exception;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\helpers\Json;

class PigsController extends ApiController
{
    public $modelClass = 'app\models\Pig';

    public function actionError(): array
    {
        \Yii::$app->response->setStatusCode(501);
        return [];
    }

    public function actionIndex(string $graduated = '')
    {
        $pigs = Pig::find();

//        foreach ($pigs as $pig) {
//            $time = strtotime($pig->datetime.' UTC');
//            $pig->datetime = date("Y-m-d H:i:s", $time);
//        }

        if ($graduated and $graduated === 'graduated') {
            $pigs = $pigs->where('graduated');
        } else {
            $pigs = $pigs->where(['graduated' => false]);
        }

        return $this->paginate($pigs->orderBy('datetime DESC'), 15);
    }

    /**
     * @param int $id ID свиника
     * @throws NotFoundHttpException Ошибка 404 при неверном ID
     */
    public function actionGet(int $id): Pig|array|null
    {
        $pig = Pig::findOne($id);

        if ($pig) {
//            if ($pig->getPhotos()) {
//                $photos = $pig->getPhotos()->select('image')->asArray()->all();
//                $photos = ArrayHelper::getColumn($photos, 'image');
//        }

//                    $pig = $pig->toArray();
//                    ArrayHelper::setValue($pig, 'photos', $photos);
            return $pig;
        } else {
            throw new NotFoundHttpException('Объект не найден');
        }
    }

    public function actionCreate(): Pig|array
    {
        $formData = \Yii::$app->request->post();

        $newPig = new Pig();
        $newPig->load($formData, '');
        $files = UploadedFile::getInstancesByName('files');
        $newPig->files = $files;

        if ($newPig->validate()) {
            $newPig->save(false);

            if ($files) {
                $newPig->linkPhotos($files);
            }

            return $newPig;
        }

        return $this->validationFailed($newPig);
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
            $files = UploadedFile::getInstancesByName('files');

            if (!empty($files) && $files[0]->size) {
                $pig->files = $files;
            }

            if ($formData and $pig->validate()) {

                // Получаем уже имеющиеся фотографии
                $old_photos = $formData['old_photos'];

                // Декодируем массив с именами фотографий
                $old_photos = Json::decode($old_photos);

                // Сравниваем фотографии с загруженными ранее
                $difference = $pig->comparePhotos($old_photos);

                // Удаляем лишние фотографии
                foreach ($difference as $photo) {
                    $pig->unlinkPhoto($photo);
                }

                if (!empty($files) && $files[0]->size) {
                    $pig->linkPhotos($files);
                }

                $pig->save(false);
                $pig->refresh();
                return $pig;
            }

            return $this->validationFailed($pig);
       }

        throw new NotFoundHttpException('Объект не найден');
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

    /**
     * Свинок удалять нельзя!
     * @throws MethodNotAllowedHttpException
     */
    public function actionDelete(int $id)
    {
        throw new MethodNotAllowedHttpException('Свинок удалять нельзя!');
    }

    public function actionRandomize(int $number, string $graduated = ''): Pig|array|null
    {
        $isGraduated = false;

        if ($graduated === 'graduated') {
            $isGraduated = true;
        }

        return Pig::find()->joinWith('photos', false,'INNER JOIN')->where(['IN','graduated', $isGraduated])->groupBy('pigs.id')->orderBy('RANDOM()')->limit($number)->all();
    }
}
