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
     * @param string $view view file name or alias
     * @param array $data
     * @param string $viewPath
     * @return static
     * @throws \rmrevin\yii\postman\LetterException
     */
    public function setBodyFromView($view, $data = [], $viewPath = null)
    {
        $controller = \Yii::$app->controller;

        if (!empty($controller) && empty($viewPath)) {
            $viewPath = isset($controller->module) && $controller->module !== null
                ? $controller->module->getViewPath()
                : $controller->getViewPath();
        }

        if (empty($viewPath)) {
            $viewPath = \Yii::$app->basePath . '/views';
        }

        if (strncmp($view, '@', 1) === 0) {
            // example $view = '@app/view/email/letter-text.php'
            $path = \Yii::getAlias($view);
        } else {
            // example $view = 'letter-text';
            // expand to '/app/views/email/letter-text.php'
            //        or '/app/modules/ModuleName/views/email/letter-text.php'
            $path = \Yii::getAlias($viewPath) . Component::get()->view_path . DIRECTORY_SEPARATOR . $view . '.php';
        }

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