Mail Extension for Yii2
============

Installation
------------
In `composer.json`:
```
{
    "require": {
        "rmrevin/yii2-postman": "~2.0"
    }
}
```

configuration
-------------
`/protected/config/main.php`
```php
<?
return [
	// ...
	'components' => [
		// ...
		'postman' => [
			'class' => 'rmrevin\yii\postman\Postman',
				'driver' => 'smtp',
				'default_from' => ['mailer@somehost.com', 'Mailer'],
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
    ->setBodyView('letter-view', [
        'name' => 'Rosy',
        'date' => date('Y-m-d')
    ])
    ->addAddress(['user@somehost.com', 'John Smith'])
    ->addAttachment('/path/to/file.tar.gz');
if(!$Letter->send()){
	echo $Letter->getLastError();
}
```

In cron script:
```php
LetterModel::cron($num_letters_per_step = 10)
```
