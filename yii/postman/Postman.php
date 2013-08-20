<?php
/**
 * Postman.php
 * @author Roman Revin
 * @link http://phptime.ru
 */

namespace yii\postman;

use Yii;
use PHPMailer;
use yii\base\Component;

/**
 * Class Postman
 * The main class to wrap a config of PHPMailer
 * @package yii\postman
 */
class Postman extends Component
{

	/** @var array the default value for the "From" field */
	public $default_from = array('mailer@localhost', 'Mailer');

	/** @var string a name of the db table for letters */
	public $table = 'tbl_letter';

	/** @var string a path to views of letters */
	public $view_path = '/email';

	/** @var string a driver for sending mail [mail|qmail|sendmail|smtp] */
	public $driver = 'mail';

	/** @var array smtp config */
	public $smtp_config = array(
		'host' => 'localhost',
		'port' => 25,
		'auth' => false,
		'user' => '',
		'password' => '',
		'secure' => 'ssl',
		'debug' => false,
	);

	/** @var PHPMailer object */
	private $_mailer = null;

	/** @var PostmanTable */
	private $_table = null;

	/**
	 * The init method for the component
	 */
	public function init()
	{
		parent::init();

		$mailer = new PHPMailer();
		$mailer->CharSet = 'utf-8';

		$this->_mailer = $mailer;

		$this->set_default_from($this->default_from);
		$this->reconfigure_driver();
		$this->table()->create();
	}

	/**
	 * the method adjusts the selected driver to send emails
	 * @return $this
	 * @throws PostmanException
	 */
	public function reconfigure_driver()
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
				throw new PostmanException(Yii::t('app', 'Could not determine the driver is sending letters.'));
		}

		return $this;
	}

	/**
	 * @param array $from
	 * @return $this
	 */
	public function set_default_from($from)
	{
		$this->default_from = $from;
		$this->_mailer->SetFrom($from[0], $from[1]);

		return $this;
	}

	/**
	 * @return PostmanTable
	 */
	public function table()
	{
		if ($this->_table === null) {
			$this->_table = new PostmanTable($this);
		}

		return $this->_table;
	}

	/**
	 * @return PHPMailer
	 */
	public function get_mailer_object()
	{
		return $this->_mailer;
	}

	/**
	 * factory method to create clones of "Postman"
	 * @return PHPMailer
	 */
	public function get_clone_mailer_object()
	{
		return clone $this->_mailer;
	}
}