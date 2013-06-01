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
use yii\db\Expression;
use yii\helpers\Json;
use yii\postman\models\LetterModel;

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

	/** @var string subject */
	protected $subject;

	/** @var string body message */
	protected $body;

	/** @var string alternative body message */
	protected $alt_body;

	/** @var array recepients */
	protected $recipients = array(
		'from' => array(),
		'to' => array(),
		'cc' => array(),
		'bcc' => array(),
		'reply' => array(),
	);

	/** @var array attachments */
	protected $attachments;

	/** @var bool is_html */
	protected $is_html = true;

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
		$this->set_from($Postman->default_from);

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
		$this->recipients['from'] = $from;

		return $this;
	}

	/**
	 * method sets several recipients
	 * @param array $to
	 * @param array $cc
	 * @param array $bcc
	 * @param array $reply_to
	 *
	 * @return $this
	 */
	public function add_address_list($to = array(), $cc = array(), $bcc = array(), $reply_to = array())
	{
		foreach ($to as $address) {
			$this->add_address($address);
		}
		foreach ($cc as $address) {
			$this->add_cc_address($address);
		}
		foreach ($bcc as $address) {
			$this->add_bcc_address($address);
		}
		foreach ($reply_to as $address) {
			$this->add_reply_to($address);
		}

		return $this;
	}

	/**
	 * method adds recipient
	 * @param array $address
	 *
	 * @return $this
	 */
	public function add_address($address)
	{
		return $this->_add_addr('to', $address);
	}

	/**
	 * method adds recipient in Cc
	 * @param array $address
	 *
	 * @return $this
	 */
	public function add_cc_address($address)
	{
		return $this->_add_addr('cc', $address);
	}

	/**
	 * method adds recipient in Bcc
	 * @param array $address
	 *
	 * @return $this
	 */
	public function add_bcc_address($address)
	{
		return $this->_add_addr('bcc', $address);
	}

	/**
	 * method specifies to whom to send reply letter
	 * @param array $address = array('user@somehost.com') || array('user@somehost.com', 'John Smith')
	 *
	 * @return $this
	 */
	public function add_reply_to($address)
	{
		return $this->_add_addr('reply', $address);
	}

	/**
	 * method adds recipient by type
	 * @param $type
	 * @param $address
	 *
	 * @return $this
	 */
	private function _add_addr($type, $address)
	{
		$address = !is_array($address) ? array($address) : $address;
		if (!isset($this->recipients[$type])) {
			$this->recipients[$type] = array();
		}
		$this->recipients[$type][] = $address;
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
		$this->attachments[] = array(
			'path' => $path,
			'name' => $name,
			'encoding' => $encoding,
			'type' => $type
		);
		return $this;
	}

	/**
	 * method sends a letter
	 * @param bool $immediately
	 *
	 * @return bool
	 */
	public function send($immediately = false)
	{
		$this->on_before_send();

		$LetterModel = $this->_data_to_model();
		$LetterModel->date_create = new Expression('NOW()');
		$result = $LetterModel->save();

		if ($immediately === true) {
			$LetterModel
				->set_mailer($this->_postman->get_clone_mailer_object())
				->send_immediately();
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
	 * method convert letter data to letter model
	 *
	 * @return LetterModel
	 */
	private function _data_to_model()
	{
		$LetterModel = new LetterModel();
		$LetterModel->recipients = Json::encode($this->recipients);
		$LetterModel->subject = $this->subject;
		$LetterModel->body = $this->body;
		$LetterModel->alt_body = $this->alt_body;
		$LetterModel->attachments = Json::encode($this->attachments);
		$LetterModel->is_html = $this->is_html === true ? 1 : 0;

		return $LetterModel;
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