<?php
/**
 * LetterModel.php
 * @author: Roman Revin <xgismox@gmail.com>
 * @date  : 01.06.13
 */

namespace yii\postman\models;

use Yii;
use PHPMailer;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Json;
use yii\postman\LetterException;

/**
 * Class LetterModel
 * @package yii\postman\models
 */
class LetterModel extends ActiveRecord
{

	public $id;
	public $date_create;
	public $date_send;
	public $from;
	public $reply_to;
	public $recipients;
	public $subject;
	public $body;
	public $alt_body;
	public $attachments;

	/** @var PHPMailer */
	private $_mailer = null;

	/** @var string last error message */
	private $_error = null;

	/** name of before send email event */
	const EVENT_BEFORE_SEND = 'on_before_send';

	/** name of after send email event */
	const EVENT_AFTER_SEND = 'on_after_send';

	public function rules()
	{
		return array();
	}

	public function set_mailer(PHPMailer $mailer)
	{
		if (!$this->getIsNewRecord()) {
			$from = Json::decode($this->from);
			$mailer->SetFrom($from[0], isset($from[1]) ? $from[1] : '');

			$mailer->Subject = $this->subject;
			$mailer->Body = $this->body;
			$mailer->AltBody = $this->alt_body;

			$recipients = Json::decode($this->recipients);
			foreach ($recipients['main'] as $address) {
				$address = is_string($address) ? array($address) : $address;
				$mailer->AddAddress($address[0], isset($address[1]) ? $address[1] : '');
			}
			foreach ($recipients['cc'] as $address) {
				$address = is_string($address) ? array($address) : $address;
				$mailer->AddCC($address[0], isset($address[1]) ? $address[1] : '');
			}
			foreach ($recipients['bcc'] as $address) {
				$address = is_string($address) ? array($address) : $address;
				$mailer->AddBCC($address[0], isset($address[1]) ? $address[1] : '');
			}
			foreach ($recipients['reply'] as $address) {
				$address = is_string($address) ? array($address) : $address;
				$mailer->AddReplyTo($address[0], isset($address[1]) ? $address[1] : '');
			}

			$attachments = Json::decode($this->attachments);
			if (is_array($attachments)) {
				foreach ($attachments as $attachment) {
					$mailer->AddAttachment(
						$attachment['path'],
						$attachment['name'],
						$attachment['encoding'],
						$attachment['type']
					);
				}
			}
		}

		$this->_mailer = $mailer;

		return $this;
	}

	/**
	 * method sends a letter
	 *
	 * @return bool
	 */
	public function send_immediately()
	{
		$this->_check_mailer();

		$this->on_before_send();

		$result = $this->_mailer->Send();
		if ($result === false) {
			$this->_error = $this->_mailer->ErrorInfo;
		} else {
			$this->date_send = new Expression('NOW()');
			$this->update(false, array('date_send'));
		}

		$this->on_after_send();

		return $result;
	}

	public function attributeLabels()
	{
		return array(
			'id' => Yii::t('app', 'ID'),
			'date_create' => Yii::t('app', 'Date create'),
			'date_send' => Yii::t('app', 'Date send'),
			'from' => Yii::t('app', 'From'),
			'reply_to' => Yii::t('app', 'Reply to'),
			'recipients' => Yii::t('app', 'Recipients'),
			'subject' => Yii::t('app', 'Subject'),
			'body' => Yii::t('app', 'Body message'),
			'alt_body' => Yii::t('app', 'Alternative body message'),
			'attachments' => Yii::t('app', 'Attachments'),
		);
	}

	public static function tableName()
	{
		return '{{%letter}}';
	}

	/**
	 * method checks to see if the object is "PHPMailer"
	 *
	 * @return bool
	 * @throws LetterException
	 */
	protected function _check_mailer()
	{
		if (!($this->_mailer instanceof PHPMailer)) {
			throw new LetterException(Yii::t('app', 'PHPMailer object not initialize.'));
		}

		return true;
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