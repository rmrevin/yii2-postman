<?php
/**
 * main.php
 * @author Roman Revin
 * @link http://phptime.ru
 */

use rmrevin\yii\postman\Component;

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
			'class' => Component::className(),
			'driver' => 'sendmail',
			'default_from' => ['no-reply@localhost', 'Mailer'],
			'table' => 'tbl_letters_test',
			'view_path' => '/email',
		]
	],
	'params' => [
		/** In the file "main-local.php", you can override the "demo_email" for testing letters on real email address. */
//		'demo_email' => ['you_real_email@example.com', 'Your Name'],
		/** In the file "main-local.php", you can override the "default_from" for testing smtp. */
//		'default_from' => ['no-reply@localhost', 'Mailer'],
		'smtp' => [
			'host' => 'smtp.domain.com',
			'port' => 25,
			'auth' => true,
			'user' => 'email@domain.com',
			'password' => 'password',
			'secure' => false,
			'debug' => false,
		]
	]
];