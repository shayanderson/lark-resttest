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

use Lark\Cli;
use Lark\Cli\Output;
use Lark\Exception as LarkException;
use Lark\Http\Client;
use Lark\Json\Decoder as JsonDecoder;
use Lark\Timer;
use stdClass;
use Throwable;

/**
 * REST test
 *
 * @author Shay Anderson
 */
abstract class RestTest extends Assert
{
	/**
	 * Base URL used for client
	 *
	 * @var string
	 */
	private string $baseUrl;

	/**
	 * Client object (lazy)
	 *
	 * @var Client|null
	 */
	private ?Client $client;

	/**
	 * IDs used in tests
	 *
	 * @var array
	 */
	private static array $ids = [];

	/**
	 * Client options
	 *
	 * @var array
	 */
	private array $options;

	/**
	 * Test client
	 *
	 * @var Client|null
	 */
	private static ?Client $testClient = null;

	/**
	 * Test client request info
	 *
	 * @var array|null
	 */
	private static ?array $testClientRequest = null;
	/**
	 * Test client response body
	 *
	 * @var string|null
	 */
	private static ?string $testClientResponseBody = null;

	/**
	 * Test client response code
	 *
	 * @var integer|null
	 */
	private static ?int $testClientResponseCode = null;

	/**
	 * CLI object getter
	 *
	 * @return Cli
	 */
	private static function cli(): Cli
	{
		return Cli::getInstance();
	}

	/**
	 * Setup client for tests
	 *
	 * @param string $baseUrl
	 * @param array $options
	 * @return void
	 */
	final protected function client(string $baseUrl, array $options = [])
	{
		$this->baseUrl = $baseUrl;
		$this->options = $options + ['headers' => ['content-type' => 'application/json']];
	}

	/**
	 * Client DELETE
	 *
	 * @param string $path
	 * @param array|stdClass|null $params
	 * @param array $options
	 * @return Client
	 */
	final protected function clientDelete(string $path, $params = null, array $options = []): Client
	{
		return $this->clientFetch('delete', $path, self::jsonParams($params), $options);
	}

	/**
	 * Client request method
	 *
	 * @param string $method
	 * @param string $path
	 * @param array|stdClass|null $params
	 * @param array $options
	 * @return Client
	 */
	private function clientFetch(string $method, string $path, $params, array $options): Client
	{
		$this->client = null; // reset

		$client = &$this->clientUse(true);

		$url = $this->baseUrl . '/' . ltrim($path, '/');

		// set for run access
		self::$testClient = &$client;
		self::$testClientRequest = [strtoupper($method), $url, $client->getOptions()];

		// store response
		self::$testClientResponseBody = $client->{$method}($url, $params, $options);

		self::$testClientResponseCode = $client->statusCode();

		return $client;
	}

	/**
	 * Client GET
	 *
	 * @param string $path
	 * @param array $params
	 * @param array $options
	 * @return Client
	 */
	final protected function clientGet(string $path, array $params = [], array $options = []): Client
	{
		return $this->clientFetch('get', $path, $params, $options);
	}

	/**
	 * Client PATCH
	 *
	 * @param string $path
	 * @param array|stdClass|null $params
	 * @param array $options
	 * @return Client
	 */
	final protected function clientPatch(string $path, $params = null, array $options = []): Client
	{
		return $this->clientFetch('patch', $path, self::jsonParams($params), $options);
	}

	/**
	 * Client POST
	 *
	 * @param string $path
	 * @param array|stdClass|null $params
	 * @param array $options
	 * @return Client
	 */
	final protected function clientPost(string $path, $params = null, array $options = []): Client
	{
		return $this->clientFetch('post', $path, self::jsonParams($params), $options);
	}

	/**
	 * Client PUT
	 *
	 * @param string $path
	 * @param array|stdClass|null $params
	 * @param array $options
	 * @return Client
	 */
	final protected function clientPut(string $path, $params = null, array $options = []): Client
	{
		return $this->clientFetch('put', $path, self::jsonParams($params), $options);
	}

