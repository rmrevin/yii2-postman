<?php
/**
 * Postman.php
 * @author: Roman Revin <roma@quetzal.ru>
 * @date  : 31.05.13
 */

namespace yii\postman;

use Yii;
use PHPMailer;
use yii\base\Component;

class Postman extends Component
{

	public $default_from = array('mailer@localhost', 'Mailer');

	public $default_view_path = '/email';

	public $driver = 'mail';

	public $smtp_config = array(
		'host' => 'localhost',
		'port' => 25,
		'auth' => false,
		'user' => '',
		'password' => '',
		'secure' => 'ssl',
		'debug' => false,
	);

	/** @var PHPMailer */
	private $_mailer_object = null;

	public function init()
	{
		parent::init();

		$from = $this->default_from;

		$mailer = new PHPMailer();
		$mailer->CharSet = 'utf-8';
		$mailer->SetFrom($from[0], $from[1]);

		$this->_mailer_object = $mailer;

		$this->reconfigure_driver();
	}

	public function reconfigure_driver()
	{
		$mailer = $this->_mailer_object;

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

	public function get_clone_mailer_object()
	{
		return clone $this->_mailer_object;
	}
}