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
        $source = null;
        if (!empty(\Yii::$app->controller)) {
            $controller = \Yii::$app->controller;

            $source = isset($controller->module) && $controller->module !== null
                ? $controller->module->getViewPath()
                : $controller->getViewPath();
        }

        if (empty($source)) {
            $source = \Yii::$app->basePath . '/views';
        }

        $path = $source . Component::get()->view_path . DIRECTORY_SEPARATOR . $view . '.php';
        if (!file_exists($path)) {
            throw new LetterException(\Yii::t('app', 'View file «{path}» not found.', ['path' => $path]));
        } else {
            $data['_code'] = $this->code;
            $data['_subject'] = $this->raw_subject;

            $this->body = \Yii::$app->getView()->renderFile($path, $data);
        }

        return $this;
    }
}