<?php
/**
 * PostmanTest.php
 * @author Roman Revin http://phptime.ru
 */

namespace rmrevin\yii\postman\tests\unit\postman;

use rmrevin\yii\postman;

/**
 * Class PostmanTest
 * @package rmrevin\yii\postman\tests\unit\postman
 */
class PostmanTest extends postman\tests\unit\TestCase
{

    public function testMain()
    {
        $Postman = \rmrevin\yii\postman\Component::get();

        $this->assertInstanceOf(postman\Component::className(), $Postman);

        $PHPMailer = $Postman->getCloneMailerObject();
        $this->assertInstanceOf('PHPMailer', $PHPMailer);

        $PHPMailer = $Postman->getMailerObject();
        $this->assertInstanceOf('PHPMailer', $PHPMailer);

    }

    public function testDriverMail()
    {
        $Postman = \rmrevin\yii\postman\Component::get();
        $Postman->driver = 'mail';
        $Postman->reconfigureDriver();
    }

    public function testDriverSendmail()
    {

//        $this->markTestSkipped();

        $Postman = \rmrevin\yii\postman\Component::get();
        $Postman->driver = 'sendmail';
        $Postman->reconfigureDriver();
    }

    public function testDriverQmail()
    {
        $Postman = \rmrevin\yii\postman\Component::get();
        $Postman->driver = 'qmail';
        $Postman->reconfigureDriver();
    }

    public function testDriverSMTP()
    {
        $Postman = \rmrevin\yii\postman\Component::get();
        $Postman->driver = 'smtp';
        $Postman->reconfigureDriver();
    }

    /**
     * @expectedException \rmrevin\yii\postman\Exception
     */
    public function testDriverError()
    {
        $Postman = \rmrevin\yii\postman\Component::get();
        $Postman->driver = 'unknown';
        $Postman->reconfigureDriver();
    }
}