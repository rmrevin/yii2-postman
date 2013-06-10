<?php
/**
 * RawLetter.php
 * @author: Roman Revin <xgismox@gmail.com>
 * @date  : 31.05.13
 */

namespace yii\postman;

/**
 * Class RawLetter
 * @package yii\postman
 */
class RawLetter extends Letter
{

	/**
	 * @param string $subject  a subject of a message
	 * @param string $body     a text of a message
	 */
	public function __construct($subject, $body)
	{
		parent::__construct();

		$this->set_data($subject, $body);
	}

	/**
	 * @param string $subject  a subject of a message
	 * @param string $body     a text of a message
	 */
	public function set_data($subject, $body)
	{
		$this->subject = $subject;
		$this->body = $body;
	}
}