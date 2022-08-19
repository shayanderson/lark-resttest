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

use stdClass;

/**
 * Response body object assertion
 *
 * @author Shay Anderson
 */
class ResponseBodyObject extends Assertion
{
	/**
	 * @inheritDoc
	 */
	public function assert($responseBody, ?string $message = null): void
	{
		if (!$this->test($responseBody))
		{
			throw new AssertFailedException(
				$message ?? 'Response body is not an object',
				['responseBody' => $responseBody]
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function test($responseBody): bool
	{
		return $responseBody instanceof stdClass;
	}
}
