<?php
/**
 * ViewLetter.php
 * @author: Roman Revin <xgismox@gmail.com>
 * @date  : 31.05.13
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
	 * @param string $subject subject message
	 * @param string $view    name of view file
	 * @param array  $params  params for view
	 * @param bool   $is_html
	 */
	public function __construct($subject, $view, $params = array(), $is_html = true)
	{
		parent::__construct();

		$this->subject = $subject;
		$this->is_html = $is_html;

		$this->set_view($view)->set_params($params);

		$this->on(self::EVENT_BEFORE_SEND, array($this, 'before_send'));
	}

	/**
	 * method sets name of view file
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
	 * method sets params for view
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
	 * method event before save
	 *
	 * @param Event $Event
	 * @throws LetterException
	 */
	public function before_send(Event $Event)
	{
		$type = $this->is_html === true ? 'html' : 'raw';
		$type_alt = $type === 'html' ? 'raw' : 'html';

		$base_view_path = Yii::$app->getViewPath() . $this->_postman->view_path . DIRECTORY_SEPARATOR;

		$path = $base_view_path . $this->_view . '.' . $type . '.php';
		$path_alt = $base_view_path . $this->_view . '.' . $type_alt . '.php';

		if (!file_exists($path)) {
			throw new LetterException(Yii::t('app', 'View file «{path}» not found.', array('{path}' => $path)));
		} else {
			$this->body = Yii::$app->getView()->renderFile($path, $this->_params);
		}

		if (file_exists($path_alt)) {
			$this->alt_body = Yii::$app->getView()->renderFile($path_alt, $this->_params);
		}
	}
}