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

use Lark\Http\Client;
use LarkRestTest\Assertion\Assertion;
use LarkRestTest\Assertion\ResponseBodyCount;
use LarkRestTest\Assertion\ResponseBodyObject;
use LarkRestTest\Assertion\ResponseBodyObjects;
use LarkRestTest\Assertion\ResponseBodySame;
use LarkRestTest\Assertion\ResponseCode;

/**
 * Assert
 *
 * @author Shay Anderson
 */
abstract class Assert
{
	/**
	 * Assertions count
	 *
	 * @var integer
	 */
	private static int $count = 0;

	/**
	 * Assert
	 *
	 * @param mixed $value
	 * @param Assertion $assertion
	 * @param string|null $message
	 * @return void
	 */
	private static function assert($value, Assertion $assertion, ?string $message): void
	{
		self::$count++;
		$assertion->assert($value, $message);
	}

	/**
	 * Assert response body count
	 *
	 * @param integer $count
	 * @param mixed $responseBody
	 * @param string|null $message
	 * @return void
	 */
	final public static function assertResponseBodyCount(
		int $count,
		$responseBody,
		?string $message = null
	): void
	{
		static::assert($responseBody, new ResponseBodyCount($count), $message);
	}

	/**
	 * Assert response body as object
	 *
	 * @param mixed $responseBody
	 * @param string|null $message
	 * @return void
	 */
	final public static function assertResponseBodyObject(
		$responseBody,
		?string $message = null
	): void
	{
		static::assert($responseBody, new ResponseBodyObject, $message);
	}

	/**
	 * Assert response body as array of objects
	 *
	 * @param mixed $responseBody
	 * @param string|null $message
	 * @return void
	 */
	final public static function assertResponseBodyObjects(
		$responseBody,
		?string $message = null
	): void
	{
		static::assert($responseBody, new ResponseBodyObjects, $message);
	}

	/**
	 * Assert response body as same
	 *
	 * @param mixed $expected
	 * @param mixed $responseBody
	 * @param string|null $message
	 * @return void
	 */
	final public static function assertResponseBodySame(
		$expected,
		$responseBody,
		?string $message = null
	): void
	{
		static::assert($responseBody, new ResponseBodySame($expected), $message);
	}

	/**
	 * Assert response code
	 *
	 * @param int $expected
	 * @param Client $client
	 * @param string|null $message
	 * @return void
	 */
	final public static function assertResponseCode(
		int $expected,
		Client $client,
		?string $message = null
	): void
	{
		static::assert($client->statusCode(), new ResponseCode($expected), $message);
	}

	/**
	 * Assertions count getter
	 *
	 * @return integer
	 */
	final public static function getCount(): int
	{
		return self::$count;
	}
}
