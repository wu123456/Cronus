<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    // require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    // require(__DIR__ . '/params-local.php'),
    require(__DIR__ . '/../../environments/' . YII_ENV . '/common/config/params-local.php')
);

return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => $params,
];
