<?php

namespace app\controllers;

use app\models\Article;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\models\Photo;
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

        return $this->paginate($articles->orderBy('datetime DESC'));
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
        $newArticle->type_id = $type_id;

        $files = UploadedFile::getInstancesByName('files');
        $photos = [];

        // если создаётся статья из полнотекстового редактора
        if ($type_id === 1) {
            $photos = $newArticle->handleImageMarkup($photos);
        }

        if (empty($newArticle->errors) and $newArticle->validate()) {
            $newArticle->save(false);

            if ($files) {
                $newArticle->files = $files;
                foreach ($files as $file) {
                    $photo = new Photo();

                    try {
                        $photo->upload($file);
                        $photos[] = $photo;
                    } catch (\Exception $exception) {
                        $newArticle->addError('files', $exception->getMessage());
                    }
                }
            }

            foreach ($photos as $photo) {
                $newArticle->linkPhoto($photo);
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

            if (!empty($files) && $files[0]->size) {
                $article->files = $files;
            }

            if ($formData and $article->validate()) {

                // Получаем уже имеющиеся фотографии
                $old_photos = $formData['old_photos'];

                // Декодируем массив с именами фотографий
                $old_photos = Json::decode($old_photos);

                // Сравниваем фотографии с загруженными ранее
                $difference = $article->comparePhotos($old_photos);

                // Удаляем лишние фотографии
                foreach ($difference as $photo) {
                    $photo = Photo::find()->where(['image' => $photo])->one();
                    $article->unlinkPhotos($photo);
                }

                if (!empty($files) && $files[0]->size) {
                    $article->linkPhoto($files);
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
            // Находим имеющиеся фотографии
            $article_photos = $this->photos;
            $article_photos = ArrayHelper::getColumn($article_photos, 'image');

            foreach ($article_photos as $photo) {
                $article->unlinkPhoto($photo);
            }

            $article->delete();

            \Yii::$app->response->statusCode = 204;

            return \Yii::$app->response;
        }

        throw new NotFoundHttpException('Запись с таким ID не найдена');
    }
}