	/**
	 * Client (lazy) getter
	 *
	 * @param boolean $autoCreate
	 * @return Client
	 */
	private function &clientUse(bool $autoCreate = false): Client
	{
		if (!isset($this->baseUrl))
		{
			throw new RestTestException(
				'No client found, must call client() method before running client tests',
				[
					'caller' => static::class
				]
			);
		}

		if (!$this->client)
		{
			if (!$autoCreate)
			{
				throw new RestTestException(
					'Client test method must be called before using client checks'
				);
			}

			$this->client = new Client($this->options);
		}

		return $this->client;
	}

	/**
	 * Echo
	 *
	 * @param string $text
	 * @param string $end
	 * @return void
	 */
	private static function echo($text = null, string $end = PHP_EOL): void
	{
		self::out()->echo($text, $end);
	}

	/**
	 * Expect response body count (or array items)
	 *
	 * @param integer $count
	 * @param string|null $message
	 * @return void
	 */
	final protected function expectBodyCount(int $count, ?string $message = null): void
	{
		$this->assertResponseBodyCount($count, self::responseBodyJsonDecoded(), $message);
	}

	/**
	 * Expect response body object
	 *
	 * @param string|null $message
	 * @return void
	 */
	final protected function expectBodyObject(?string $message = null): void
	{
		$this->assertResponseBodyObject(self::responseBodyJsonDecoded(), $message);
	}

	/**
	 * Expect response body array of objects
	 *
	 * @param string|null $message
	 * @return void
	 */
	final protected function expectBodyObjects(?string $message = null): void
	{
		$this->assertResponseBodyObjects(self::responseBodyJsonDecoded(), $message);
	}

	/**
	 * Expect response body same as $expected array or object
	 *
	 * @param array|object $expected
	 * @param string|null $message
	 * @return void
	 */
	final protected function expectBodySame($expected, ?string $message = null): void
	{
		// auto encode/decode JSON to set correct types
		$expected = JsonDecoder::decode(
			json_encode($expected)
		);

		$this->assertResponseBodySame($expected, self::responseBodyJsonDecoded(), $message);
	}

	/**
	 * Expect response body same as $expected array or object with auto response body sorting
	 *
	 * @param array|object $expected
	 * @param string|null $message
	 * @return void
	 */
	final protected function expectBodySameSorted(
		$expected,
		string $sortField = 'id',
		?string $message = null
	): void
	{
		// auto encode/decode JSON to set correct types
		$expected = JsonDecoder::decode(
			json_encode($expected)
		);

		$resBody = self::responseBodyJsonDecoded();

		if (!is_array($expected))
		{
			throw new RestTestException('Cannot auto sort $expected, must be array');
		}

		// sort expected by sort field
		usort(
			$expected,
			fn (stdClass $a, stdClass $b) => strcmp($a->{$sortField}, $b->{$sortField})
		);

		if (!is_array($resBody))
		{
			throw new RestTestException('Cannot auto sort response body, must be array');
		}

		// sort response body by sort field
		usort(
			$resBody,
			fn (stdClass $a, stdClass $b) => strcmp($a->{$sortField}, $b->{$sortField})
		);

		$this->assertResponseBodySame($expected, $resBody, $message);
	}

	/**
	 * Expect response status code
	 *
	 * @param integer $code
	 * @param string|null $message
	 * @return void
	 */
	final protected function expectCode(int $code, ?string $message = null): void
	{
		$this->assertResponseCode($code, $this->clientUse(), $message);
	}

	/**
	 * Expect response status code 200
	 *
	 * @param string|null $message
	 * @return void
	 */
	final protected function expectCodeOk(?string $message = null): void
	{
		$this->assertResponseCode(200, $this->clientUse(), $message);
	}

