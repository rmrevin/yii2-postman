<?php
/**
 * Component.php
 * @author Roman Revin http://phptime.ru
 */

namespace rmrevin\yii\postman;

use PHPMailer;

/**
 * Class Component
 * The main class to wrap a config of PHPMailer
 * @package rmrevin\yii\postman
 */
class Component extends \yii\base\Component
{

    const COMPONENT = 'postman';

    /** @var array the default value for the "From" field */
    public $default_from = ['mailer@localhost', 'Mailer'];

    /** @var string|null the string that is added to the beginning of the letter subject */
    public $subject_prefix = null;

    /** @var string|null the string that is added to the end of the letter subject */
    public $subject_suffix = null;

    /** @var string a name of the db table for letters */
    public $table = '{{%postman_letter}}';

    /** @var string a path to views of letters */
    public $view_path = '/email';

    /** @var string a driver for sending mail [mail|qmail|sendmail|smtp] */
    public $driver = 'mail';

    /** @var array smtp config */
    public $smtp_config = [
        'host' => 'localhost',
        'port' => 25,
        'auth' => false,
        'user' => '',
        'password' => '',
        'secure' => false, // Sets connection prefix. Options are "", "ssl" or "tls"
        'debug' => false,
    ];

    /** @var PHPMailer object */
    private $_mailer = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $mailer = new PHPMailer();
        $mailer->CharSet = 'utf-8';

        $this->_mailer = $mailer;

        $this->setDefaultFrom($this->default_from);
        $this->reconfigureDriver();
    }

    /**
     * the method adjusts the selected driver to send emails
     * @return self
     * @throws \rmrevin\yii\postman\Exception
     */
    public function reconfigureDriver()
    {
        $mailer = $this->_mailer;

        switch ($this->driver) {
            case 'mail':
                $mailer->IsMail();
                break;
            case 'qmail':
                $mailer->IsQmail();
                break;
            case 'sendmail':
                $mailer->IsSendmail();
                break;
            case 'smtp':
                $mailer->IsSMTP();
                $mailer->Host = $this->smtp_config['host'];
                $mailer->Port = $this->smtp_config['port'];
                $mailer->SMTPAuth = $this->smtp_config['auth'];
                $mailer->Username = $this->smtp_config['user'];
                $mailer->Password = $this->smtp_config['password'];
                $mailer->SMTPSecure = $this->smtp_config['secure'];
                $mailer->SMTPDebug = $this->smtp_config['debug'];
                break;
            default:
                throw new Exception(\Yii::t('app', 'Could not determine the driver is sending letters.'));
        }

        return $this;
    }

    /**
     * @param array $from
     * @return $this
     */
    public function setDefaultFrom($from)
    {
        $this->default_from = $from;
        $this->_mailer->SetFrom($from[0], $from[1]);

        return $this;
    }

    /**
     * @return PHPMailer
     */
    public function getMailerObject()
    {
        return $this->_mailer;
    }

    /**
     * factory method to create clones of "Postman"
     * @return PHPMailer
     */
    public function getCloneMailerObject()
    {
        return clone $this->_mailer;
    }

    /**
     * @static
     * @return null|self
     * @throws \yii\base\InvalidConfigException
     */
    public static function get()
    {
        return \Yii::$app->get(static::COMPONENT);
    }
}