<?php
/**
 * example.php
 * @author Roman Revin http://phptime.ru
 */

(new \rmrevin\yii\postman\RawLetter())
    ->setSubject('Subject')
    ->setBody('Message body')
    ->addAddress('user@somehost.com')
    ->addBccAddress(['tech@somehost.com'])
    ->send();

(new \rmrevin\yii\postman\ViewLetter)
    ->setSubject('Subject')
    ->setBodyView('letter-view', [
        'name' => 'Rosy',
        'date' => date('Y-m-d')
    ])
    ->addAddress(['user@somehost.com', 'John Smith'])
    ->addAttachment('/path/to/file.tar.gz')
    ->send();