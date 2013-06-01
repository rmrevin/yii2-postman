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

/**
 * Class Letter
 * abstract class that implements the basic functionality of letters;
 *
 * @package yii\postman
 */
abstract class Letter extends Component
{

	/** @var PHPMailer object */
	protected $_mailer = null;

	/** @var Postman object */
	protected $_postman = null;

	/** @var string last error message */
	private $_error = null;

	/** name of before send email event */
	const EVENT_BEFORE_SEND = 'on_before_send';

	/** name of after send email event */
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

	/**
	 * method sets the object "postman"
	 * @param Postman $Postman
	 *
	 * @return $this
	 */
	public function set_postman(Postman $Postman)
	{
		$this->_postman = $Postman;
		$this->_mailer = $Postman->get_clone_mailer_object();

		return $this;
	}

	/**
	 * method sets from whom we received a letter
	 * @param array $from = array('user@somehost.com') || array('user@somehost.com', 'John Smith')
	 *
	 * @return $this
	 */
	public function set_from($from)
	{
		$this->_check_mailer();

		$this->_mailer->SetFrom($from[0], $from[1]);

		return $this;
	}

	/**
	 * method specifies to whom to send reply letter
	 * @param array $reply_to = array('user@somehost.com') || array('user@somehost.com', 'John Smith')
	 *
	 * @return $this
	 */
	public function add_reply_to($reply_to)
	{
		$this->_check_mailer();

		foreach ($reply_to as $address) {
			$address = is_string($address) ? array($address) : $address;
			$this->_mailer->AddAddress($address[0], isset($address[1]) ? $address[1] : '');
		}

		return $this;
	}

	/**
	 * method sets several recipients
	 * @param array $to
	 * @param array $cc
	 * @param array $bcc
	 *
	 * @return $this
	 */
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

	/**
	 * method adds recipient
	 * @param string $address
	 * @param string $name
	 *
	 * @return $this
	 */
	public function add_address($address, $name = '')
	{
		$this->_check_mailer();
		$this->_mailer->AddAddress($address, $name);
		return $this;
	}

	/**
	 * method adds recipient in Cc
	 * @param string $address
	 * @param string $name
	 *
	 * @return $this
	 */
	public function add_cc_address($address, $name = '')
	{
		$this->_check_mailer();
		$this->_mailer->AddCC($address, $name);
		return $this;
	}

	/**
	 * method adds recipient in Bcc
	 * @param string $address
	 * @param string $name
	 *
	 * @return $this
	 */
	public function add_bcc_address($address, $name = '')
	{
		$this->_check_mailer();
		$this->_mailer->AddBCC($address, $name);
		return $this;
	}

	/**
	 * method adds attachment
	 * @param string $path
	 * @param string $name
	 * @param string $encoding
	 * @param string $type
	 *
	 * @return $this
	 */
	public function add_attachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
	{
		$this->_check_mailer();
		$this->_mailer->AddAttachment($path, $name, $encoding, $type);
		return $this;
	}

	/**
	 * method sends a letter
	 *
	 * @return bool
	 */
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

	/**
	 * method gets the message about the last error
	 *
	 * @return null|string
	 */
	public function get_last_error()
	{
		return $this->_error;
	}

	/**
	 * method checks to see if the object is "Postman"
	 *
	 * @return bool
	 * @throws LetterException
	 */
	protected function _check_mailer()
	{
		if (!($this->_mailer instanceof PHPMailer)) {
			throw new LetterException(Yii::t('app', 'First we need to call method "set_postman".'));
		}

		return true;
	}

	/**
	 * Event method before sending
	 */
	public function on_before_send()
	{
		$Event = new Event();
		$Event->sender = $this;
		$this->trigger(self::EVENT_BEFORE_SEND, $Event);
	}

	/**
	 * Event method after sending
	 */
	public function on_after_send()
	{
		$Event = new Event();
		$Event->sender = $this;
		$this->trigger(self::EVENT_AFTER_SEND, $Event);
	}
}