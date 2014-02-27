<?php
/**
 * LetterModel.php
 * @author Roman Revin
 * @link http://phptime.ru
 */

namespace rmrevin\yii\postman\models;

use PHPMailer;
use rmrevin\yii\postman\Component;
use rmrevin\yii\postman\LetterException;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * Class LetterModel
 * @package rmrevin\yii\postman\models
 *
 * @property integer $id         ;
 * @property string  $date_create;
 * @property string  $date_send  ;
 * @property string  $from       ;
 * @property string  $recipients ;
 * @property string  $subject    ;
 * @property string  $body       ;
 * @property string  $attachments;
 * @property boolean $is_html
 */
class LetterModel extends ActiveRecord
{

	/** @var PHPMailer */
	private $_mailer = null;

	/** @var string a last error message */
	private $_error = null;

	/** the name of the event that occurs before sending emails */
	const EVENT_BEFORE_SEND = 'on_before_send';

	/** the name of the event that occurs after sending emails */
	const EVENT_AFTER_SEND = 'on_after_send';

	public function init()
	{
		parent::init();
		$this->on(self::EVENT_BEFORE_INSERT, [$this, 'on_b_insert_set_date']);
	}

	public function rules()
	{
		return [
			['date_create', 'filter', 'filter' => 'trim'],
			['date_send', 'filter', 'filter' => 'trim'],

			['subject', 'filter', 'filter' => 'trim'],
			['subject', 'required'],
			['subject', 'string', 'max' => 5000],

			['body', 'filter', 'filter' => 'trim'],
			['body', 'required'],
			['body', 'string', 'max' => 5000],

			['recipients', 'filter', 'filter' => 'trim'],
			['recipients', 'required'],
			['recipients', 'string', 'max' => 5000],

			['attachments', 'string', 'max' => 5000],
			['attachments', 'filter', 'filter' => 'trim'],
		];
	}

	public function get_mailer()
	{
		return $this->_mailer;
	}

	public function set_mailer(PHPMailer $mailer)
	{
		if (!$this->getIsNewRecord()) {
			$mailer->Subject = $this->subject;
			$mailer->Body = $this->body;
			$mailer->IsHTML(true);

			$recipients = Json::decode($this->recipients);

			$mailer->SetFrom($recipients['from'][0], isset($recipients['from'][1]) ? $recipients['from'][1] : '');
			$mailer->Sender = $recipients['from'][0];
			if (isset($recipients['to'])) {
				foreach ($recipients['to'] as $address) {
					$address = is_string($address) ? [$address] : $address;
					$mailer->AddAddress($address[0], isset($address[1]) ? $address[1] : '');
				}
			}
			if (isset($recipients['cc'])) {
				foreach ($recipients['cc'] as $address) {
					$address = is_string($address) ? [$address] : $address;
					$mailer->AddCC($address[0], isset($address[1]) ? $address[1] : '');
				}
			}
			if (isset($recipients['bcc'])) {
				foreach ($recipients['bcc'] as $address) {
					$address = is_string($address) ? [$address] : $address;
					$mailer->AddBCC($address[0], isset($address[1]) ? $address[1] : '');
				}
			}
			if (isset($recipients['reply'])) {
				foreach ($recipients['reply'] as $address) {
					$address = is_string($address) ? [$address] : $address;
					$mailer->AddReplyTo($address[0], isset($address[1]) ? $address[1] : '');
				}
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
	 * the method sends a letter
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
			$this->update(false, ['date_send']);
		}

		$this->on_after_send();

		return $result;
	}

	public function attributeLabels()
	{
		return [
			'id' => \Yii::t('app', 'ID'),
			'date_create' => \Yii::t('app', 'Date create'),
			'date_send' => \Yii::t('app', 'Date send'),
			'from' => \Yii::t('app', 'From'),
			'recipients' => \Yii::t('app', 'Recipients'),
			'subject' => \Yii::t('app', 'Subject'),
			'body' => \Yii::t('app', 'Body message'),
			'attachments' => \Yii::t('app', 'Attachments'),
			'is_html' => \Yii::t('app', 'Is HTML'),
		];
	}

	public static function tableName()
	{
		return \Yii::$app->getComponent('postman')->table;
	}

	/**
	 * the method checks if the object is "PHPMailer"
	 *
	 * @return bool
	 * @throws LetterException
	 */
	protected function _check_mailer()
	{
		if (!($this->_mailer instanceof PHPMailer)) {
			throw new LetterException(\Yii::t('app', 'PHPMailer object not initialize.'));
		}

		return true;
	}

	/**
	 * @param int $num_letters_per_step
	 * @return int
	 */
	public static function cron($num_letters_per_step = 10)
	{
		$send = 0;

		/** @var Component $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		/** @var LetterModel[] $LetterModels */
		$LetterModels = self::find()
			->where('[[date_send]] = :date OR [[date_send]] IS NULL', [':date' => '0000-00-00 00:00:00'])
			->orderBy(['id' => SORT_ASC])
			->limit($num_letters_per_step)
			->all();
		foreach ($LetterModels as $LetterModel) {
			$LetterModel->set_mailer($Postman->get_clone_mailer_object());
			$LetterModel->send_immediately();
			$err = $LetterModel->get_last_error();
			if (!empty($err)) {
				echo $err . "\n";
			} else {
				$send++;
			}
		}

		return $send;
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

	public function on_b_insert_set_date()
	{
		$this->date_create = new Expression('NOW()');
	}
}