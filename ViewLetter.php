<?php
/**
 * ViewLetter.php
 * @author Roman Revin http://phptime.ru
 */

namespace rmrevin\yii\postman;

/**
 * Class ViewLetter
 * @package rmrevin\yii\postman
 */
class ViewLetter extends Letter
{

    /**
     * @param string $view
     * @param array $data
     * @return static
     * @throws \rmrevin\yii\postman\LetterException
     */
    public function setBodyFromView($view, $data = [])
    {
        $path = \Yii::$app->controller->module->getViewPath() . Component::get()->view_path . DIRECTORY_SEPARATOR . $view . '.php';
        if (!file_exists($path)) {
            throw new LetterException(\Yii::t('app', 'View file «{path}» not found.', ['path' => $path]));
        } else {
            $this->body = \Yii::$app->getView()->renderFile($path, $data);
        }

        return $this;
    }
}