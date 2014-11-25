<?php
/**
 * LetterTest.php
 * @author Roman Revin http://phptime.ru
 */

namespace rmrevin\yii\postman\tests\unit\postman;

use rmrevin\yii\postman;

/**
 * Class LetterTest
 * @package rmrevin\yii\postman\tests\unit\postman
 */
class LetterTest extends postman\tests\unit\TestCase
{

    public function testAddRecipients()
    {
        $Letter = (new postman\RawLetter())
            ->setSubject('Subject')
            ->setBody('Text');

        $Letter->addAddress(
            ['test1@email.com', 'Name Test 1'],
            ['test2@email.com', 'Name Test 2'],
            ['test3@email.com', 'Name Test 3']
        );

        $Letter->addCcAddress(
            ['cc1@email.com', 'Name Cc 1'],
            ['cc2@email.com', 'Name Cc 2'],
            ['cc3@email.com', 'Name Cc 3']
        );

        $Letter->addBccAddress(
            ['bcc1@email.com', 'Name Bcc 1'],
            ['bcc2@email.com', 'Name Bcc 2'],
            ['bcc3@email.com', 'Name Bcc 3']
        );

        $Letter->addReplyTo(
            ['reply1@email.com', 'Name Reply 1'],
            ['reply2@email.com', 'Name Reply 2'],
            ['reply3@email.com', 'Name Reply 3']
        );

        $this->assertNotEmpty($Letter->getRecipients());

        $this->assertEquals(13, $Letter->getCountRecipients());
    }

    public function testAddAttachments()
    {
        $Letter = (new postman\RawLetter())
            ->setSubject('Subject')
            ->setBody('Text')
            ->addAttachment(realpath(__DIR__ . '/../data/phptime.ru.png'), 'phptime.ru.png');

        $attachments = $Letter->getAttachments();

        $this->assertEquals(substr($attachments[0]['path'], -31), '/tests/unit/data/phptime.ru.png');
        $this->assertEquals($attachments[0]['name'], 'phptime.ru.png');
        $this->assertEquals($attachments[0]['encoding'], 'base64');
        $this->assertEquals($attachments[0]['type'], 'application/octet-stream');
    }

    /**
     * @expectedException \rmrevin\yii\postman\LetterException
     */
    public function testPostmanNotSet()
    {
        \Yii::$app->set(postman\Component::COMPONENT, null);
        new postman\RawLetter('', '');
    }
}