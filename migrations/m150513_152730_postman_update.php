<?php

use yii\db\mysql\Schema;

class m150513_152730_postman_update extends yii\db\Migration
{

    public function safeUp()
    {
        $this->addColumn(\rmrevin\yii\postman\Component::get()->table, 'code', Schema::TYPE_STRING);

        $letters = (new \yii\db\Query())
            ->select('*')
            ->from(\rmrevin\yii\postman\Component::get()->table)
            ->all();

        if (!empty($letters)) {
            foreach ($letters as $letter) {
                $this->update(
                    \rmrevin\yii\postman\Component::get()->table,
                    ['code' => \Yii::$app->getSecurity()->generateRandomString()],
                    ['id' => $letter['id']]
                );
            }
        }
    }

    public function safeDown()
    {
        $this->dropColumn(\rmrevin\yii\postman\Component::get()->table, 'code');
    }
}