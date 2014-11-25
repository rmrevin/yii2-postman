<?php
/**
 * TestCase.php
 * @author Roman Revin http://phptime.ru
 */

namespace rmrevin\yii\postman\tests\unit;

use yii\helpers\ArrayHelper;

/**
 * Class TestCase
 * @package rmrevin\yii\postman\tests\unit
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{

    public static $params;

    protected function setUp()
    {
        parent::setUp();
        $this->mock_application();

        \Yii::$app->getDb()->createCommand()
            ->delete($this->get_param('components')['postman']['table'])
            ->execute();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->destroy_application();
    }

    /**
     * Returns a test configuration param from /data/config.php
     * @param string $name params name
     * @param mixed $default default value to use when param is not set.
     * @return mixed the value of the configuration param
     */
    public function get_param($name, $default = null)
    {
        if (self::$params === null) {
            self::$params = require(__DIR__ . '/config/main.php');
            $main_local = __DIR__ . '/config/main-local.php';
            if (file_exists($main_local)) {
                self::$params = array_merge(self::$params, require($main_local));
            }
        }
        return isset(self::$params[$name]) ? self::$params[$name] : $default;
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param string $appClass
     */
    protected function mock_application($appClass = '\yii\console\Application')
    {
        // for update self::$params
        $this->get_param('id');

        /** @var \yii\console\Application $app */
        new $appClass(self::$params);
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroy_application()
    {
        \Yii::$app = null;
    }
}
