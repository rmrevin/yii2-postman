<?php
/**
 * RawLetterTest.php
 * @author: Roman Revin <xgismox@gmail.com>
 * @date  : 10.06.2013
 */

namespace postmantest\postman;

use postmantest\TestCase;
use yii\postman\models\LetterModel;
use yii\postman\RawLetter;

class RawLetterTest extends TestCase
{

	public function testMain()
	{
		$Letter = new RawLetter('Subject', '<b>Html text</b><br/><hr/>');
		$Letter->add_address(\Yii::$app->params['demo_email']);
		$Letter->add_attachment(realpath(__DIR__ . '/../data/phptime-copyright.png'), 'phptime-copyright.png');
		$this->assertEmpty($Letter->get_last_error(), $Letter->get_last_error());
		$this->assertInstanceOf('\yii\postman\RawLetter', $Letter);

		return $Letter;
	}

	/**
	 * @depends testMain
	 */
	public function testSendSendmail(RawLetter $Letter)
	{
		/** @var \yii\postman\Postman $Postman */
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
		/** @var \yii\postman\Postman $Postman */
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
		/** @var \yii\postman\Postman $Postman */
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
		/** @var \yii\postman\Postman $Postman */
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