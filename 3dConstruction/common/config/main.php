<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DBManager',
            'defaultRoles' => ['user', 'admin', 'engineer', 'staff'],
        ],
    ],
];
