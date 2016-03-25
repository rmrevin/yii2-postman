<?php
/**
 * LetterModel.php
 * @author Roman Revin http://phptime.ru
 */

namespace rmrevin\yii\postman\models;

use PHPMailer;
use rmrevin\yii\postman\Component;
use yii\helpers\Json;

/**
 * Class LetterModel
 * @package rmrevin\yii\postman\models
 *
 * @property integer $id
 * @property string $code
 * @property string $date_create
 * @property string $date_send
 * @property string $from
 * @property string $recipients
 * @property string $subject
 * @property string $body
 * @property string $attachments
 */
class LetterModel extends \yii\db\ActiveRecord
{

    /** @var PHPMailer */
    private $_mailer = null;

    /** @var string a last error message */
    private $_error = null;

    /** the name of the event that occurs before sending emails */
    const EVENT_BEFORE_SEND = 'beforeSend';

    /** the name of the event that occurs after sending emails */
    const EVENT_AFTER_SEND = 'afterSend';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_BEFORE_INSERT, function (\yii\base\ModelEvent $Event) {
            $Event->sender->code = empty($Event->sender->code) ? \Yii::$app->getSecurity()->generateRandomString() : $Event->sender->code;
            $Event->sender->date_create = new \yii\db\Expression('NOW()');
        });
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'subject', 'body', 'recipients'], 'required'],
            [['code', 'date_create', 'date_send', 'subject', 'body', 'recipients', 'attachments'], 'filter', 'filter' => 'trim'],
            [['code', 'subject', 'body', 'recipients', 'attachments'], 'string'],
        ];
    }

    /**
     * @return \PHPMailer
     */
    public function getMailer()
    {
        return $this->_mailer;
    }

    /**
     * @param \PHPMailer $mailer
     * @return self
     * @throws \Exception
     * @throws \phpmailerException
     */
    public function setMailer(PHPMailer $mailer)
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
    public function sendImmediately()
    {
        $this->_checkMailer();

        $this->beforeSend();

        $result = $this->_mailer->Send();
        if ($result === false) {
            $this->_error = $this->_mailer->ErrorInfo;
        } else {
            $this->date_send = new \yii\db\Expression('NOW()');
            $this->update(false, ['date_send']);
        }

        $this->afterSend();

        return $result;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Component::get()->table;
    }

    /**
     * the method checks if the object is "PHPMailer"
     *
     * @return bool
     * @throws \rmrevin\yii\postman\LetterException
     */
    protected function _checkMailer()
    {
        if (!($this->_mailer instanceof PHPMailer)) {
            throw new \rmrevin\yii\postman\LetterException(\Yii::t('app', 'PHPMailer object not initialize.'));
        }

        return true;
    }

    /**
     * @param int $num_letters_per_step
     * @return int
     * @throws \yii\base\Exception
     */
    public static function cron($num_letters_per_step = 10)
    {
        $send = 0;

        $Postman = Component::get();

        /** @var static[] $LetterModels */
        $LetterModels = static::find()
            ->onlyNotSend()
            ->orderBy(['id' => SORT_ASC])
            ->limit($num_letters_per_step)
            ->all();

        foreach ($LetterModels as $LetterModel) {
            $LetterModel->setMailer($Postman->getCloneMailerObject());
            $LetterModel->sendImmediately();
            $err = $LetterModel->getLastError();
            if (!empty($err)) {
                throw new \yii\base\Exception($err);
            } else {
                $send++;
            }
        }

        return $send;
    }

    /**
     * @return queries\LetterQuery
     */
    public static function find()
    {
        return new queries\LetterQuery(get_called_class());
    }

    /**
     * the method gets the message about the last error
     *
     * @return null|string
     */
    public function getLastError()
    {
        return $this->_error;
    }

    /**
     * before send event
     */
    public function beforeSend()
    {
        $Event = new \yii\base\Event();
        $Event->sender = $this;
        $this->trigger(self::EVENT_BEFORE_SEND, $Event);
    }

    /**
     * after send event
     */
    public function afterSend()
    {
        $Event = new \yii\base\Event();
        $Event->sender = $this;
        $this->trigger(self::EVENT_AFTER_SEND, $Event);
    }
}