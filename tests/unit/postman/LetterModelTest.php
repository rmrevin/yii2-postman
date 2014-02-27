<?php
/**
 * LetterModelTest.php
 * @author Roman Revin
 * @link http://phptime.ru
 */

namespace rmrevin\yii\postman\tests\unit\postman;

use rmrevin\yii\postman\Component;
use rmrevin\yii\postman\RawLetter;
use rmrevin\yii\postman\tests\unit\TestCase;
use rmrevin\yii\postman\models\LetterModel;

class LetterModelTest extends TestCase
{

	public function testMain()
	{
		$NewLetter = new LetterModel();
		$NewLetter->attributes = [
			'subject' => uniqid(rand(), true),
			'body' => uniqid(rand(), true),
			'recipients' => uniqid(rand(), true),
			'attachments' => uniqid(rand(), true),
		];

		$this->assertNotEmpty($NewLetter->attributeLabels());

		if ($NewLetter->save() === false) {
			$this->fail('Failed to save the model. Error: ' . $NewLetter->getFirstErrors()[0]);
		} else {
			$Letter = LetterModel::find($NewLetter->id);

			$this->assertInstanceOf(LetterModel::className(), $Letter);

			$this->assertEquals(1, $NewLetter->delete());
		}
	}

	public function testSending()
	{
		$Letter = new RawLetter('Test sending', 'body');
		$Letter
			->add_address(\Yii::$app->params['demo_email'])
			->add_cc_address(['cc@domail.com'])
			->add_bcc_address(['bcc@domail.com'])
			->add_reply_to(['reply@domail.com']);
		$letter_id = $Letter->send();

		/** @var LetterModel $LetterModel */
		$LetterModel = LetterModel::find($letter_id);
		$this->assertInstanceOf(LetterModel::className(), $LetterModel);

		$LetterModel->set_mailer($Letter->get_postman()->get_clone_mailer_object());
		$this->assertInstanceOf('PHPMailer', $LetterModel->get_mailer());
		$this->assertTrue($LetterModel->send_immediately());

		return $LetterModel;
	}

	/**
	 * @depends testSending
	 * @param LetterModel $LetterModel
	 */
	public function testErrorSending(LetterModel $LetterModel)
	{
		$mailer = $LetterModel->get_mailer();
		$mailer->IsSMTP();
		$mailer->Host = 'smtp.gmail.com';
		$mailer->Username = 'unknow';
		$LetterModel->send_immediately();
		$this->assertNotEmpty($LetterModel->get_last_error());
	}

	public function testCronSending()
	{
		$Letter = new RawLetter('Test cron sending', 'body');
		$Letter->add_address(
			\Yii::$app->params['demo_email'],
			['test1@domain.com'],
			['test2@domain.com'],
			['test3@domain.com']
		);
		$Letter->send();
		$Letter->send();
		$Letter->send();
		$Letter->send();
		$Letter->send();

		$count = LetterModel::find()
			->where(['date_send' => null])
			->count();
		$this->assertEquals(5, $count);

		LetterModel::cron(3);
		$this->expectOutputString('');
	}

	public function testErrorCronSending()
	{
		/** @var Component $Postman */
		$Postman = \Yii::$app->getComponent('postman');
		$Mailer = $Postman->get_mailer_object();
		$Mailer->IsSMTP();
		$Mailer->Host = 'smtp.google.com';
		$Mailer->Username = 'unknow';

		$Letter = new RawLetter('Test cron sending', 'body');
		$Letter->add_address(
			\Yii::$app->params['demo_email'],
			['test1@domain.com'],
			['test2@domain.com'],
			['test3@domain.com']
		);
		$Letter->set_from(['test@domain.com', 'Test']);
		$Letter->send();
		$Letter->send();
		$Letter->send();
		$Letter->send();
		$Letter->send();

		$count = LetterModel::find()
			->where(['date_send' => null])
			->count();
		$this->assertEquals(5, $count);

		LetterModel::cron(1);
		$this->expectOutputString('The following From address failed: test@domain.com : Called Mail() without being connected' . "\n");
	}

	/**
	 * @expectedException \rmrevin\yii\postman\LetterException
	 */
	public function testNotInitMailerException()
	{
		$NewLetter = new LetterModel();
		$NewLetter->send_immediately();
	}
}