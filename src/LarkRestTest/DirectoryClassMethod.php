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

use ReflectionMethod;

/**
 * Test directory class method
 *
 * @author Shay Anderson
 */
class DirectoryClassMethod implements DependInterface
{
	/**
	 * Class method dependencies
	 *
	 * @var array
	 */
	private array $depends = [];

	/**
	 * Method description
	 *
	 * @var string|null
	 */
	private ?string $description = null;

	/**
	 * Method name
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Test
	 *
	 * @var boolean
	 */
	private bool $isTest = false;

	/**
	 * Init
	 *
	 * @param ReflectionMethod $method
	 */
	public function __construct(ReflectionMethod $method)
	{
		$this->name = $method->getName();

		// do not use constructor for now
		if ($this->name === '__construct')
		{
			$this->isTest = false;
			return;
		}

		$docComment = $method->getDocComment();

		if ($docComment !== false)
		{
			// match '@test'
			if (preg_match('/@test/', $docComment))
			{
				$this->isTest = true;
			}

			if (!$this->isTest)
			{
				return;
			}

			$lines = explode("\n", $docComment);
			if (isset($lines[1]))
			{
				$this->description = ltrim(trim($lines[1]), ' *');
				unset($lines);
			}

			// match '@depends METHOD'
			preg_match_all('/@depends\s+(.*?)\n/s', $docComment, $m);

			if (isset($m[1]))
			{
				$this->depends = $m[1];
			}
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
	 * Description getter
	 *
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Check if description exists
	 *
	 * @return boolean
	 */
	public function hasDescription(): bool
	{
		return $this->description !== null;
	}

	/**
	 * Check if test method
	 *
	 * @return boolean
	 */
	public function isTest(): bool
	{
		return $this->isTest;
	}
}
