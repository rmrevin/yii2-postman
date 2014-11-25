<?php
/**
 * LetterModelTest.php
 * @author Roman Revin http://phptime.ru
 */

namespace rmrevin\yii\postman\tests\unit\postman;

use rmrevin\yii\postman;

/**
 * Class LetterModelTest
 * @package rmrevin\yii\postman\tests\unit\postman
 */
class LetterModelTest extends postman\tests\unit\TestCase
{

    public function testMain()
    {
        $NewLetter = new postman\models\LetterModel([
            'subject' => uniqid(rand(), true),
            'body' => uniqid(rand(), true),
            'recipients' => uniqid(rand(), true),
            'attachments' => uniqid(rand(), true),
        ]);

        $this->assertNotEmpty($NewLetter->attributeLabels());

        if ($NewLetter->save() === false) {
            $this->fail('Failed to save the model. Error: ' . $NewLetter->getFirstErrors()[0]);
        } else {
            $Letter = postman\models\LetterModel::findOne($NewLetter->id);

            $this->assertInstanceOf(postman\models\LetterModel::className(), $Letter);

            $this->assertEquals(1, $NewLetter->delete());
        }
    }

    /**
     * @return \rmrevin\yii\postman\models\LetterModel
     */
    public function testSending()
    {
        $Letter = (new postman\RawLetter())
            ->setSubject('Test sending')
            ->setBody('body')
            ->addAddress(\Yii::$app->params['demo_email'])
            ->addCcAddress(['cc@domail.com'])
            ->addBccAddress(['bcc@domail.com'])
            ->addReplyTo(['reply@domail.com']);

        $letter_id = $Letter->send();

        /** @var postman\models\LetterModel $LetterModel */
        $LetterModel = postman\models\LetterModel::findOne($letter_id);
        $this->assertInstanceOf(postman\models\LetterModel::className(), $LetterModel);

        $LetterModel->setMailer(\rmrevin\yii\postman\Component::get()->getCloneMailerObject());
        $this->assertInstanceOf('PHPMailer', $LetterModel->getMailer());
        $this->assertTrue($LetterModel->sendImmediately());

        return $LetterModel;
    }

    /**
     * @depends testSending
     * @param postman\models\LetterModel $LetterModel
     */
    public function testErrorSending(postman\models\LetterModel $LetterModel)
    {
        $mailer = $LetterModel->getMailer();
        $mailer->IsSMTP();
        $mailer->Host = 'smtp.gmail.com';
        $mailer->Username = 'unknow';
        $LetterModel->sendImmediately();
        $this->assertNotEmpty($LetterModel->getLastError());
    }

    public function testCronSending()
    {
        $Letter = (new postman\RawLetter())
            ->setSubject('Test cron sending')
            ->setBody('body')
            ->addAddress(
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

        $count = postman\models\LetterModel::find()
            ->where(['date_send' => null])
            ->count();

        $this->assertEquals(5, $count);

        postman\models\LetterModel::cron(3);
        $this->expectOutputString('');
    }

    public function testErrorCronSending()
    {
        $Postman = \rmrevin\yii\postman\Component::get();
        $Mailer = $Postman->getMailerObject();
        $Mailer->IsSMTP();
        $Mailer->Host = 'smtp.google.com';
        $Mailer->Username = 'unknow';

        $Letter = (new postman\RawLetter())
            ->setSubject('Test cron sending')
            ->setBody('body')
            ->setFrom(['test@domain.com', 'Test'])
            ->addAddress(
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

        $count = postman\models\LetterModel::find()
            ->where(['date_send' => null])
            ->count();
        $this->assertEquals(5, $count);

        postman\models\LetterModel::cron(1);
        $this->expectOutputString('The following From address failed: test@domain.com : Called Mail() without being connected' . "\n");
    }

    /**
     * @expectedException \rmrevin\yii\postman\LetterException
     */
    public function testNotInitMailerException()
    {
        $NewLetter = new postman\models\LetterModel();
        $NewLetter->sendImmediately();
    }
}