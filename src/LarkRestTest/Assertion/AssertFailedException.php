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

use Lark\Exception;

/**
 * Assert failed exception
 *
 * @author Shay Anderson
 */
class AssertFailedException extends Exception
{
	/**
	 * Init
	 *
	 * @param string $message
	 * @param array $context
	 * @param int $code
	 * @param \Throwable $previous
	 */
	public function __construct(
		string $message = '',
		array $context = null,
		int $code = 0,
		\Throwable $previous = null
	)
	{
		parent::__construct('Assert failed: ' . $message, $context, $code, $previous);
	}
}
