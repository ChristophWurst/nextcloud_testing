# Nextcloud Testing

Simple and fast unit and integration testing framework for Nextcloud 25+, based on PHPUnit

Features
* Minimal setUp/tearDown overhead for unit tests
* Simple trait-based mechanism to reset database after integration tests
* Based on PHPUnit

## Unit Tests

Use ``TestCase`` as base class for your test case:

```php
<?php

use ChristophWurst\Nextcloud\Testing\TestCase;

class ControllerTest extends TestCase {

    … your test code …

}
```

## Integration Tests

Include the `DatabaseTransaction` trait in your test case and any changes to the database will be rolled back after each test:

```php
<?php

use ChristophWurst\Nextcloud\Testing\DatabaseTransaction;
use ChristophWurst\Nextcloud\Testing\TestCase;

class ControllerTest extends TestCase {

    use DatabaseTransaction;

    … your test code …

}
```

## Selenium Tests

Include the `Selenium` trait in your test case and access the Webdriver instance via `$this->webDriver`:

```php
<?php

use ChristophWurst\Nextcloud\Testing\Selenium;
use ChristophWurst\Nextcloud\Testing\TestCase;

class ControllerTest extends TestCase {

    use Selenium;

    public function testWithSelenium() {
        …

        $this->webDriver->get('http://localhost:8080/index.php/login');

        …
    }

}
```

This framework targets [Sauce Labs](https://saucelabs.com/) as testing back-end that runs the test
browser instances. Hence, it is necessary to set the `SAUCE_USERNAME` and `SAUCE_ACCESS_KEY` env
variables. `SELENIUM_BROWSER` lets you choose the browser to run your tests against.

## Test Users

Include the `TestUser` trait in your test case to conveniently generate new test users. The trait
will create a new user with a random UID (including collision detection).

```php
<?php

use ChristophWurst\Nextcloud\Testing\TestCase;
use ChristophWurst\Nextcloud\Testing\TestUser;

class ControllerTest extends TestCase {

    use TestUser;

    public function testWithSelenium() {
        …

        $user = $this->createTestUser();

        …
    }

}
```

The returned user is of type `IUser`. You can read its UID with `$user->getUID()`. Note that the
user is not removed after the test.
