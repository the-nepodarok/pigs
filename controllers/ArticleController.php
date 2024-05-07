<?php

namespace app\controllers;

use app\models\Article;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\models\Photo;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ArticleController extends ApiController
{
    public string $modelClass = Article::class;

    public function actionIndex(int $type_id = null): array
    {
        if ($type_id) {
            $articles = Article::find()->where("type_id = $type_id");

            return $this->paginate($articles->orderBy('datetime DESC'));
        }

        throw new BadRequestHttpException();
    }

    public function actionCreate(int $type_id = null): Article|array
    {
        if (!$type_id) {
            throw new BadRequestHttpException();
        }

        $formData = \Yii::$app->request->post();

        $newArticle = new Article();
        $newArticle->load($formData, '');
        $newArticle->type_id = $type_id;
        $photos = [];

        // если создаётся статья из полнотекстового редактора
        if ($type_id === 1) {
            $photos = $newArticle->handleImageMarkup($photos);
        }

        if (empty($newArticle->errors) and $newArticle->validate()) {
            $newArticle->save(false);

            if ($newArticle->type_id === 1) {
                foreach ($photos as $photo) {
                    $newArticle->linkPhoto($photo);
                }
            } else {

                $main_photo_index = $formData['main_photo_index'];

                // меняет порядок файлов, если одна из фотографий выбрана главной
                if (isset($main_photo_index) && intval($main_photo_index) !== 0) {
                    $newArticle->changePhotoOrder($main_photo_index);
                }

                $newArticle->handlePhotos();
            }

            return $newArticle;
        }

        return $this->validationFailed($newArticle);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id): Article|array
    {
        $article = Article::findOne($id);

        if ($article) {
            $formData = \Yii::$app->request->bodyParams;

            $article->load($formData, '');

            if ($formData and $article->validate()) {
                $photos = [];

                // если редактируется статья из полнотекстового редактора
                if ($article->type_id === 1) {
                    $photos = $article->handleImageMarkup($photos);
                    $difference = $article->comparePhotos(array_column($photos, 'image'));

                } else {
                    // Получаем уже имеющиеся фотографии
                    $old_photos = Json::decode($formData['old_photos']);

                    // Сравниваем фотографии с загруженными ранее
                    $difference = $article->comparePhotos($old_photos);
                }

                // Удаляем лишние фотографии
                foreach ($difference as $filename) {
                    try {
                        $article->unlinkPhoto(Photo::find()->where(['image' => $filename])->one());
                    } catch (\Exception $e) {
                        error_log($e->getMessage());
                    }
                }

                $article->refresh();

                // Загружаем новые из формы
                if ($article->type_id === 1) {
                    foreach ($photos as $photo) {
                        $article->linkPhoto($photo);
                    }
                } else {

                    // если одна из старых или новых фотографий должна стать главной
                    if (isset($formData['main_photo_name']) || isset($formData['main_photo_index'])) {

                        // получаем список текущих фотографий
                        $current_photos = $article->photos;

                        foreach ($current_photos as $current_photo) {
                            // отвязываем их
                            $article->unlink('photos', $current_photo, true);
                        }

                        // если нужно выбрать из новых фотографий
                        if (isset($formData['main_photo_index'])) {
                            // меняем порядок файлов, чтобы главная фотография была первой
                            $article->changePhotoOrder($formData['main_photo_index']);

                            // загружаем фотографии
                            $article->handlePhotos();
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
                            $new_photo->link('article', $article);
                        }

                        // если помимо главной из старых были переданые новые фото,
                        // загрузить их
                        if (isset($main_photo) && $article->files) {
                            $article->handlePhotos();
                        }

                    } elseif ($article->files) {
                        // если главной не была отмечена ни одна фотография, просто загрузить файлы
                        $article->handlePhotos();
                    }
                }

                $article->save(false);
                $article->refresh();

                return $article;
            }

            return $this->validationFailed($article);
        }

        throw new NotFoundHttpException('Объект не найден');
    }
}
