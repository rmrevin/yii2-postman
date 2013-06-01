Mail Extension for Yii2
============

Configuration
-------------
`/protected/config/main.php`
```php
<?
return array(
	// ...
	'components' => array(
		// ...
		'postman' => array(
			'class' => 'yii\postman\Postman',
				'driver' => 'smtp',
				'default_from' => array('track@rmrevin.ru', 'Mailer'),
				'table' => 'tbl_letters',
				'view_path' => '/email',
				'smtp_config' => array(
					'host' => 'smtp.yandex.ru',
					'port' => 25,
					'auth' => true,
					'user' => 'track@rmrevin.ru',
					'password' => 'uA8qbaMDzFjDzu6gcn3p',
					'secure' => false,
					'debug' => false,
				)
		),
	),
	// ...
);
```

Usage
-----
```php
<?
// ...
$Letter = new \yii\postman\RawLetter('Subject', 'Body message', 'Alternative body message');
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
$Letter = new \yii\postman\ViewLetter('Subject', 'message-view', array('url'=>'http://...'));
$Letter->add_address_list(
	// recipients
	array(
		array('user1@somehost.com', 'User 1 name'),
		array('user2@somehost.com', 'User 2 name'),
		array('user3@somehost.com', 'User 3 name'),
	),
	// cc recipients
	array(
	   	array('cc-user1@somehost.com', 'User 1 name'),
	   	array('cc-user2@somehost.com', 'User 2 name'),
	   	array('cc-user3@somehost.com', 'User 3 name'),
	   ),
	// bcc recipients
	array(
		array('bcc-user1@somehost.com', 'User 1 name'),
		array('bcc-user2@somehost.com', 'User 2 name'),
		array('bcc-user3@somehost.com', 'User 3 name'),
	),
	// reply to
	array(
		array('abuse@somehost.com'),
	)
);

if(!$Letter->send()){
	echo $Letter->get_last_error();
}
```

In cron script:
```php
LetterMode::cron($num_letters_per_step = 10)
```
