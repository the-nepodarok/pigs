<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => 'pigs/index',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'Kq0fWa8ON5GRwAEkDZpFvlYs_WAeqLz7',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'multipart/form-data' => 'yii\web\MultipartFormDataParser',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\Admin',
        ],
        'errorHandler' => [
            'errorAction' => 'pigs/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'normalizer' => [
                'class' => 'yii\web\UrlNormalizer',
            ],
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'article',
//                    'except' => ['view', 'create'],
                    'patterns' => [
                        'GET type/<type_id:[1-2]>' => 'index',
                        'GET <id>' => 'get',
                        'POST type/<type_id:\d+>' => 'create',
                        'DELETE <id>' => 'delete'
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'pigs',
                    'extraPatterns' => [
                        'GET <id:\d+>' => 'get',
                        'GET <graduated:\w+>' => 'index',
                        'PATCH graduate/<id>' => 'graduate'
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['requests' => 'turn-in'],
                    'except' => ['view'],
                    'extraPatterns' => [
                        'GET <id>' => 'get'
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['admin' => 'auth'],
                    'patterns' => [
                        'POST login' => 'login',
                        'POST logout' => 'logout'
                    ],
                ],
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
