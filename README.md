# Lark RestTest

RestTest is a REST API test library for testing REST API endpoints.

## Install

```
composer require lark/resttest
```

## Setup a Test Directory

Create a test directory, for example `./tests/Rest` and add a run tests file like `./tests/Rest/run.php`:

```php
<?php
use LarkRestTest\RestTest;

require_once '../../app/bootstrap.php';

// setup autoloading for tests/Rest dir
// this can also be done in composer.json using require-dev
$loader = new Composer\Autoload\ClassLoader;
$loader->addPsr4('Tests\\', dirname(__DIR__, 1));
$loader->register();

// handle options, like debugging
$argv = $_SERVER['argv'] ?? [];
$isDebug = false;
if (in_array('--debug', $argv) || in_array('-d', $argv))
{
    $isDebug = true;
}

// run tests using base namespace and directory
RestTest::run('Tests\Rest', __DIR__, $isDebug);
```

## Create a Test Class

A test class can be created the `./tests/Rest` directory and must end in `Test.php`. Create a first test class like `./tests/Rest/UserTest.php`:

```php
namespace Tests\Rest;
use LarkRestTest\RestTest;

class UserTest extends RestTest
{
    public function __construct()
    {
        // HTTP client must be initialized with a base URL before
        // any client methods can be called
        $this->client('http://localhost');
    }

    /**
     * Create users
     * @test
     */
    public function create(): void
    {
        // send request:
        // POST /users
        // [{"name":"test"},{"name":"test2"}]
        $this->clientPost('/users', [
            ['name' => 'test'],
            ['name' => 'test2']
        ]);

        // must be response status code 200
        $this->expectCodeOk();

        // body must be the same as this
        // because "id" will be unknown use "$$VAR" to allow any value
        $this->expectBodySame([
            ['id' => '$$VAR', 'name' => 'test'],
            ['id' => '$$VAR', 'name' => 'test2']
        ]);

        // save IDs for later tests
        $this->ids('user');
    }

    /**
     * Delete user
     * @test
     * @depends create
     */
    public function deleteOne(): void
    {
        // get ID from earlier save in create method
        $id = $this->id('user');
        // also can use $this->id('user', 1); to get ID at exact index

        $this->clientDelete('/users/' . $id);

        $this->expectCodeOk();
        $this->expectBodySame(['affected' => 1]);
    }
}
```

Each test class method that is a test must have `@test` in the docblock, otherwise it will be ignored when running tests.

If a method depends on another method, or multiple methods, the `@depends [METHODNAME]` annotation in the method docblock should be used, like `@depends create`.

If a class depends on another class the `@depends [CLASSNAME]` annotation in the class docblock should be used, like `@depends \Tests\Rest\UserTest`.

> All classes ending in `Test.php` will be considered tests. To exclude a class file ending in `Test.php` from tests use `@ignore` in the class docblock.

### Cleanup Callable

A test method can return a `callable` that is used as a cleanup function and will be called after the test method has been called, example:

```php
    /**
     * Delete user
     * @test
     * @depends create
     */
    public function deleteOne(): callable
    {
        // ...

        return function() {
            // do cleanup here
        };
    }
```

### Comparing Response Body

Sometimes a response body array of objects can be randomly ordered, which can cause a test to fail when using `RestTest::expectBodySame`. For example:

```php
    /**
     * Update users
     * @test
     * @depends create
     */
    public function update(): void
    {
        $this->clientPatch('/users', [
            [
                'id' => $this->id('user', 1),
                'name' => 'test5'
            ],
            [
                'id' => $this->id('user', 2),
                'name' => 'test6'
            ]
        ]);

        // this will possibly fail depending on the order of objects
        // returned in the response body
        $this->expectBodySame([
            [
                'id' => $this->id('user', 1),
                'name' => 'test5'
            ],
            [
                'id' => $this->id('user', 2),
                'name' => 'test6'
            ]
        ])
    }
```

To get around this problem the `RestTest::expectBodySameSorted` method can be used to auto sort the expected array of objects and the response body array of objects by a specific field (`id` by default).

## Run Tests

To run the tests go to the tests directory where the run tests file is located like `./tests/Rest` and execute the run file:

```
$ php run.php

Running tests...
------------------------------------------------------------------------------------------
  Base namespace: Tests\Rest
  Base directory: /myproject/tests/Rest
  Test classes: 1
------------------------------------------------------------------------------------------
001) 200 Tests\Rest\UserTest::create    # Create users [0.0475s]
002) 200 Tests\Rest\UserTest::deleteOne    # Delete user [0.0312s]
------------------------------------------------------------------------------------------
 OK  (Tests: 2, Assertions: 4) in 0.0874s
```
