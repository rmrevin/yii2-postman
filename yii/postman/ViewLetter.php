<?php
/**
 * ViewLetter.php
 * @author: Roman Revin <xgismox@gmail.com>
 * @date  : 31.05.2013
 */

namespace yii\postman;

use Yii;
use yii\base\Event;

/**
 * Class ViewLetter
 * @package yii\postman
 */
class ViewLetter extends Letter
{

	/** @var string name of view file */
	private $_view = null;

	/** @var array params for view */
	private $_params = array();

	/**
	 * @param string $subject a subject of a message
	 * @param string $view    a name of a view file
	 * @param array  $params  params for a view
	 */
	public function __construct($subject, $view, $params = array())
	{
		parent::__construct();

		$this->subject = $subject;

		$this->set_view($view)->set_params($params);

		$this->on(self::EVENT_BEFORE_SEND, array($this, 'before_send'));
	}

	/**
	 * the method sets a name of a view file
	 *
	 * @param $view
	 * @return $this
	 */
	public function set_view($view)
	{
		$this->_view = $view;
		return $this;
	}

	/**
	 * the method sets params for a view
	 *
	 * @param $params
	 * @return $this
	 */
	public function set_params($params)
	{
		$this->_params = $params;
		return $this;
	}

	/**
	 * BeforeSend event method
	 *
	 * @param Event $Event
	 * @throws LetterException
	 */
	public function before_send(Event $Event)
	{
		$path = Yii::$app->getViewPath() . $this->_postman->view_path . DIRECTORY_SEPARATOR . $this->_view . '.php';
		if (!file_exists($path)) {
			throw new LetterException(Yii::t('app', 'View file «{path}» not found.', array('{path}' => $path)));
		} else {
			$this->body = Yii::$app->getView()->renderFile($path, $this->_params);
		}
	}
}