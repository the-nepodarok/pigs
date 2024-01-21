<?php

namespace app\controllers;

use app\models\Article;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class ArticleController extends ApiController
{
    public function actionIndex(?int $type_id = null)
    {
        $articles = Article::find();

        if ($type_id) {
            $articles = $articles->where("type_id = $type_id");
        }

        return $this->paginate($articles);
    }

    public function actionGet(int $id)
    {
        $article = Article::findOne($id);

        if ($article) {
//            if ($article->getPhotos()) {
//                $photos = $article->getPhotos()->select('image')->asArray()->all();
//                $photos = ArrayHelper::getColumn($photos, 'image');
//
//                if ($photos) {
//                    $article = $article->toArray();
//                    ArrayHelper::setValue($article, 'photos', $photos);
//                }
//            }
            return $article;
        }

        throw new NotFoundHttpException('Объект не найден');
    }

    public function actionCreate(int $type_id): Article|array
    {
        $formData = \Yii::$app->request->post();

        $newArticle = new Article();
        $newArticle->load($formData, '');
        $files = UploadedFile::getInstancesByName('files');

        $newArticle->type_id = $type_id;
        $newArticle->files = $files;

        if ($newArticle->validate()) {
            $newArticle->save(false);

            if ($files) {
                $newArticle->linkPhotos($files);
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
            $files = UploadedFile::getInstancesByName('files');
            $article->files = $files;

            if ($formData and $article->validate()) {
                $article->unlinkPhotos();

                if ($files) {
                    $article->linkPhotos($files);
                }

                $article->save(false);
                $article->refresh();

                return $article;
            }

            return $this->validationFailed($article);
        }

        throw new NotFoundHttpException('Объект не найден');
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id): Response
    {
        $article = Article::findOne($id);

        if ($article) {
            $article->unlinkPhotos();
            $article->delete();

            \Yii::$app->response->statusCode = 204;

            return \Yii::$app->response;
        }

        throw new NotFoundHttpException('Запись с таким ID не найдена');
    }
}
