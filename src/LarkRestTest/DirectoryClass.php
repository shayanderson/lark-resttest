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

use ReflectionClass;
use ReflectionMethod;

/**
 * Test directory class
 *
 * @author Shay Anderson
 */
class DirectoryClass implements DependInterface
{
	/**
	 * Class dependencies
	 *
	 * @var array
	 */
	private array $depends = [];

	/**
	 * Class name
	 *
	 * @var string
	 */
	private string $name;

	/**
	 * Test class flag
	 *
	 * @var boolean
	 */
	private bool $isTestClass = true;

	/**
	 * Class path
	 *
	 * @var string
	 */
	private string $path;

	/**
	 * Methods
	 *
	 * @var DirectoryClassMethod[]
	 */
	private array $methods = [];

	/**
	 * Init
	 *
	 * @param string $path
	 * @param string $baseNamespace
	 * @param string $baseDirectory
	 */
	public function __construct(string $path, string $baseNamespace, string $baseDirectory)
	{
		$this->path = $path;
		$this->name = self::pathToClass($this->path, $baseNamespace, $baseDirectory);

		$rc = new ReflectionClass($this->name);

		// extract dependencies
		$docComment = $rc->getDocComment();

		if ($docComment !== false)
		{
			// match '@test'
			if (preg_match('/@ignore/', $docComment))
			{
				$this->isTestClass = false;
				return;
			}

			// match '@depends CLASS'
			preg_match_all('/@depends\s+(.*?)\n/s', $docComment, $m);

			if (isset($m[1]))
			{
				$this->depends = $m[1];

				foreach ($this->depends as &$dependency)
				{
					// make 'Ns\Class' => '\Ns\Class'
					$dependency = '\\' . ltrim($dependency, '\\');

					// ensure dependencies exist
					class_exists($dependency) or throw new RestTestException(
						'Dependency class "' . $dependency . '" not found for test class "'
							. $this->name . '" (@depends ' . $dependency . ')',
						[
							'testClass' => $this->name,
							'dependencyClass' => $dependency,
							'docComment' => $docComment
						]
					);
				}
			}
		}

		// set methods
		foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
		{
			$method = new DirectoryClassMethod($method);

			if ($method->isTest())
			{
				$this->methods[] = $method;
			}
		}

		// sort methods by dependencies
		if ($this->methods)
		{
			$this->methods = (new DependSorter($this->methods, $this->name))->sort();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getDepends(): array
	{
		return $this->depends;
	}

	/**
	 * Methods getter
	 *
	 * @return DirectoryClassMethod[]
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Path getter
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * Check if methods exist
	 *
	 * @return boolean
	 */
	public function hasMethods(): bool
	{
		return !empty($this->methods);
	}

	/**
	 * Check if test class
	 *
	 * @return boolean
	 */
	public function isTestClass(): bool
	{
		return $this->isTestClass;
	}

	/**
	 * Path to class
	 *
	 * @param string $path
	 * @param string $baseNamespace
	 * @param string $baseDirectory
	 * @return string
	 */
	private static function pathToClass(
		string $path,
		string $baseNamespace,
		string $baseDirectory
	): string
	{
		// rm base dir
		$class = substr($path, strlen($baseDirectory));

		// rm '.php'
		$class = substr($class, 0, -4);

		// replace dir seps with '\'
		$class = str_replace(DIRECTORY_SEPARATOR, '\\', $class);

		// add base namespace
		$class = '\\' . ltrim($baseNamespace, '\\') . $class;

		return $class;
	}
}
