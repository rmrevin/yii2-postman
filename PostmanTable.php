<?php
/**
 * PostmanTable.php
 * @author Roman Revin
 * @link http://phptime.ru
 */

namespace rmrevin\yii\postman;

/**
 * Class PostmanTable
 * @package rmrevin\yii\postman
 */
class PostmanTable
{

	/** @var \rmrevin\yii\postman\Component */
	private $_component;

	public function __construct(Component $component)
	{
		$this->_component = $component;
	}

	public function exists($refresh = false)
	{
		return null !== \Yii::$app->getDb()->getTableSchema($this->_component->table, $refresh);
	}

	public function drop()
	{
		return \Yii::$app->getDb()->createCommand()->dropTable($this->_component->table)->execute();
	}

	public function create()
	{
		if ($this->exists() === false) {
			$Schema = \Yii::$app->getDb()->getSchema();

			\Yii::$app->getDb()->createCommand()->createTable(
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