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

use Countable;

/**
 * Response body count assertion
 *
 * @author Shay Anderson
 */
class ResponseBodyCount extends Assertion
{
	/**
	 * Count
	 *
	 * @var integer
	 */
	private int $count;

	/**
	 * Init
	 *
	 * @param integer $count
	 */
	public function __construct(int $count)
	{
		$this->count = $count;
	}

	/**
	 * @inheritDoc
	 */
	public function assert($responseBody, ?string $message = null): void
	{
		if (!$this->test($responseBody))
		{
			throw new AssertFailedException($message ?? f(
				'Response body count is not {}',
				$this->count
			), ['responseBody' => $responseBody]);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function test($responseBody): bool
	{
		if (!is_array($responseBody) && !$responseBody instanceof Countable)
		{
			return false;
		}

		return count($responseBody) === $this->count;
	}
}
