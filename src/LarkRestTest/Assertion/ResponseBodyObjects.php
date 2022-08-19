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
 * Response body of objects assertion
 *
 * @author Shay Anderson
 */
class ResponseBodyObjects extends Assertion
{
	/**
	 * @inheritDoc
	 */
	public function assert($responseBody, ?string $message = null): void
	{
		if (!$this->test($responseBody))
		{
			throw new AssertFailedException(
				$message ?? 'Response body is not an array of objects',
				['responseBody' => $responseBody]
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function test($responseBody): bool
	{
		return is_array($responseBody) && reset($responseBody) instanceof stdClass;
	}
}
