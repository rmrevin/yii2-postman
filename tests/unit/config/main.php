<?php
/**
 * main.php
 * @author Roman Revin http://phptime.ru
 */

return [
    'id' => 'testapp',
    'basePath' => realpath(__DIR__ . '/..'),
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=yii2postman',
            'username' => 'root',
            'password' => '',
        ],
        'postman' => [
            'class' => \rmrevin\yii\postman\Component::className(),
            'driver' => 'sendmail',
            'default_from' => ['no-reply@localhost', 'Mailer'],
            'table' => '{{%letters_test}}',
            'view_path' => '/email',
            'smtp_config' => [
                'host' => 'smtp.domain.com',
                'port' => 25,
                'auth' => true,
                'user' => 'email@domain.com',
                'password' => 'password',
                'secure' => false,
                'debug' => false,
            ],
        ]
    ],
    'params' => [
        /** In the file "main-local.php", you can override the "demo_email" for testing letters on real email address. */
//		'demo_email' => ['you_real_email@example.com', 'Your Name'],
    ]
];