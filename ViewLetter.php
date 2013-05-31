<?php
/**
 * ViewLetter.php
 * @author: Roman Revin <roma@quetzal.ru>
 * @date  : 31.05.13
 */

namespace yii\postman;

use Yii;
use yii\base\Event;

class ViewLetter extends Letter
{

	private $_is_html = true;
	private $_view = null;
	private $_params = array();

	public function __construct($subject, $view, $params = array(), $is_html = true)
	{
		parent::__construct();

		$this->_check_mailer();
		$this->_mailer->Subject = $subject;

		$this->_is_html = $is_html;
		$this->_mailer->IsHTML($is_html);

		$this->set_view($view)->set_params($params);

		$this->on(self::EVENT_BEFORE_SEND, array($this, 'before_send'));
	}

	public function set_view($view)
	{
		$this->_view = $view;
		return $this;
	}

	public function set_params($params)
	{
		$this->_params = $params;
		return $this;
	}

	public function before_send(Event $Event)
	{
		$type = $this->_is_html === true ? 'html' : 'raw';
		$type_alt = $type === 'html' ? 'raw' : 'html';

		$base_view_path = Yii::$app->getViewPath() . $this->_postman->default_view_path . DIRECTORY_SEPARATOR;

		$path = $base_view_path . $this->_view . '.' . $type . '.php';
		$path_alt = $base_view_path . $this->_view . '.' . $type_alt . '.php';

		if (!file_exists($path)) {
			throw new LetterException(Yii::t('app', 'View file "{path}" not found.', array('{path}' => $path)));
		} else {
			$this->_mailer->Body = Yii::$app->getView()->renderFile($path, $this->_params);
		}

		if (file_exists($path_alt)) {
			$this->_mailer->AltBody = Yii::$app->getView()->renderFile($path_alt, $this->_params);
		}
	}
}