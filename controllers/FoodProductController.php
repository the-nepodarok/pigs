<?php

namespace app\controllers;

use app\models\FoodProduct;
use app\models\FoodQuery;
use yii\db\Expression;
use yii\web\NotFoundHttpException;

class FoodProductController extends ApiController
{
    public string $modelClass = FoodProduct::class;
    public string $sortOption = 'id';

    public function actionCreate(): FoodProduct|array
    {
        $formData = \Yii::$app->request->post();

        $newProduct = new FoodProduct();
        $newProduct->load($formData, '');

        if ($newProduct->validate()) {
            $newProduct->description =
                ($formData['desc'] ?? ' ') . '|' . ($formData['doses'] ?? ' ') . '|' . ($formData['allowed'] ?? ' ' ) .
                    '|' . ($formData['restrictions'] ?? ' ') . '|' . ($formData['notes'] ?? ' ');

            if ($newProduct->file) {
                $newProduct->uploadImage($newProduct->file);
            }

            $newProduct->save(false);
        } else {
            return $this->validationFailed($newProduct);
        }

        return $newProduct;
    }

    public function actionUpdate(int $id): FoodProduct|array
    {
        $product = FoodProduct::findOne($id);

        $formData = \Yii::$app->request->post();
        $product->load($formData, '');

        if ($product->validate()) {
            $product->description =
                ($formData['desc'] ?? ' ') . '|' . ($formData['doses'] ?? ' ') . '|' . ($formData['allowed'] ?? ' ' ) .
                    '|' . ($formData['restrictions'] ?? ' ') . '|' . ($formData['notes'] ?? ' ');

            if ($product->file) {

                if ($product->image) {
                    $product->unlinkImage();
                }

                $product->uploadImage($product->file);
            }

            $product->save(false);
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

            if ($product->image) {
                $product->unlinkImage();
            }

            $product->delete();

            \Yii::$app->response->statusCode = 204;
            return \Yii::$app->response;
        }

        throw new NotFoundHttpException('Запись с таким ID не найдена');
    }

    public function actionSearch(int $type, string $query)
    {
        $products = FoodProduct::find()->leftJoin('food_categories', 'category_id = food_categories.id');

        if ($type) {
            $products = $products->where(['food_categories.id' => $type]);
        }

        if ($query) {

            $products = $products->andWhere(['OR',
                ['LIKE', 'title', $query],
                ['LIKE', 'synonyms', $query],
                ['LIKE', 'food_categories.value', $query]]);

            $foodQuery = FoodQuery::find()->where(['value' => $query])->one();

            if (!$foodQuery) {
                $foodQuery = new FoodQuery();
                $foodQuery->value = $query;
            }

            $foodQuery->count++;
            $foodQuery->updated_at = new Expression("DATE('now')");
            $foodQuery->save(false);
        }

        $products = $products->all();

        if ($query && !$products) {
            $foodQuery->failed = true;
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
