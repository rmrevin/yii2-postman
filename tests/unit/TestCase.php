<?php
/**
 * TestCase.php
 * @author Roman Revin http://phptime.ru
 */

namespace rmrevin\yii\postman\tests\unit;

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

        $this->mockApplication();

        \Yii::$app->getDb()->createCommand()
            ->delete($this->getParam('components')['postman']['table'])
            ->execute();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    /**
     * Returns a test configuration param from config
     * @param string $name params name
     * @param mixed $default default value to use when param is not set.
     * @return mixed the value of the configuration param
     */
    public function getParam($name, $default = null)
    {
        if (static::$params === null) {
            static::$params = require(__DIR__ . '/config/main.php');
            $main_local = __DIR__ . '/config/main-local.php';
            if (file_exists($main_local)) {
                static::$params = array_merge(static::$params, require($main_local));
            }
        }

        return isset(static::$params[$name]) ? static::$params[$name] : $default;
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param string $appClass
     */
    protected function mockApplication($appClass = '\yii\console\Application')
    {
        // for update static::$params
        $this->getParam('id');

        /** @var \yii\console\Application $app */
        new $appClass(static::$params);
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        \Yii::$app = null;
    }
}
