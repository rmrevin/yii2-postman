<?php
/**
 * RawLetter.php
 * @author: Roman Revin <roma@quetzal.ru>
 * @date  : 31.05.13
 */

namespace yii\postman;

class RawLetter extends Letter
{

	public function __construct($subject, $body, $alt_body = null, $is_html = true)
	{
		parent::__construct();

		$this->set_data($subject, $body, $alt_body, $is_html);
	}

	public function set_data($subject, $body, $alt_body = null, $is_html = true)
	{
		$this->_check_mailer();

		$this->_mailer->Subject = $subject;
		$this->_mailer->Body = $body;
		$this->_mailer->AltBody = $alt_body;
		$this->_mailer->IsHTML($is_html);
	}
}