	/**
	 * IDs getter for specific name/test that can be used in tests
	 *
	 * @param string $name
	 * @param int $index
	 * @param boolean $clearId Will clear the ID
	 * @return string
	 */
	final protected function id(string $name, ?int $index = null, bool $clearId = false): string
	{
		if (!isset(self::$ids[$name]))
		{
			throw new RestTestException(
				'Cannot get ID for name "' . $name . '", name does not exist in IDs'
			);
		}

		if (!count(self::$ids[$name]))
		{
			throw new RestTestException(
				'Cannot get ID for name "' . $name . '", there are zero IDs for name'
			);
		}

		// use index
		if ($index !== null)
		{
			if ($index < 1)
			{
				throw new RestTestException('ID index must be greater than 1');
			}

			$indexOrig = $index;
			$index -= 1;

			if (!isset(self::$ids[$name][$index]))
			{
				throw new RestTestException('ID index ' . $indexOrig . ' does not exist');
			}

			if ($clearId)
			{
				$id = self::$ids[$name][$index];
				unset(self::$ids[$name][$index]);
				return $id;
			}

			return self::$ids[$name][$index];
		}

		if ($clearId)
		{
			reset(self::$ids[$name]);
			return array_shift(self::$ids[$name]);
		}

		$id = current(self::$ids[$name]);

		if ($id === false) // end
		{
			reset(self::$ids[$name]);
			$id = current(self::$ids[$name]);
		}

		next(self::$ids[$name]);

		return $id;
	}

	/**
	 * IDs setter for specific name/test that can be used in tests
	 *
	 * @param string $name
	 * @return void
	 */
	final protected function ids(string $name): void
	{
		$res = $this->responseBodyJsonDecoded();

		if (is_array($res))
		{
			foreach ($res as $object)
			{
				$this->idsObject($name, $object);
			}

			return;
		}

		if ($res instanceof stdClass)
		{
			$this->idsObject($name, $res);
			return;
		}

		throw new RestTestException(
			'Cannot extract IDs from response body that is not an array of objects or an object',
			[
				'class' => static::class,
				'responseBody' => $res
			]
		);
	}

	/**
	 * Extract ID in object
	 *
	 * @param string $name
	 * @param stdClass $object
	 * @return void
	 */
	private function idsObject(string $name, stdClass $object): void
	{
		if (!$object instanceof stdClass)
		{
			throw new RestTestException(
				'Cannot extract ID from value that is not an object',
				[
					'class' => static::class,
					'object' => $object
				]
			);
		}

		if (!isset($object->id))
		{
			throw new RestTestException(
				'Cannot extract ID from object, object must have field "id"',
				[
					'class' => static::class,
					'object' => $object
				]
			);
		}

		self::$ids[$name][] = $object->id;
	}

	/**
	 * Encode JSON params
	 *
	 * @param array|object|null $params
	 * @return void
	 */
	private static function jsonParams($params)
	{
		if ($params)
		{
			$params = json_encode($params);
		}

		return $params;
	}

	/**
	 * Output object getter
	 *
	 * @return Output
	 */
	private static function out(): Output
	{
		return self::cli()->output();
	}

	/**
	 * Response body getter
	 *
	 * @return string|null
	 */
	final protected static function responseBody(): ?string
	{
		return self::$testClientResponseBody;
	}

	/**
	 * Response body JSON decoded getter
	 *
	 * @return void
	 */
	private static function responseBodyJsonDecoded()
	{
		if (self::$testClientResponseBody === null || self::$testClientResponseBody === '')
		{
			return;
		}

		try
		{
			return JsonDecoder::decode(self::$testClientResponseBody);
		}
		catch (Throwable $th)
		{
		}

		return null;
	}

