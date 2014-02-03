<?php
/**
 * example.php
 * @author Roman Revin
 * @link http://phptime.ru
 */

namespace yii\postman;

$Letter = new RawLetter('Subject', 'Message body', 'Alternative message body', true);
$Letter
	->add_address('user@somehost.com')
	->add_bcc_address(['tech@somehost.com'])
	->send();

$Letter = new ViewLetter('Subject', 'letter-view', [
	'name' => 'Rosy',
	'date' => date('Y-m-d')
], false);
$Letter
	->add_address(['user@somehost.com', 'John Smith'])
	->add_attachment('/path/to/file.tar.gz')
	->send();