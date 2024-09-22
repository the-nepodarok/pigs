<?php

namespace app\commands;

use app\models\EntityWithPhotos;
use app\models\FoodProduct;
use app\models\Photo;
use yii\console\Controller;

class CloudController extends Controller
{
    /**
     * @throws \Exception
     */
    public function actionIndex(): void
    {
        $photos = Photo::find()->all();

        foreach ($photos as $photo) {
            if (!$photo->cloud) {
                $photo->uploadToCloud($photo->food_product_id ? FoodProduct::UPLOAD_DIRECTORY : EntityWithPhotos::UPLOAD_DIRECTORY);
                $photo->save();
            }
        }
    }
}