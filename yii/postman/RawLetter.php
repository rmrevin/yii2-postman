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
	 * @param string $subject  subject message
	 * @param string $body     text message
	 * @param string $alt_body alternative text message
	 * @param bool   $is_html
	 */
	public function __construct($subject, $body, $alt_body = null, $is_html = true)
	{
		parent::__construct();

		$this->set_data($subject, $body, $alt_body, $is_html);
	}

	/**
	 * @param string $subject  subject message
	 * @param string $body     text message
	 * @param string $alt_body alternative text message
	 * @param bool   $is_html
	 */
	public function set_data($subject, $body, $alt_body = null, $is_html = true)
	{
		$this->_check_mailer();

		$this->_mailer->Subject = $subject;
		$this->_mailer->Body = $body;
		$this->_mailer->AltBody = $alt_body;
		$this->_mailer->IsHTML($is_html);
	}
}