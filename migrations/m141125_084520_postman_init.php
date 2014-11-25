<?php

use yii\db\Schema;

class m141125_084520_postman_init extends yii\db\Migration
{

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(\rmrevin\yii\postman\Component::get()->table, [
            'id' => Schema::TYPE_PK,
            'date_create' => Schema::TYPE_DATETIME,
            'date_send' => Schema::TYPE_DATETIME,
            'subject' => Schema::TYPE_STRING,
            'body' => Schema::TYPE_TEXT,
            'recipients' => Schema::TYPE_TEXT,
            'attachments' => Schema::TYPE_TEXT,
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable(\rmrevin\yii\postman\Component::get()->table);
    }
}