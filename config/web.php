<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => 'pigs/index',
    'timeZone' => 'UTC',
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
                    'patterns' => [
                        'GET randomize/<typeId:[1-2]>' => 'randomize',
                        'GET type/<type_id:[1-2]>' => 'index',
                        'GET tag/<tag:\w+>' => 'find-by-tag',
                        'GET <id>' => 'get',
                        'POST type/<type_id:\d+>' => 'create',
                        'PATCH <id>' => 'update',
                        'DELETE <id>' => 'delete'
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'pigs',
                    'extraPatterns' => [
                        'GET random/<number:\d+>' => 'randomize',
                        'GET random/<number:\d+>/<graduated:\w+>' => 'randomize',
                        'GET count/<statusId:\d+>' => 'count',
                        'GET <id:\d+>' => 'get',
                        'GET <graduated:\w+>' => 'index',
                        'PATCH graduate/<id>/<typeId:\d+>' => 'graduate',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['requests' => 'turn-in'],
                    'patterns' => [
                        'GET' => 'index',
                        'GET <id>' => 'get',
                        'POST turnin' => 'create',
                        'DELETE <id>' => 'delete'
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['admin' => 'auth'],
                    'patterns' => [
                        'POST login' => 'login',
                        'POST logout' => 'logout',
                        'HEAD' => 'check'
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['city'],
                    'patterns' => [
                        'GET' => 'index',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['overseer'],
                    'patterns' => [
                        'GET' => 'index',
                        'GET <active:\w+>' => 'index',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['status'],
                    'patterns' => [
                        'GET' => 'index',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['food' => 'food-category'],
                    'patterns' => [
                        'GET' => 'index',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['queries' => 'food-query'],
                    'patterns' => [
                        'GET <filter:\w+>/<sort:\w+>' => 'index',
                    ],
                    'ruleConfig' => [
                        'class' => 'yii\web\UrlRule',
                        'defaults' => [
                            'filter' => 'all',
                            'sort' => 'date',
                        ]
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['product' => 'food-product'],
                    'patterns' => [
                        'GET random' => 'randomize',
                        'GET' => 'index',
                        'GET <id>' => 'get',
                        'POST' => 'create',
                        'PATCH <id>' => 'update',
                        'DELETE <id>' => 'delete',
                        'GET search/<type:\d+>/<query:[\w+\s+]+>' => 'search',
                    ],
                    'ruleConfig' => [
                        'class' => 'yii\web\UrlRule',
                        'defaults' => [
                            'type' => 0,
                            'query' => '',
                        ]
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
