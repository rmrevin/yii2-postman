<?php
/**
 * Letter.php
 * @author: Roman Revin <xgismox@gmail.com>
 * @date  : 31.05.13
 */

namespace yii\postman;

use Yii;
use PHPMailer;
use yii\base\Component;
use yii\base\Event;

abstract class Letter extends Component
{

	/** @var PHPMailer */
	protected $_mailer = null;

	/** @var Postman */
	protected $_postman = null;

	private $_error = null;

	const EVENT_BEFORE_SEND = 'on_before_send';
	const EVENT_AFTER_SEND = 'on_after_send';

	public function __construct()
	{
		if (!Yii::$app->hasComponent('postman')) {
			throw new LetterException(Yii::t('app', 'You need to configure the component "Postman".'));
		}

		/** @var Postman $Postman */
		$Postman = Yii::$app->getComponent('postman');
		$this->set_postman($Postman);
	}

	public function set_postman(Postman $Postman)
	{
		$this->_postman = $Postman;
		$this->_mailer = $Postman->get_clone_mailer_object();

		return $this;
	}

	public function set_from($from)
	{
		$this->_check_mailer();

		$this->_mailer->SetFrom($from[0], $from[1]);

		return $this;
	}

	public function add_reply_to($reply_to)
	{
		$this->_check_mailer();

		foreach ($reply_to as $address) {
			$address = is_string($address) ? array($address) : $address;
			$this->_mailer->AddAddress($address[0], isset($address[1]) ? $address[1] : '');
		}

		return $this;
	}

	public function add_address_list($to = array(), $cc = array(), $bcc = array())
	{
		$this->_check_mailer();

		foreach ($to as $address) {
			$address = is_string($address) ? array($address) : $address;
			$this->add_address($address[0], isset($address[1]) ? $address[1] : '');
		}

		foreach ($cc as $address) {
			$address = is_string($address) ? array($address) : $address;
			$this->add_cc_address($address[0], isset($address[1]) ? $address[1] : '');
		}

		foreach ($bcc as $address) {
			$address = is_string($address) ? array($address) : $address;
			$this->add_bcc_address($address[0], isset($address[1]) ? $address[1] : '');
		}

		return $this;
	}

	public function add_address($address, $name = '')
	{
		$this->_check_mailer();
		$this->_mailer->AddAddress($address, $name);
		return $this;
	}

	public function add_cc_address($address, $name = '')
	{
		$this->_check_mailer();
		$this->_mailer->AddCC($address, $name);
		return $this;
	}

	public function add_bcc_address($address, $name = '')
	{
		$this->_check_mailer();
		$this->_mailer->AddBCC($address, $name);
		return $this;
	}

	public function add_attachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
	{
		$this->_check_mailer();
		$this->_mailer->AddAttachment($path, $name, $encoding, $type);
		return $this;
	}

	public function send()
	{
		$this->_check_mailer();

		$this->on_before_send();

		$result = $this->_mailer->Send();
		if ($result === false) {
			$this->_error = $this->_mailer->ErrorInfo;
		}

		$this->on_after_send();

		return $result;
	}

	public function get_last_error()
	{
		return $this->_error;
	}

	protected function _check_mailer()
	{
		if (!($this->_mailer instanceof PHPMailer)) {
			throw new LetterException(Yii::t('app', 'First we need to call method "set_postman".'));
		}

		return true;
	}

	public function on_before_send()
	{
		$Event = new Event();
		$Event->sender = $this;
		$this->trigger(self::EVENT_BEFORE_SEND, $Event);
	}

	public function on_after_send()
	{
		$Event = new Event();
		$Event->sender = $this;
		$this->trigger(self::EVENT_AFTER_SEND, $Event);
	}
}