<?php
/**
 * PostmanTest.php
 * @author: Roman Revin <xgismox@gmail.com>
 * @date  : 10.06.13
 */

namespace postmantest\postman;

use postmantest\TestCase;
use yii\postman\Postman;

class PostmanTest extends TestCase
{

	public function testMain()
	{
		/** @var Postman $Postman */
		$Postman = \Yii::$app->getComponent('postman');

		$this->assertInstanceOf('\yii\postman\Postman', $Postman);

		$this->assertTrue($Postman->table()->exists(true));
		$Postman->table()->drop();
		$this->assertFalse($Postman->table()->exists(true));
		$Postman->table()->create();
		$this->assertTrue($Postman->table()->exists(true));

		$PHPMailer = $Postman->get_clone_mailer_object();
		$this->assertInstanceOf('PHPMailer', $PHPMailer);

		$PHPMailer = $Postman->get_mailer_object();
		$this->assertInstanceOf('PHPMailer', $PHPMailer);

	}

	public function testDriverMail()
	{
		/** @var Postman $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Postman->driver = 'mail';
		$Postman->reconfigure_driver();
	}

	public function testDriverSendmail()
	{
		/** @var Postman $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Postman->driver = 'sendmail';
		$Postman->reconfigure_driver();
	}

	public function testDriverQmail()
	{
		/** @var Postman $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Postman->driver = 'qmail';
		$Postman->reconfigure_driver();
	}

	public function testDriverSMTP()
	{
		/** @var Postman $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Postman->driver = 'smtp';
		$Postman->smtp_config = \Yii::$app->params['smtp'];
		$Postman->reconfigure_driver();
	}

	/**
	 * @expectedException \yii\postman\PostmanException
	 */
	public function testDriverError()
	{
		/** @var Postman $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Postman->driver = 'unknow';
		$Postman->smtp_config = \Yii::$app->params['smtp'];
		$Postman->reconfigure_driver();
	}
}