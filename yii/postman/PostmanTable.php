<?php
/**
 * PostmanTable.php
 * @author: Roman Revin <xgismox@gmail.com>
 * @date  : 10.06.2013
 */

namespace yii\postman;

use Yii;

class PostmanTable
{

	/** @var \yii\postman\Postman */
	private $_component;

	public function __construct(Postman $component)
	{
		$this->_component = $component;
	}

	public function exists($refresh = false)
	{
		return null !== Yii::$app->getDb()->getTableSchema($this->_component->table, $refresh);
	}

	public function drop()
	{
		return Yii::$app->getDb()->createCommand()->dropTable($this->_component->table)->execute();
	}

	public function create()
	{
		if ($this->exists() === false) {
			$Schema = Yii::$app->getDb()->getSchema();

			Yii::$app->getDb()->createCommand()->createTable(
				$this->_component->table,
				array(
					'id' => $Schema::TYPE_PK,
					'date_create' => $Schema::TYPE_DATETIME,
					'date_send' => $Schema::TYPE_DATETIME,
					'subject' => $Schema::TYPE_STRING,
					'body' => $Schema::TYPE_TEXT,
					'recipients' => $Schema::TYPE_TEXT,
					'attachments' => $Schema::TYPE_TEXT,
				),
				'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB'
			)->execute();
		}
	}
}