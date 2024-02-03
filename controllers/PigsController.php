<?php

namespace app\controllers;

use app\models\Photo;
use app\models\Pig;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

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
                foreach ($files as $file) {
                    $photo = new Photo();

                    try {
                        $photo->upload($file);
                    } catch (\Exception $exception) {
                        error_log($exception->getMessage());
                    }

                    $newPig->linkPhoto($photo);
                }
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
            $pig->files = $files;

            if ($formData and $pig->validate()) {
                $pig->unlinkPhotos();

                if ($files) {
                    foreach ($files as $file) {
                        $photo = new Photo();

                        try {
                            $photo->upload($file);
                        } catch (\Exception $exception) {
                            error_log($exception->getMessage());
                        }

                        $pig->linkPhoto($photo);
                    }
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
