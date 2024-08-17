<?php

namespace app\controllers;

use app\models\Article;
use app\models\Tag;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\models\Photo;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ArticleController extends ApiController
{
    public string $modelClass = Article::class;

    public function allowedActions(): array
    {
        return ArrayHelper::merge(parent::allowedActions(), ['find-by-tag', 'create']);
    }

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

            if ($newArticle->hashtags) {
                $hashtags = array_filter(explode(' ', $newArticle->hashtags));

                foreach ($hashtags as $hashtag) {
                    $newArticle->attachTag($hashtag);
                }
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

                // обработка хэштегов
                $currentHashtags = ArrayHelper::getColumn($article->tags, 'tag_value');

                if (!empty($currentHashtags) && empty($article->hashtags)) {
                    foreach ($currentHashtags as $hashtag) {
                        $article->detachTag($hashtag);
                    }
                }

                if ($article->hashtags) {
                    $hashtags = array_filter(explode(' ', $article->hashtags));

                    // если убрали что-то из старых тегов
                    if ($diff = array_diff($currentHashtags, $hashtags)) {
                        foreach ($diff as $hashtag) {
                            $article->detachTag($hashtag);
                        }
                    }

                    // если добавили что-то новое
                    if ($diff = array_diff($hashtags, $currentHashtags)) {
                        foreach ($diff as $hashtag) {
                            $article->attachTag($hashtag);
                        }
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

    public function actionDelete(int $id): Response
    {
        $article = Article::findOne($id);

        if ($article) {
            $article->unlinkAllPhotos();
            $article->detachAllTags();
            $article->delete();

            \Yii::$app->response->statusCode = 204;

            return \Yii::$app->response;
        }

        throw new NotFoundHttpException('Запись с таким ID не найдена');
    }

    /**
     * @param string $tag
     * @return array
     */
    public function actionFindByTag(string $tag): array
    {
        $articles = Article::find()->joinWith('tags')->where(['tags.tag_value' => $tag])->all();
        return ['payload' => $articles];
    }

    /**
     * @param int $typeId
     * @return Article|array|null
     */
    public function actionRandomize(int $typeId): Article|array|null
    {
        $articles = Article::find()->select('articles.id, title, datetime')
            ->where(['type_id' => $typeId]);

        if ($typeId === 1) {
            // для новостей
            $articles = $articles->orderBy('RANDOM()');
        } else {
            // для статей
            $articles = $articles->orderBy('datetime DESC');
        }

        return ['payload' => $articles->limit(3)->all()];
    }
}
