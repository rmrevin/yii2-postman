<?php
/**
 * RawLetterTest.php
 * @author Roman Revin http://phptime.ru
 */

namespace rmrevin\yii\postman\tests\unit\postman;

use rmrevin\yii\postman;

/**
 * Class RawLetterTest
 * @package rmrevin\yii\postman\tests\unit\postman
 */
class RawLetterTest extends postman\tests\unit\TestCase
{

    public $run_sendmail_test = false;
    public $run_mail_test = false;
    public $run_qmail_test = false;

    /**
     * @return \rmrevin\yii\postman\RawLetter
     */
    public function testMain()
    {
        $Letter = (new postman\RawLetter())
            ->setSubject('Subject')
            ->setBody('<b>Html text</b><br/><hr/>')
            ->addAddress(\Yii::$app->params['demo_email'])
            ->addAttachment(realpath(__DIR__ . '/../data/phptime.ru.png'), 'phptime.ru.png');

        $this->assertEmpty($Letter->getLastError(), $Letter->getLastError());
        $this->assertInstanceOf(postman\RawLetter::className(), $Letter);

        return $Letter;
    }

    /**
     * @depends testMain
     * @param \rmrevin\yii\postman\RawLetter $Letter
     * @throws \rmrevin\yii\postman\Exception
     */
    public function testSendSendmail(postman\RawLetter $Letter)
    {
        if ($this->run_sendmail_test === false) {
            $this->markTestSkipped();

            return;
        }

        $Postman = \rmrevin\yii\postman\Component::get();
        $Postman->driver = 'sendmail';
        $Postman->reconfigureDriver();

        $Letter->setSubject('Sendmail raw letter');

        $this->assertInternalType('integer', $Letter->send());
        $this->assertInternalType('integer', $Letter->send(true));

        $this->assertEmpty($Letter->getLastError(), $Letter->getLastError());

        $count = postman\models\LetterModel::find()
            ->where(['subject' => 'Sendmail raw letter'])
            ->count();

        $this->assertEquals(2, $count);
    }

    /**
     * @depends testMain
     * @param \rmrevin\yii\postman\RawLetter $Letter
     */
    public function testSendMail(postman\RawLetter $Letter)
    {
        if ($this->run_mail_test === false) {
            $this->markTestSkipped();

            return;
        }

        $Postman = \rmrevin\yii\postman\Component::get();
        $Postman->driver = 'mail';
        $Postman->reconfigureDriver();

        $Letter->setSubject('Native php mail() raw letter');

        $this->assertInternalType('integer', $Letter->send());
        $this->assertInternalType('integer', $Letter->send(true));

        $this->assertEmpty($Letter->getLastError(), $Letter->getLastError());

        $count = postman\models\LetterModel::find()
            ->where(['subject' => 'Native php mail() raw letter'])
            ->count();

        $this->assertEquals(2, $count);
    }

    /**
     * @depends testMain
     * @param \rmrevin\yii\postman\RawLetter $Letter
     * @throws \rmrevin\yii\postman\Exception
     */
    public function testSendQmail(postman\RawLetter $Letter)
    {
        if ($this->run_qmail_test === false) {
            $this->markTestSkipped();

            return;
        }

        $Postman = \rmrevin\yii\postman\Component::get();
        $Postman->driver = 'qmail';
        $Postman->reconfigureDriver();

        $Letter->setSubject('Qmail raw letter');

        $this->assertInternalType('integer', $Letter->send());
        $this->assertInternalType('integer', $Letter->send(true));

        $this->assertEmpty($Letter->getLastError(), $Letter->getLastError());

        $count = postman\models\LetterModel::find()
            ->where(['subject' => 'Qmail raw letter'])
            ->count();

        $this->assertEquals(2, $count);
    }

    /**
     * @depends testMain
     * @param \rmrevin\yii\postman\RawLetter $Letter
     * @throws \rmrevin\yii\postman\Exception
     */
    public function testSendSMTP(postman\RawLetter $Letter)
    {
        $Postman = \rmrevin\yii\postman\Component::get();
        $Postman->driver = 'smtp';
        $Postman->reconfigureDriver();

        $Letter->setSubject('SMTP raw letter');

        $this->assertInternalType('integer', $Letter->send());
        $this->assertInternalType('integer', $Letter->send(true));

        $this->assertEmpty($Letter->getLastError(), $Letter->getLastError());

        $count = postman\models\LetterModel::find()
            ->where(['subject' => 'SMTP raw letter'])
            ->count();

        $this->assertEquals(2, $count);
    }
}