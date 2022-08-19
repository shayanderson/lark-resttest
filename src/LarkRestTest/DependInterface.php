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

/**
 * Depend interface
 *
 * @author Shay Anderson
 */
interface DependInterface
{
	/**
	 * Dependencies getter
	 *
	 * @return array
	 */
	public function getDepends(): array;

	/**
	 * Name getter
	 *
	 * @return string
	 */
	public function getName(): string;
}
