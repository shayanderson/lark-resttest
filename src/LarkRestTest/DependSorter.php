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
 * Depend sort for DependInterface objects
 *
 * @author Shay Anderson
 */
class DependSorter
{
	/**
	 * Base class name
	 *
	 * @var string|null
	 */
	private ?string $baseClass;

	/**
	 * Object dependencies
	 *
	 * @var array
	 */
	private array $depends = [];

	/**
	 * DependInterface objects
	 *
	 * @var array<string,DependInterface>
	 */
	private array $objects = [];

	/**
	 * Sorted objects
	 *
	 * @var array
	 */
	private array $sorted = [];

	/**
	 * Init
	 *
	 * @param DependInterface[] $objects
	 *
	 * @throws RestTestException If $objects is empty
	 */
	public function __construct(array &$objects, string $baseClass = null)
	{
		if (!$objects)
		{
			throw new RestTestException('Failed to sort, cannot sort empty array of objects');
		}

		foreach ($objects as &$obj)
		{
			$this->objects[$obj->getName()] = &$obj;
			$this->depends[$obj->getName()] = $obj->getDepends();
		}

		$this->baseClass = $baseClass;
	}

	/**
	 * Output ID name
	 *
	 * @param string $id
	 * @return string
	 */
	private function idName(string $id): string
	{
		if ($this->baseClass)
		{
			return $this->baseClass . '::' . $id;
		}

		return $id;
	}

	/**
	 * Pull object from objects
	 *
	 * @param string $id
	 * @return DependInterface|null
	 */
	private function &pullObject(string $id): ?DependInterface
	{
		if (!isset($this->objects[$id]))
		{
			$none = null;
			return $none;
		}

		$obj = $this->objects[$id];
		unset($this->objects[$id]);
		return $obj;
	}

	/**
	 * Push object to sorted
	 *
	 * @param string $id
	 *
	 * @throws RestTestException If dependencies do not exist
	 * @throws RestTestException If cyclic dependencies detected
	 *
	 * @return void
	 */
	private function pushObject(string $id): void
	{
		$obj = $this->pullObject($id);

		if (!$obj)
		{
			return; // nothing to do
		}

		foreach ($this->depends[$id] as $depend)
		{
			if (!isset($this->depends[$depend]))
			{
				throw new RestTestException(
					'Failed to sort, dependencies do not exist for "' . $this->idName($depend) . '"'
				);
			}

			// check for cycle
			if (in_array($id, $this->depends[$depend]))
			{
				throw new RestTestException(
					'Failed to sort, "' . $this->idName($depend) . '" and "' . $this->idName($id)
						. '" cannot depend on each other (cyclic dependencies)'
				);
			}

			$this->pushObject($depend);
		}

		$this->sorted[$id] = $obj;
	}

	/**
	 * Sort objects and return sorted objects
	 *
	 * @throws RestTestException If object already sorted
	 * @throws RestTestException If objects all have dependencies
	 *
	 * @return array<string,DependInterface>
	 */
	public function &sort(): array
	{
		if ($this->sorted)
		{
			return $this->sorted;
		}

		// add all objects with no dependencies to sorted
		foreach ($this->depends as $id => $depends)
		{
			if (!$depends)
			{
				$obj = &$this->pullObject($id);

				if (!$obj)
				{
					throw new RestTestException(
						'Failed to sort object "' . $this->idName($id) . '", object already sorted'
					);
				}

				$this->sorted[$id] = $obj;
			}
		}

		if (!$this->sorted)
		{
			throw new RestTestException(
				'Failed to sort, must have at least one object without dependencies'
			);
		}

		// sort remaining objects with dependencies
		while ($this->objects)
		{
			$this->pushObject(current($this->objects)->getName());
		}

		return $this->sorted;
	}
}
