<?php
/**
 * ViewLetterTest.php
 * @author Roman Revin http://phptime.ru
 */

namespace rmrevin\yii\postman\tests\unit\postman;

use rmrevin\yii\postman;

/**
 * Class ViewLetterTest
 * @package rmrevin\yii\postman\tests\unit\postman
 */
class ViewLetterTest extends postman\tests\unit\TestCase
{

    public $run_sendmail_test = false;
    public $run_mail_test = false;
    public $run_qmail_test = false;

    /**
     * @return \rmrevin\yii\postman\ViewLetter
     */
    public function testMain()
    {
        $Letter = (new postman\ViewLetter())
            ->setSubject('Subject')
            ->setBodyFromView('test-template', [
                'name' => 'Josh',
                'age' => 23,
                'sex' => 'male'
            ])
            ->addAddress(\Yii::$app->params['demo_email'])
            ->addAttachment(realpath(__DIR__ . '/../data/phptime.ru.png'), 'phptime.ru.png');

        $this->assertEmpty($Letter->getLastError(), $Letter->getLastError());
        $this->assertInstanceOf(postman\ViewLetter::className(), $Letter);

        return $Letter;
    }

    /**
     * @depends testMain
     * @param \rmrevin\yii\postman\ViewLetter $Letter
     * @throws \rmrevin\yii\postman\Exception
     */
    public function testSendSendmail(postman\ViewLetter $Letter)
    {
        if ($this->run_sendmail_test === false) {
            $this->markTestSkipped();

            return;
        }

        $Postman = \rmrevin\yii\postman\Component::get();
        $Postman->driver = 'sendmail';
        $Postman->reconfigureDriver();

        $Letter->setSubject('Sendmail html letter');

        $this->assertInternalType('integer', $Letter->send());
        $this->assertInternalType('integer', $Letter->send(true));

        $this->assertEmpty($Letter->getLastError(), $Letter->getLastError());

        $count = postman\models\LetterModel::find()
            ->where(['subject' => 'Sendmail html letter'])
            ->count();

        $this->assertEquals(2, $count);
    }

    /**
     * @depends testMain
     * @param \rmrevin\yii\postman\ViewLetter $Letter
     * @throws \rmrevin\yii\postman\Exception
     */
    public function testSendMail(postman\ViewLetter $Letter)
    {
        if ($this->run_mail_test === false) {
            $this->markTestSkipped();

            return;
        }

        $Postman = \rmrevin\yii\postman\Component::get();
        $Postman->driver = 'mail';
        $Postman->reconfigureDriver();

        $Letter->setSubject('Native php mail() html letter');

        $this->assertInternalType('integer', $Letter->send());
        $this->assertInternalType('integer', $Letter->send(true));

        $this->assertEmpty($Letter->getLastError(), $Letter->getLastError());

        $count = postman\models\LetterModel::find()
            ->where(['subject' => 'Native php mail() html letter'])
            ->count();

        $this->assertEquals(2, $count);
    }

    /**
     * @depends testMain
     * @param \rmrevin\yii\postman\ViewLetter $Letter
     * @throws \rmrevin\yii\postman\Exception
     */
    public function testSendQmail(postman\ViewLetter $Letter)
    {
        if ($this->run_qmail_test === false) {
            $this->markTestSkipped();

            return;
        }

        $Postman = \rmrevin\yii\postman\Component::get();
        $Postman->driver = 'qmail';
        $Postman->reconfigureDriver();

        $Letter->setSubject('Qmail html letter');

        $this->assertInternalType('integer', $Letter->send());
        $this->assertInternalType('integer', $Letter->send(true));

        $this->assertEmpty($Letter->getLastError(), $Letter->getLastError());

        $count = postman\models\LetterModel::find()
            ->where(['subject' => 'Qmail html letter'])
            ->count();

        $this->assertEquals(2, $count);
    }

    /**
     * @depends testMain
     * @param \rmrevin\yii\postman\ViewLetter $Letter
     * @throws \rmrevin\yii\postman\Exception
     */
    public function testSendSMTP(postman\ViewLetter $Letter)
    {
        $Postman = \rmrevin\yii\postman\Component::get();
        $Postman->driver = 'smtp';
        $Postman->reconfigureDriver();

        $Letter->setSubject('SMTP html letter');

        $this->assertInternalType('integer', $Letter->send());
        $this->assertInternalType('integer', $Letter->send(true));

        $this->assertEmpty($Letter->getLastError(), $Letter->getLastError());

        $count = postman\models\LetterModel::find()
            ->where(['subject' => 'SMTP html letter'])
            ->count();

        $this->assertEquals(2, $count);
    }

    /**
     * @expectedException \rmrevin\yii\postman\LetterException
     */
    public function testViewNotFoundException()
    {
        (new postman\ViewLetter())
            ->setSubject('Subject')
            ->setBodyFromView('non-exist-template', [
                'name' => 'Josh',
                'age' => 23,
                'sex' => 'male'
            ])
            ->send();
    }
}