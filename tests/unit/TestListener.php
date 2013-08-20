<?php
/**
 * TestListener.php
 * @author Roman Revin
 * @link http://phptime.ru
 */

namespace postmantest;

class TestListener implements \PHPUnit_Framework_TestListener
{

	protected $timeTest = 0;

	protected $timeSuite = 0;

	public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
	{
		$text = $this->colorize("error", "red");
		echo "\t[{$text}]-";
	}

	public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
	{
		$text = $this->colorize("failed", "red");
		echo "\t[{$text}]-";
	}

	public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
	{
		$text = $this->colorize("incomplete");
		echo "\t\t[{$text}]-";
	}

	public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
	{
		$text = $this->colorize("skipped");
		echo "\t[{$text}]-";
	}

	public function startTest(\PHPUnit_Framework_Test $test)
	{
		$this->timeTest = microtime(1);
		$method = $this->colorize($test->getName(), 'green');
		echo "\n\t-> {$method}";
	}

	public function endTest(\PHPUnit_Framework_Test $test, $time)
	{
		$time = sprintf('%0.3f sec', microtime(1) - $this->timeTest);
		$count = $test->getCount();
		$tabs = ceil((29 - strlen($test->getName())) / 8);

		echo str_repeat("\t", $tabs) . "{$count} Assertions";
		echo $this->colorize("\t{$time}", 'green');
	}

	public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
	{
		$this->timeSuite = microtime(1);
		$text = $this->colorize($suite->getName(), 'blue');
		echo "\n\n{$text}";
	}

	public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
	{
		$time = sprintf('%0.3f sec', microtime(1) - $this->timeSuite);

		echo $this->colorize("\n\tTime: {$time}", 'green');
	}

	private function colorize($text, $color = 'yellow')
	{
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
		return "\033[{$color}m{$text}\033[0m";
	}
}