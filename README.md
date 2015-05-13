Mail Extension for Yii2
============

Installation
------------
In `composer.json`:
```
{
    "require": {
        "rmrevin/yii2-postman": "~2.2"
    }
}
```


configuration
-------------
`/config/web.php`
```php
<?
return [
	// ...
	'components' => [
		// ...
		'postman' => [
			'class' => 'rmrevin\yii\postman\Component',
				'driver' => 'smtp',
				'default_from' => ['mailer@somehost.com', 'Mailer'],
				'subject_prefix' => 'Sitename / ',
				'subject_suffix' => null,
				'table' => '{{%postman_letter}}',
				'view_path' => '/email',
				'smtp_config' => [
					'host' => 'smtp.domain.cpom',
					'port' => 465,
					'auth' => true,
					'user' => 'email@domain.cpom',
					'password' => 'password',
					'secure' => 'ssl',
					'debug' => false,
				]
		],
	],
	// ...
];
```
Updating database schema
-------------
After you downloaded and configured yii2-postman, the last thing you need to do is updating your database schema by applying the migrations:

In `/config/console.php`:
```php
<?
return [
	// ...
	'components' => [
		// ...
		'postman' => [
			'class' => 'rmrevin\yii\postman\Component',
		],
	],
	// ...
];
```
In `Command line`:
```
php yii migrate/up --migrationPath=@vendor/rmrevin/yii2-postman/migrations/
```

Usage
-----
```php
<?
// ...
(new \rmrevin\yii\postman\RawLetter())
    ->setSubject('Subject')
    ->setBody('Message body')
    ->addAddress('user@somehost.com')
    ->addBccAddress(['tech@somehost.com']);
if(!$Letter->send()){
	echo $Letter->getLastError();
}

// path to view algorithm:
// Yii::$app->controller->module->getViewPath() . Postman::$view_path . '/' . 'message-view.php'
// path to view: /protected/views/email/message-view.php
(new \rmrevin\yii\postman\ViewLetter)
    ->setSubject('Subject')
    ->setBodyFromView('letter-view', [
        'name' => 'Rosy',
        'date' => date('Y-m-d')
    ])
    ->addAddress(['user@somehost.com', 'John Smith'])
    ->addAttachment('/path/to/file.tar.gz');
if(!$Letter->send()){
	echo $Letter->getLastError();
}
```

Cron
----
In cron script:
```php
LetterModel::cron($num_letters_per_step = 10)
```
