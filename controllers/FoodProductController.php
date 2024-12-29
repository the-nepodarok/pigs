<?php

namespace app\controllers;

use app\helpers\StringHelper;
use app\models\FoodProduct;
use app\models\FoodQuery;
use yii\db\Exception;
use yii\db\Expression;
use yii\web\NotFoundHttpException;

class FoodProductController extends ApiController
{
    public string $modelClass = FoodProduct::class;
    public string $sortOption = 'title';

    /**
     * @throws Exception
     */
    public function actionCreate(): FoodProduct|array
    {
        $formData = \Yii::$app->request->post();

        $newProduct = new FoodProduct();

        $newProduct->load($formData, '');

        if ($newProduct->validate()) {
            $newProduct->description =
                ($formData['desc'] ?? ' ') . '|' . ($formData['doses'] ?? ' ') . '|' . ($formData['allowed'] ?? ' ' ) .
                    '|' . ($formData['restrictions'] ?? ' ') . '|' . ($formData['notes'] ?? ' ');

            $newProduct->save(false);
            $newProduct->handleCategories();

            if ($newProduct->files) {
                $newProduct->handleNewPhotos();
            }
        } else {
            return $this->validationFailed($newProduct);
        }

        return $newProduct;
    }

    /**
     * @throws Exception
     */
    public function actionUpdate(int $id): FoodProduct|array
    {
        $product = FoodProduct::findOne($id);

        $formData = \Yii::$app->request->post();
        $formData['is_banned'] = $formData['is_banned'] ?? 0;

        $product->load($formData, '');

        if ($product->validate()) {
            $product->description =
                ($formData['desc'] ?? ' ') . '|' . ($formData['doses'] ?? ' ') . '|' . ($formData['allowed'] ?? ' ' ) .
                    '|' . ($formData['restrictions'] ?? ' ') . '|' . ($formData['notes'] ?? ' ');

            $product->save(false);
            $product->handleCategories();

            if ($product->files) {

                if ($product->photos) {
                    $product->unlinkAllPhotos();
                }

                $product->handleNewPhotos();
            }
        } else {
            return $this->validationFailed($product);
        }

        return $product;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id): \yii\web\Response
    {
        $product = FoodProduct::findOne($id);

        if ($product) {

            if ($product->photos) {
                $product->unlinkAllPhotos();
            }

            $product->unlinkAllCategories();
            $product->delete();

            \Yii::$app->response->statusCode = 204;
            return \Yii::$app->response;
        }

        throw new NotFoundHttpException('Запись с таким ID не найдена');
    }

    public function actionSearch(int $type, string $query)
    {
        $products = FoodProduct::find()
            ->leftJoin('food_categories_products', 'food_products.id = product_id')
            ->leftJoin('food_categories', 'food_categories.id = food_categories_products.category_id');

        if ($type) {
            $products = $products->where(['food_categories.id' => $type]);
        }

        if ($query) {

            $query = StringHelper::mb_ucfirst($query);
            $products = $products->andWhere(['OR',
                ['LIKE', 'title', $query],
                ['LIKE', 'synonyms', $query],
                ['LIKE', 'food_categories.value', $query]])->orWhere(
                    ['OR',
                        ['LIKE', 'title', mb_strtolower($query)],
                        ['LIKE', 'synonyms', mb_strtolower($query)],
                        ['LIKE', 'food_categories.value', mb_strtolower($query)]]
                );

            $foodQuery = FoodQuery::find()->where(['value' => $query])->one();

            if (!$foodQuery) {
                $foodQuery = new FoodQuery();
                $foodQuery->value = $query;
            }

            $foodQuery->count++;
            $foodQuery->updated_at = new Expression("DATE('now')");
            $foodQuery->save(false);
        }

        $products = $products->orderBy($this->sortOption)->all();

        if ($query) {
            if (!$products) {
                $foodQuery->failed = true;
            } else if ($foodQuery->failed) {
                $foodQuery->failed = false;
            }
            $foodQuery->save(false);
        }

        return $products;
    }

    /**
     * @return array
     */
    public function actionRandomize(): array
    {
        return FoodProduct::find()->orderBy('RANDOM()')->limit(5)->all();
    }
}
