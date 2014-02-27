<?php
/**
 * PostmanTest.php
 * @author Roman Revin
 * @link http://phptime.ru
 */

namespace rmrevin\yii\postman\tests\unit\postman;

use rmrevin\yii\postman\Component;
use rmrevin\yii\postman\tests\unit\TestCase;

class PostmanTest extends TestCase
{

	public function testMain()
	{
		/** @var Component $Postman */
		$Postman = \Yii::$app->getComponent('postman');

		$this->assertInstanceOf(Component::className(), $Postman);

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
		/** @var Component $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Postman->driver = 'mail';
		$Postman->reconfigure_driver();
	}

	public function testDriverSendmail()
	{
		/** @var Component $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Postman->driver = 'sendmail';
		$Postman->reconfigure_driver();
	}

	public function testDriverQmail()
	{
		/** @var Component $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Postman->driver = 'qmail';
		$Postman->reconfigure_driver();
	}

	public function testDriverSMTP()
	{
		/** @var Component $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Postman->driver = 'smtp';
		$Postman->smtp_config = \Yii::$app->params['smtp'];
		$Postman->reconfigure_driver();
	}

	/**
	 * @expectedException \rmrevin\yii\postman\PostmanException
	 */
	public function testDriverError()
	{
		/** @var Component $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Postman->driver = 'unknow';
		$Postman->smtp_config = \Yii::$app->params['smtp'];
		$Postman->reconfigure_driver();
	}
}