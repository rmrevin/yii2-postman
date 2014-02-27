<?php
/**
 * RawLetterTest.php
 * @author Roman Revin
 * @link http://phptime.ru
 */

namespace rmrevin\yii\postman\tests\unit\postman;

use rmrevin\yii\postman\Component;
use rmrevin\yii\postman\models\LetterModel;
use rmrevin\yii\postman\RawLetter;
use rmrevin\yii\postman\tests\unit\TestCase;

class RawLetterTest extends TestCase
{

	public $run_sendmail_test = true;
	public $run_mail_test = true;
	public $run_qmail_test = true;

	public function testMain()
	{
		$Letter = new RawLetter('Subject', '<b>Html text</b><br/><hr/>');
		$Letter->add_address(\Yii::$app->params['demo_email']);
		$Letter->add_attachment(realpath(__DIR__ . '/../data/phptime-copyright.png'), 'phptime-copyright.png');
		$this->assertEmpty($Letter->get_last_error(), $Letter->get_last_error());
		$this->assertInstanceOf(RawLetter::className(), $Letter);

		return $Letter;
	}

	/**
	 * @depends testMain
	 */
	public function testSendSendmail(RawLetter $Letter)
	{
		if ($this->run_sendmail_test === false) {
			$this->markTestSkipped();

			return;
		}

		/** @var Component $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Postman->driver = 'sendmail';
		$Postman->reconfigure_driver();
		$Letter->set_postman($Postman);
		$Letter->set_from(\Yii::$app->params['default_from']);
		$Letter->set_subject('Sendmail raw letter');

		$this->assertInternalType('integer', $Letter->send());
		$this->assertInternalType('integer', $Letter->send(true));

		$this->assertEmpty($Letter->get_last_error(), $Letter->get_last_error());

		$count = LetterModel::find()->where(array('subject' => 'Sendmail raw letter'))->count();
		$this->assertEquals(2, $count);
	}

	/**
	 * @depends testMain
	 */
	public function testSendMail(RawLetter $Letter)
	{
		if ($this->run_mail_test === false) {
			$this->markTestSkipped();

			return;
		}

		/** @var Component $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Postman->driver = 'mail';
		$Postman->reconfigure_driver();
		$Letter->set_postman($Postman);
		$Letter->set_from(\Yii::$app->params['default_from']);
		$Letter->set_subject('Native php mail() raw letter');

		$this->assertInternalType('integer', $Letter->send());
		$this->assertInternalType('integer', $Letter->send(true));

		$this->assertEmpty($Letter->get_last_error(), $Letter->get_last_error());

		$count = LetterModel::find()->where(array('subject' => 'Native php mail() raw letter'))->count();
		$this->assertEquals(2, $count);
	}

	/**
	 * @depends testMain
	 */
	public function testSendQmail(RawLetter $Letter)
	{
		if ($this->run_qmail_test === false) {
			$this->markTestSkipped();

			return;
		}

		/** @var Component $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Postman->driver = 'qmail';
		$Postman->reconfigure_driver();
		$Letter->set_postman($Postman);
		$Letter->set_from(\Yii::$app->params['default_from']);
		$Letter->set_subject('Qmail raw letter');

		$this->assertInternalType('integer', $Letter->send());
		$this->assertInternalType('integer', $Letter->send(true));

		$this->assertEmpty($Letter->get_last_error(), $Letter->get_last_error());

		$count = LetterModel::find()->where(array('subject' => 'Qmail raw letter'))->count();
		$this->assertEquals(2, $count);
	}

	/**
	 * @depends testMain
	 */
	public function testSendSMTP(RawLetter $Letter)
	{
		/** @var Component $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Postman->driver = 'smtp';
		$Postman->smtp_config = \Yii::$app->params['smtp'];
		$Postman->reconfigure_driver();
		$Letter->set_postman($Postman);
		$Letter->set_from(\Yii::$app->params['default_from']);
		$Letter->set_subject('SMTP raw letter');

		$this->assertInternalType('integer', $Letter->send());
		$this->assertInternalType('integer', $Letter->send(true));

		$this->assertEmpty($Letter->get_last_error(), $Letter->get_last_error());

		$count = LetterModel::find()->where(array('subject' => 'SMTP raw letter'))->count();
		$this->assertEquals(2, $count);
	}
}