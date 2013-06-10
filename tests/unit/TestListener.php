<?php
/**
 * VideoTest.php
 * @author: Roman Revin <xgismox@gmail.com>
 * @date  : 09.06.13
 */

namespace postmantest;

class TestListener implements \PHPUnit_Framework_TestListener
{

	protected $timeTest = 0;

	protected $timeSuite = 0;

	public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
	{
		echo "\t[";
		echo $this->colorize("error", "red");
		echo "]-";
	}

	public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
	{
		echo "\t[";
		echo $this->colorize("failed", "red");
		echo "]-";
	}

	public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
	{
		echo "\t\t[";
		echo $this->colorize("incomplete");
		echo "]-";
	}

	public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
	{
		echo "\t[";
		echo $this->colorize("skipped");
		echo "]-";
	}

	public function startTest(\PHPUnit_Framework_Test $test)
	{
		$this->timeTest = microtime(1);
		$method = $this->colorize($test->getName(), 'green');

		echo "\n\t-> " . $method;
	}

	public function endTest(\PHPUnit_Framework_Test $test, $time)
	{
		$time = sprintf('%0.3f sec', microtime(1) - $this->timeTest);

		echo "\t\t" . $test->getCount() . '(Assertions)';
		echo $this->colorize("\t" . $time, 'green');
	}

	public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
	{
		$this->timeSuite = microtime(1);
		echo "\n\n" . $this->colorize($suite->getName(), 'blue');
	}

	public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
	{
		$time = sprintf('%0.3f sec', microtime(1) - $this->timeSuite);

		echo $this->colorize("\nTime: " . $time, 'green');
	}

	private function colorize($text, $color = 'yellow')
	{
		return $text;

		switch ($color) {
			case 'red':
				$color = "1;31";
				break;
			case 'green':
				$color = "1;32";
				break;
			case 'blue':
				$color = "1;34";
				break;
			case 'white':
				$color = "1;37";
				break;
			default:
				$color = "1;33";
				break;
		}
		return "\033[" . $color . 'm' . $text . "\033[0m";
	}
}