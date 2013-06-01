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
 *
 * @property integer $id         ;
 * @property string  $date_create;
 * @property string  $date_send  ;
 * @property string  $from       ;
 * @property string  $recipients ;
 * @property string  $subject    ;
 * @property string  $body       ;
 * @property string  $alt_body   ;
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

	public function rules()
	{
		return array();
	}

	public function set_mailer(PHPMailer $mailer)
	{
		if (!$this->getIsNewRecord()) {
			$mailer->Subject = $this->subject;
			$mailer->Body = $this->body;
			$mailer->AltBody = $this->alt_body;
			$mailer->IsHTML(true);

			$recipients = Json::decode($this->recipients);
			$mailer->SetFrom($recipients['from'][0], isset($recipients['from'][1]) ? $recipients['from'][1] : '');
			foreach ($recipients['to'] as $address) {
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
			'recipients' => Yii::t('app', 'Recipients'),
			'subject' => Yii::t('app', 'Subject'),
			'body' => Yii::t('app', 'Body message'),
			'alt_body' => Yii::t('app', 'Alternative body message'),
			'attachments' => Yii::t('app', 'Attachments'),
			'is_html' => Yii::t('app', 'Is HTML'),
		);
	}

	public static function tableName()
	{
		return '{{%letter}}';
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
			throw new LetterException(Yii::t('app', 'PHPMailer object not initialize.'));
		}

		return true;
	}

	public static function cron($num_letters_per_step = 10)
	{
		/** @var \yii\postman\Postman $Postman */
		$Postman = Yii::$app->getComponent('postman');
		/** @var LetterModel[] $LetterModels */
		$LetterModels = self::find()
			->where('date_send IS NULL')
			->orderBy('id ASC')
			->limit($num_letters_per_step)
			->all();
		foreach ($LetterModels as $LetterModel) {
			$LetterModel->set_mailer($Postman->get_clone_mailer_object());
			$LetterModel->send_immediately();
		}
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
}