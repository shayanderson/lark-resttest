<?php
/**
 * Lark RestTest
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/lark-resttest/blob/master/LICENSE.md>
 * @link <https://github.com/shayanderson/lark-resttest>
*/
declare(strict_types=1);

namespace LarkRestTest;

use Lark\Cli\Output;
use Lark\Timer;

/**
 * Test result
 *
 * @author Shay Anderson
 */
class TestResult
{
	/**
	 * Directory class object
	 *
	 * @var DirectoryClass
	 */
	private DirectoryClass $class;

	/**
	 * Test ID
	 *
	 * @var integer
	 */
	private int $id;

	/**
	 * Directory class method object
	 *
	 * @var DirectoryClassMethod
	 */
	private DirectoryClassMethod $method;

	/**
	 * Output object
	 *
	 * @var Output
	 */
	private Output $out;

	/**
	 * Global test ID
	 *
	 * @var integer
	 */
	private static int $testId = 0;

	/**
	 * Total test error count
	 *
	 * @var integer
	 */
	private static $testsError = 0;

	/**
	 * Total test success count
	 *
	 * @var integer
	 */
	private static $testsOk = 0;

	/**
	 * Total test warning count
	 *
	 * @var integer
	 */
	private static $testsWarn = 0;

	/**
	 * Init
	 *
	 * @param DirectoryClass $class
	 * @param Output $out
	 */
	public function __construct(DirectoryClass $class, Output $out)
	{
		$this->class = $class;
		$this->out = $out;
	}

	/**
	 * Add test
	 *
	 * @param DirectoryClassMethod $method
	 * @return void
	 */
	public function addTest(DirectoryClassMethod $method): void
	{
		self::$testId++;
		$this->id = self::$testId;
		$this->method = $method;
	}

	/**
	 * Output error
	 *
	 * @param string|null $text
	 * @return void
	 */
	public function error(string $text = null): void
	{
		self::$testsError++;
		$this->label(__FUNCTION__, $text ? '[ERROR] ' . $text : null);
	}

	/**
	 * ID getter
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return $this->id;
	}

	/**
	 * Output test label
	 *
	 * @param string $level
	 * @param string|null $text
	 * @param Timer|null $timer
	 * @param integer|null $reponseCode
	 * @return void
	 */
	public function label(
		string $level,
		?string $text = null,
		?Timer $timer = null,
		?int $reponseCode = null
	): void
	{
		$colorProp = 'colorLightGray';
		switch ($level)
		{
			case 'error':
				$colorProp = 'colorRed';
				break;

			case 'warn':
				$colorProp = 'colorYellow';
				break;
		}

		$this->out->$colorProp->echo(
			(isset($this->id) ? str_pad((string)$this->id, 3, '0', STR_PAD_LEFT) . ') ' : null),
			''
		);

		if (is_int($reponseCode))
		{
			$this->out->colorPurple->echo($reponseCode . ' ', '');
		}

		$this->out->$colorProp->echo(
			ltrim($this->class->getName(), '\\')
				. (isset($this->method) ? '::' . $this->method->getName() : null),
			''
		);

		$this->out->echo('  ', '');

		if (isset($this->method) && $this->method->hasDescription())
		{
			$this->out->dim(' # ' . $this->method->getDescription(), '');
		}

		if ($timer)
		{
			$this->out->dim(' [' . $timer->elapsed() . ']');
		}
		else
		{
			$this->out->echo();
		}

		if ($text)
		{
			$this->out->$colorProp->echo('  ' . $text);
		}
	}

	/**
	 * Output success message
	 *
	 * @param Timer $timer
	 * @param integer|null $responseCode
	 * @return void
	 */
	public function ok(Timer $timer, ?int $responseCode = null): void
	{
		self::$testsOk++;
		$this->label(__FUNCTION__, null, $timer, $responseCode);
	}

	/**
	 * Print summary
	 *
	 * @param Timer $timer
	 * @return void
	 */
	public function summary(Timer $timer): void
	{
		$tTime = $timer->elapsed();

		if (self::$testsError || self::$testsWarn)
		{
			$text = f(
				' (Tests: {}, Assertions: {}, Errors: {}, Warnings: {}) in {}',
				self::$testId,
				Assert::getCount(),
				self::$testsError,
				self::$testsWarn,
				$tTime
			);
		}
		else
		{
			$text = f(
				' (Tests: {}, Assertions: {}) in {}',
				self::$testId,
				Assert::getCount(),
				$tTime
			);
		}


		if (self::$testsError)
		{
			$this->out->error('Error!', '');
			$this->out->colorRed->echo($text);
		}
		else if (self::$testsWarn)
		{
			$this->out->bgYellow->colorBlack->echo('Warning', '');
			$this->out->warn($text);
		}
		else
		{
			$this->out->bgLightGreen->colorBlack->echo('OK', '');
			$this->out->colorLightGreen->echo($text);
		}
	}

	public function warn(string $text): void
	{
		self::$testsWarn++;
		$this->label(__FUNCTION__, '[WARN] ' . $text);
	}
}
