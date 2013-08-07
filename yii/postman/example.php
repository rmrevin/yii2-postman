<?php
/**
 * example.php
 * @author: Roman Revin <xgismox@gmail.com>
 * @date  : 01.06.2013
 */

namespace yii\postman;

$Letter = new RawLetter('Subject', 'Message body', 'Alternative message body', true);
$Letter->add_address_list(
// main
	array(
		array('user@somehost.com', 'John Smith'),
		array('user2@somehost.com', 'Mary Jane'),
	),
	// cc
	array(),
	// bcc
	array(
		array('tech@somehost.com')
	),
	// reply_to
	array(
		array('abuse@somehost.com')
	)
)->send();


$Letter = new ViewLetter('Subject', 'letter-view', array(
	'name' => 'Rosy',
	'date' => date('Y-m-d')
), false);
$Letter
	->add_address(array('user@somehost.com', 'John Smith'))
	->add_attachment('/path/to/file.tar.gz')
	->send();