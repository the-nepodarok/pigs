<?php

namespace app\controllers;

use app\models\Article;
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

                // Загружаем новые из формы
                if ($article->type_id === 1) {
                    foreach ($photos as $photo) {
                        $article->linkPhoto($photo);
                    }
                } else {
                    $article->handlePhotos();
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
