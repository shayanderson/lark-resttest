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

use Lark\Exception;
use stdClass;

/**
 * Compare values
 *
 * @author Shay Anderson
 */
class Compare
{
	/**
	 * Compare arrays (recursive)
	 *
	 * @param array $expected
	 * @param array $actual
	 * @param string|null $noCompareOnValue
	 * @return array
	 */
	public static function arrays(
		array $expected,
		array $actual,
		?string $noCompareOnValue = null
	): array
	{
		$diff = self::arraysRecurive($expected, $actual, $noCompareOnValue);

		// return diff
		if ($diff)
		{
			return $diff;
		}

		// flip compare
		return self::arraysRecurive($actual, $expected, $noCompareOnValue, true);
	}

	/**
	 * Compare arrays recursive
	 *
	 * @param array $a1
	 * @param array $a2
	 * @param string|null $noCompareOnValue
	 * @param boolean $flip
	 * @return array
	 */
	private static function arraysRecurive(
		array $a1,
		array $a2,
		?string $noCompareOnValue = null,
		bool $flip = false
	): array
	{
		$diff = [];

		foreach ($a1 as $k => $v)
		{
			// both keys must exist
			if (!array_key_exists($k, $a2))
			{
				$diff[$k] = $v;
				continue;
			}

			if (is_array($v))
			{
				// compare arrays
				$d = self::arraysRecurive($v, $a2[$k], $noCompareOnValue, $flip);

				if ($d)
				{
					$diff[$k] = $d;
					unset($d);
				}
			}
			if (is_object($v))
			{
				// can only compare stdClass
				if (!$v instanceof stdClass)
				{
					throw new Exception('Compared objects must be type of stdClass', [
						'type' => gettype($v)
					]);
				}

				// 2nd must be object also
				if (!is_object($a2[$k]))
				{
					$diff[$k] = $v;
					continue;
				}

				// can only compare stdClass for 2nd
				if (!$a2[$k] instanceof stdClass)
				{
					throw new Exception('Compared objects must be type of stdClass', [
						'type' => gettype($a2[$k])
					]);
				}

				// both objects are stdClass, compare as arrays
				$d = self::arraysRecurive((array)$v, (array)$a2[$k], $noCompareOnValue, $flip);

				if ($d)
				{
					$diff[$k] = $d;
					unset($d);
				}
			}
			else
			{
				if ($v !== $a2[$k])
				{
					if (!$flip && $noCompareOnValue && $v === $noCompareOnValue)
					{
						// ignore
						continue;
					}
					else if ($flip && $noCompareOnValue && $a2[$k] === $noCompareOnValue)
					{
						// ignore
						continue;
					}

					$diff[$k] = $v;
				}
			}
		}

		return $diff;
	}

	/**
	 * Compare objects (recursive)
	 *
	 * @param stdClass $expected
	 * @param stdClass $actual
	 * @param string|null $noCompareOnValue
	 * @return array
	 */
	public static function objects(
		stdClass $expected,
		stdClass $actual,
		?string $noCompareOnValue = null
	): array
	{
		// convert to arrays for compare
		return self::arrays((array)$expected, (array)$actual, $noCompareOnValue);
	}
}
