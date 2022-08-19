<?php
/**
 * Lark RestTest
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/lark-resttest/blob/master/LICENSE.md>
 * @link <https://github.com/shayanderson/lark-resttest>
*/
declare(strict_types=1);

namespace LarkRestTest\Assertion;

/**
 * Abstract assertion
 *
 * @author Shay Anderson
 */
abstract class Assertion
{
	/**
	 * Assert
	 *
	 * @param mixed $value
	 * @param string|null $message
	 * @return void
	 */
	abstract public function assert($value, ?string $message = null): void;

	/**
	 * Test $value pass/fail
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	abstract public function test($value): bool;

	/**
	 * Value to scalar for printing
	 *
	 * @param mixed $value
	 * @return void
	 */
	final protected static function valueToScalarOrType($value)
	{
		if ($value === null || is_scalar($value))
		{
			return $value;
		}

		return '[' . strtoupper(gettype($value)) . ']';
	}
}
