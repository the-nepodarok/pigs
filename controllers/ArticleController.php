<?php

namespace app\controllers;

use app\models\Article;
use app\models\Photo;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class ArticleController extends ApiController
{
    public string $modelClass = Article::class;

    public function actionIndex(int $type_id = null, int $all = 0): array
    {
        if ($type_id) {
            $articles = Article::find()->where("type_id = $type_id");

            return $all ? ['payload' => $articles->all()] : $this->paginate($articles->orderBy('datetime DESC'));
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

        // обработать картинки из полнотекстового редактора
        $photos = $newArticle->handleImageMarkup($photos);

        if (empty($newArticle->errors) and $newArticle->validate()) {
            $newArticle->save(false);

            foreach ($photos as $photo) {
                $newArticle->linkPhoto($photo);
            }

            return $newArticle;
        }

        return $this->validationFailed($newArticle);
    }

    public function actionUpdate(int $id): Article|array
    {
        $article = Article::findOne($id);

        if ($article) {
            $formData = \Yii::$app->request->bodyParams;

            $article->load($formData, '');

            if ($formData and $article->validate()) {

                $article->save();

                $photos = [];

                // обрабатываем картинки из полнотекстового редактора
                $photos = $article->handleImageMarkup($photos);
                $difference = $article->comparePhotos(array_column($photos, 'image')) ?? [];

                // Удаляем лишние фотографии
                foreach ($difference as $filename) {
                    try {
                        $article->unlinkPhoto(Photo::find()->where(['image' => $filename])->one());
                    } catch (\Exception $e) {
                        error_log($e->getMessage());
                    }
                }

                // Загружаем новые из формы
                foreach ($photos as $photo) {
                    $article->linkPhoto($photo);
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
