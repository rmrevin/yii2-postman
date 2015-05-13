<?php
/**
 * Letter.php
 * @author Roman Revin http://phptime.ru
 */

namespace rmrevin\yii\postman;

use PHPMailer;
use rmrevin\yii\postman\models\LetterModel;
use yii\base\Event;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * Class Letter
 * The abstract class that implements the basic functionality of letters;
 *
 * @package rmrevin\yii\postman
 */
abstract class Letter extends \yii\base\Component
{

    /** @var PHPMailer object */
    protected $_mailer = null;

    /** @var string a random code */
    protected $code;

    /** @var string a subject */
    protected $raw_subject;

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

    /** @var Component */
    private $Postman;

    /** the name of the event that occurs before sending emails */
    const EVENT_BEFORE_SEND = 'beforeSend';

    /** the name of the event that occurs after sending emails */
    const EVENT_AFTER_SEND = 'afterSend';

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!\Yii::$app->has(Component::COMPONENT)) {
            throw new LetterException(\Yii::t('app', 'You need to configure the component `{component}`.', [
                'component' => Component::COMPONENT,
            ]));
        }

        $this->Postman = \rmrevin\yii\postman\Component::get();
        $this->setFrom($this->Postman->default_from);

        $this->code = \Yii::$app->getSecurity()->generateRandomString();
    }

    /**
     * the method sets the "subject"
     * @param string $subject
     * @return static
     */
    public function setSubject($subject)
    {
        $this->raw_subject = (string)$subject;
        $this->subject = (string)$this->Postman->subject_prefix . (string)$subject . (string)$this->Postman->subject_suffix;

        return $this;
    }

    /**
     * the method sets the "body"
     * @param string $body
     * @return static
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * the method sets the value of the "From" field
     * @param array $from = ['user@somehost.com'] || ['user@somehost.com', 'John Smith']
     * @return static
     */
    public function setFrom($from)
    {
        $this->recipients['from'] = $from;

        return $this;
    }

    /**
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @return int
     */
    public function getCountRecipients()
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
     * the method adds a recipient
     * @param array $address = ['user@somehost.com'] || ['user@somehost.com', 'John Smith']
     * @return static
     */
    public function addAddress($address)
    {
        $args = func_get_args();
        foreach ($args as $address) {
            $this->_addAddr('to', $address);
        }

        return $this;
    }

    /**
     * the method adds a recipient to Cc
     * @param array $address = ['user@somehost.com'] || ['user@somehost.com', 'John Smith']
     *
     * @return static
     */
    public function addCcAddress($address)
    {
        $args = func_get_args();
        foreach ($args as $address) {
            $this->_addAddr('cc', $address);
        }

        return $this;
    }

    /**
     * the method adds a recipient to Bcc
     * @param array $address = ['user@somehost.com'] || ['user@somehost.com', 'John Smith']
     *
     * @return static
     */
    public function addBccAddress($address)
    {
        $args = func_get_args();
        foreach ($args as $address) {
            $this->_addAddr('bcc', $address);
        }

        return $this;
    }

    /**
     * the method adds a "Reply-to" address
     * @param array $address = ['user@somehost.com'] || ['user@somehost.com', 'John Smith']
     *
     * @return static
     */
    public function addReplyTo($address)
    {
        $args = func_get_args();
        foreach ($args as $address) {
            $this->_addAddr('reply', $address);
        }

        return $this;
    }

    /**
     * the method adds a recipient by type
     * @param $type
     * @param $address
     *
     * @return static
     */
    private function _addAddr($type, $address)
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
     * @return static
     */
    public function addAttachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
    {
        $this->attachments[] = [
            'path' => $path,
            'name' => $name,
            'encoding' => $encoding,
            'type' => $type
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getAttachments()
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
        $this->beforeSend();

        $LetterModel = $this->_dataToModel();
        $LetterModel->code = $this->code;
        $LetterModel->date_create = new Expression('NOW()');
        if ($LetterModel->validate()) {
            $result = $LetterModel->save();

            if ($immediately === true) {
                $LetterModel
                    ->setMailer(Component::get()->getCloneMailerObject())
                    ->sendImmediately();
            }

            $this->_error = $LetterModel->getLastError();
        } else {
            $result = false;
            $this->_error = array_shift($LetterModel->getFirstErrors());
        }

        $this->afterSend();

        return $result === true ? (int)$LetterModel->id : false;
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
     * the method converts the letter data to the letter model
     *
     * @return LetterModel
     */
    private function _dataToModel()
    {
        $LetterModel = new LetterModel();
        $LetterModel->recipients = Json::encode($this->recipients);
        $LetterModel->subject = $this->subject;
        $LetterModel->body = $this->body;
        $LetterModel->attachments = Json::encode($this->attachments);

        return $LetterModel;
    }

    /**
     * before send event
     */
    public function beforeSend()
    {
        $Event = new Event();
        $Event->sender = $this;
        $this->trigger(self::EVENT_BEFORE_SEND, $Event);
    }

    /**
     * after send event
     */
    public function afterSend()
    {
        $Event = new Event();
        $Event->sender = $this;
        $this->trigger(self::EVENT_AFTER_SEND, $Event);
    }
}