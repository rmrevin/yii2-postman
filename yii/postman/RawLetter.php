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
	 * @param string $alt_body an alternative text of a message
	 * @param bool   $is_html
	 */
	public function __construct($subject, $body, $alt_body = null, $is_html = true)
	{
		parent::__construct();

		$this->set_data($subject, $body, $alt_body, $is_html);
	}

	/**
	 * @param string $subject  a subject of a message
	 * @param string $body     a text of a message
	 * @param string $alt_body an alternative text of a message
	 * @param bool   $is_html
	 */
	public function set_data($subject, $body, $alt_body = null, $is_html = true)
	{
		$this->subject = $subject;
		$this->body = $body;
		$this->alt_body = $alt_body;
		$this->is_html = $is_html;
	}
}