	/**
	 * Run all tests in directory
	 *
	 * @param string $namespace
	 * @param string $directory
	 * @param boolean $debug
	 * @return void
	 */
	final public static function run(
		string $namespace,
		string $directory,
		bool $debug = false
	): void
	{
		self::echo('Running tests...');
		self::sep();
		self::out()->dim('  Base namespace: ', '');
		self::echo($namespace);
		self::out()->dim('  Base directory: ', '');
		self::echo($directory);

		if ($debug)
		{
			self::out()->dim('  Debug mode: ', '');
			self::echo('on');
		}

		$testClass = null;
		$testObj = null;
		$testMethod = null;
		$testResult = null;

		$timer = new Timer;

		$print_summary = function (bool $isError = false) use (&$testResult, &$timer)
		{
			if ($testResult)
			{
				if ($isError)
				{
					$testResult->error();
				}

				self::sep();
				$testResult->summary($timer);
				self::echo();
			}
		};

		$print_test_req = function (string $indent = '')
		{
			if (self::$testClientRequest)
			{
				self::sep();
				self::out()->styleBold->echo($indent . 'REQUEST');
				self::out()->dim($indent . 'URL: ', '');
				self::echo(self::$testClientRequest[1]);
				self::out()->dim($indent . 'Method: ', '');
				self::echo(self::$testClientRequest[0]);

				if (isset(self::$testClientRequest[2]['headers']))
				{
					$headers = '';
					foreach (self::$testClientRequest[2]['headers'] as $k => $v)
					{
						$headers .= ($headers ? '; ' : null) . $k . ': ' . $v;
					};

					self::out()->dim($indent . 'Headers: ', '');
					self::echo($headers);
				}
			}
		};

		$print_test_res = function (string $indent = '')
		{
			if (self::$testClient)
			{
				self::sep();
				self::out()->styleBold->echo($indent . 'RESPONSE');
				self::out()->dim($indent . 'Status Code: ', '');
				self::echo(self::$testClient->statusCode());
				self::out()->dim($indent . 'Response:');
				self::echo($indent . self::$testClientResponseBody);
			}
		};

		try
		{
			$dir = new Directory($namespace, $directory);

			$classes = $dir->getClasses();

			$classNames = [];
			foreach ($classes as $class)
			{
				$classNames[] = $class->getName();
			}

			self::out()->dim('  Test classes: ', '');
			self::echo(count($classes));
			self::sep();

			foreach ($classes as $testClass)
			{
				$testResult = new TestResult($testClass, self::out());

				if (!$testClass->hasMethods())
				{
					$testResult->warn('Test class has no test methods (use @test)');
					continue;
				}

				$testObj = new ($testClass->getName());

				foreach ($testClass->getMethods() as $testMethod)
				{
					$testTimer = new Timer;
					$testResult->addTest($testMethod);
					$testObj->{$testMethod->getName()}();

					$testResult->ok($testTimer, self::$testClientResponseCode);

					if ($debug)
					{
						$print_test_req('    ');
						$print_test_res('    ');
						self::sep();
					}

					// always reset
					self::$testClient = null;
					self::$testClientRequest = null;
					self::$testClientResponseBody = null;
					self::$testClientResponseCode = null;
				}
			}

			$print_summary();
		}
		// handle exception
		catch (Throwable $th)
		{
			$print_summary(true);

			self::out()->error($th->getMessage());
			self::echo();

			self::out()->dim('Type: ', '');
			self::echo($th::class);
			self::out()->dim('File: ', '');
			self::echo($th->getFile(), '');
			self::out()->dim(':', '');
			self::out()->colorCyan->echo($th->getLine());

			if ($testClass)
			{
				self::out()->dim('Test Class Path: ', '');
				self::echo($testClass->getPath());

				if (!$testMethod)
				{
					self::out()->dim('Test Class: ', '');
					self::echo($testClass->getName());
				}
				// test method
				else
				{
					self::out()->dim('Class Test: ', '');
					self::echo($testClass->getName() . '::' . $testMethod->getName());
				}
			}

			$print_test_req();

			$print_test_res();

			if ($debug)
			{
				self::sep();
				self::out()->styleBold->echo('EXECPTION TRACE');
				self::echo($th->getTraceAsString());
			}

			if ($th instanceof LarkException && $th->getContext())
			{
				self::sep();
				self::out()->styleBold->echo('EXECPTION CONTEXT');
				pa($th->getContext());
			}

			self::echo();
			self::cli()->exit(1);
		}

		self::echo();
	}

	/**
	 * Output separator
	 *
	 * @return void
	 */
	protected static function sep(): void
	{
		$sep = str_repeat('-', 90);

		self::out()->colorGray->echo($sep);
	}
}
