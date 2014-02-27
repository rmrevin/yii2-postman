Mail Extension for Yii2
============

Installation
------------
In `composer.json`:
```
{
    "require": {
        "rmrevin/yii2-postman": "1.3.*"
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
				'default_from' => ['track@rmrevin.ru', 'Mailer'],
				'table' => 'tbl_letters',
				'view_path' => '/email',
				'smtp_config' => [
					'host' => 'smtp.domain.cpom',
					'port' => 25,
					'auth' => true,
					'user' => 'email@domain.cpom',
					'password' => 'password',
					'secure' => false,
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
$Letter = new \rmrevin\yii\postman\RawLetter('Subject', 'Body message', 'Alternative body message');
$Letter
	->add_address('user@somehost.com', 'User name')
	->add_cc_address('user2@somehost.com', 'CC user name')
	->add_attachment('/path/to/file.tar.gz', 'File name');
if(!$Letter->send()){
	echo $Letter->get_last_error();
}

// path to view algorithm:
// Yii::app()->getViewPath() . Postman::$default_view_path . '/' . 'message-view.php'
// path to view: /protected/views/email/message-view.php
$Letter = new \rmrevin\yii\postman\ViewLetter('Subject', 'message-view', array('url'=>'http://...'));
$Letter
	->add_address(array('user@somehost.com', 'John Smith'))
	->add_attachment('/path/to/file.tar.gz');
if(!$Letter->send()){
	echo $Letter->get_last_error();
}
```

In cron script:
```php
LetterModel::cron($num_letters_per_step = 10)
```
