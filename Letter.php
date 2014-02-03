<?php
/**
 * Letter.php
 * @author Roman Revin
 * @link http://phptime.ru
 */

namespace yii\postman;

use PHPMailer;
use yii\base\Component;
use yii\base\Event;
use yii\db\Expression;
use yii\helpers\Json;
use Yii;
use yii\postman\models\LetterModel;

/**
 * Class Letter
 * The abstract class that implements the basic functionality of letters;
 *
 * @package yii\postman
 */
abstract class Letter extends Component
{

	/** @var PHPMailer object */
	protected $_mailer = null;

	/** @var Postman object */
	protected $_postman = null;

	/** @var string a subject */
	protected $subject;

	/** @var string a body of a message */
	protected $body;

	/** @var array recipients */
	protected $recipients = [];

	/** @var array attachments */
	protected $attachments;

	/** @var string a last error of a message */
	private $_error = null;

	/** the name of the event that occurs before sending emails*/
	const EVENT_BEFORE_SEND = 'on_before_send';

	/** the name of the event that occurs after sending emails */
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
	 * the method sets the "postman" object
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
	 * the method sets the "subject" object
	 * @param string $subject
	 * @return $this
	 */
	public function set_subject($subject)
	{
		$this->subject = $subject;

		return $this;
	}

	/**
	 * the method sets the value of the "From" field
	 * @param array $from = ['user@somehost.com'] || ['user@somehost.com', 'John Smith']
	 *
	 * @return $this
	 */
	public function set_from($from)
	{
		$this->recipients['from'] = $from;

		return $this;
	}

	public function get_recipients()
	{
		return $this->recipients;
	}

	public function get_count_recipients()
	{
		$count = 1; // one is "from"
		foreach ($this->recipients as $type => $recipients) {
			if ($type === 'from') {
				continue;
			}
			$count += count($recipients);
		}

		return $count;
	}

	/**
	 * the method sets several recipients
	 * @param array $to
	 * @param array $cc
	 * @param array $bcc
	 * @param array $reply_to
	 *
	 * @deprecated
	 *
	 * @return $this
	 */
	public function add_address_list($to = [], $cc = [], $bcc = [], $reply_to = [])
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
	 * the method adds a recipient
	 * @param array $address = ['user@somehost.com'] || ['user@somehost.com', 'John Smith']
	 *
	 * @return $this
	 */
	public function add_address($address)
	{
		$args = func_get_args();
		foreach ($args as $address) {
			$this->_add_addr('to', $address);
		}

		return $this;
	}

	/**
	 * the method adds a recipient to Cc
	 * @param array $address = ['user@somehost.com'] || ['user@somehost.com', 'John Smith']
	 *
	 * @return $this
	 */
	public function add_cc_address($address)
	{
		$args = func_get_args();
		foreach ($args as $address) {
			$this->_add_addr('cc', $address);
		}

		return $this;
	}

	/**
	 * the method adds a recipient to Bcc
	 * @param array $address = ['user@somehost.com'] || ['user@somehost.com', 'John Smith']
	 *
	 * @return $this
	 */
	public function add_bcc_address($address)
	{
		$args = func_get_args();
		foreach ($args as $address) {
			$this->_add_addr('bcc', $address);
		}

		return $this;
	}

	/**
	 * the method adds a "Reply-to" address
	 * @param array $address = ['user@somehost.com'] || ['user@somehost.com', 'John Smith']
	 *
	 * @return $this
	 */
	public function add_reply_to($address)
	{
		$args = func_get_args();
		foreach ($args as $address) {
			$this->_add_addr('reply', $address);
		}

		return $this;
	}

	/**
	 * the method adds a recipient by type
	 * @param $type
	 * @param $address
	 *
	 * @return $this
	 */
	private function _add_addr($type, $address)
	{
		$address = !is_array($address) ? [$address] : $address;
		if (!isset($this->recipients[$type])) {
			$this->recipients[$type] = [];
		}
		$this->recipients[$type][] = $address;

		return $this;
	}

	/**
	 * the method adds an attachment
	 * @param string $path
	 * @param string $name
	 * @param string $encoding
	 * @param string $type
	 *
	 * @return $this
	 */
	public function add_attachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
	{
		$this->attachments[] = [
			'path' => $path,
			'name' => $name,
			'encoding' => $encoding,
			'type' => $type
		];

		return $this;
	}

	public function get_attachments()
	{
		return $this->attachments;
	}

	/**
	 * the method sends a letter
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

		$this->_error = $LetterModel->get_last_error();

		$this->on_after_send();

		return $result === true ? (int)$LetterModel->id : false;
	}

	/**
	 * the method gets the message about the last error
	 *
	 * @return null|string
	 */
	public function get_last_error()
	{
		return $this->_error;
	}

	public function get_postman()
	{
		return $this->_postman;
	}

	/**
	 * the method converts the letter data to the letter model
	 *
	 * @return LetterModel
	 */
	private function _data_to_model()
	{
		$LetterModel = new LetterModel();
		$LetterModel->recipients = Json::encode($this->recipients);
		$LetterModel->subject = $this->subject;
		$LetterModel->body = $this->body;
		$LetterModel->attachments = Json::encode($this->attachments);

		return $LetterModel;
	}

	/**
	 * the "before send" event method
	 */
	public function on_before_send()
	{
		$Event = new Event();
		$Event->sender = $this;
		$this->trigger(self::EVENT_BEFORE_SEND, $Event);
	}

	/**
	 * the "after send" event method
	 */
	public function on_after_send()
	{
		$Event = new Event();
		$Event->sender = $this;
		$this->trigger(self::EVENT_AFTER_SEND, $Event);
	}
}