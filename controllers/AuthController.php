<?php

namespace app\controllers;

use app\models\Admin;
use Psy\Util\Json;

class AuthController extends ApiController
{
    public string $modelClass = 'app\models\Admin';

    public function actionLogin()
    {
        $post = \Yii::$app->request->post();

        $auth = new Admin();
        $auth->load($post, '');


        if ($auth->validate()) {
            $user = Admin::find()->where(['name' => $post['name']])->one();

            if ($user->validatePassword($post['password'])) {
                $token = \Yii::$app->security->generateRandomString();
                $user->token = $token;
                $user->save();

                return $user->toArray();
            }
        }

        return $this->validationFailed($user ?? $auth);
    }

    public function actionLogout()
    {
        $token = substr(\Yii::$app->request->getHeaders()['authorization'], 7);
        $admin = Admin::findIdentityByAccessToken($token);
        $admin->token = null;
        $admin->save();

        \Yii::$app->response->statusCode = 205;
    }
}
