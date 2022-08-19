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

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Test directory handler
 *
 * @author Shay Anderson
 */
class Directory
{
	/**
	 * Classes
	 *
	 * @var DirectoryClass[]
	 */
	private array $classes = [];

	/**
	 * Init
	 *
	 * @param string $namespace
	 * @param string $directory
	 */
	public function __construct(string $namespace, string $directory)
	{
		if (!is_dir($directory))
		{
			throw new RestTestException('Directory "' . $directory . '" does not exist');
		}

		// load classes
		$dir = new RecursiveDirectoryIterator(
			$directory,
			RecursiveDirectoryIterator::SKIP_DOTS
		);

		foreach (new RecursiveIteratorIterator($dir) as $f)
		{
			/** @var \SplFileInfo $f */

			// check if test class
			if ($f->isFile() && strtolower(substr($f->getFilename(), -8)) === 'test.php')
			{
				$class = new DirectoryClass($f->getPathname(), $namespace, $directory);

				if ($class->isTestClass())
				{
					$this->classes[] = $class;
				}
			}
		}

		// sort classes by dependencies
		if ($this->classes)
		{
			$this->classes = & (new DependSorter($this->classes))->sort();
		}
	}

	/**
	 * Classes getter
	 *
	 * @return DirectoryClass[]
	 */
	public function &getClasses(): array
	{
		return $this->classes;
	}
}
