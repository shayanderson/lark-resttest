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
 * Response code assertion
 *
 * @author Shay Anderson
 */
class ResponseCode extends Assertion
{
	/**
	 * Expected code
	 *
	 * @var integer
	 */
	private int $expectedCode;

	/**
	 * Init
	 *
	 * @param integer $expectedCode
	 */
	public function __construct(int $expectedCode)
	{
		$this->expectedCode = $expectedCode;
	}

	/**
	 * @inheritDoc
	 */
	public function assert($responseCode, ?string $message = null): void
	{
		if (!$this->test($responseCode))
		{
			throw new AssertFailedException($message ?? f(
				'Response code {} is not the same as expected response code {}',
				self::valueToScalarOrType($responseCode),
				$this->expectedCode
			));
		}
	}

	/**
	 * @inheritDoc
	 */
	public function test($responseCode): bool
	{
		return $responseCode === $this->expectedCode;
	}
}
