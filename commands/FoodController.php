<?php

namespace app\commands;

use app\models\FoodCategory;
use app\models\FoodProduct;
use yii\console\Controller;

class FoodController extends Controller
{
    /**
     * @throws \Exception
     */
    public function actionRelink(): void
    {
        $products = FoodProduct::find()->all();

        foreach ($products as $product) {
            $product->link('categories', FoodCategory::findOne(['id' => $product->category_id]));
        }
    }
}