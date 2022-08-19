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

use LarkRestTest\Compare;
use stdClass;

/**
 * Response body
 *
 * @author Shay Anderson
 */
class ResponseBodySame extends Assertion
{
	/**
	 * Diff
	 *
	 * @var array
	 */
	private array $diff;

	/**
	 * Expected
	 *
	 * @var mixed
	 */
	private $expected;

	/**
	 * Init
	 *
	 * @param mixed $expected
	 */
	public function __construct($expected)
	{
		$this->expected = $expected;
	}

	/**
	 * @inheritDoc
	 */
	public function assert($responseBody, ?string $message = null): void
	{
		if (!$this->test($responseBody))
		{
			throw new AssertFailedException(
				$message ?? 'Response body is not the same as expected',
				[
					'diff' => $this->diff
				]
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function test($responseBody): bool
	{
		if (is_array($this->expected) && is_array($responseBody))
		{
			$this->diff = Compare::arrays($this->expected, $responseBody, '$$VAR');
		}
		else if ($this->expected instanceof stdClass && $responseBody instanceof stdClass)
		{
			$this->diff = Compare::objects($this->expected, $responseBody, '$$VAR');
		}
		else if (is_array($this->expected))
		{
			$this->diff = $this->expected;
		}
		else if ($this->expected instanceof stdClass)
		{
			$this->diff = (array)$this->expected;
		}
		else
		{
			return false;
		}

		return count($this->diff) === 0;
	}
}
