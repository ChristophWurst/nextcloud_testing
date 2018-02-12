# Nextcloud Testing

Simple and fast unit and integration testing framework for Nextcloud, based on PHPUnit

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

Include the `DatabaseTransactions` trait in your test case and any changes to the database will be rolled back after each test:

```php
<?php

use ChristophWurst\Nextcloud\Testing\DatabaseTransaction;
use ChristophWurst\Nextcloud\Testing\TestCase;

class ControllerTest extends TestCase {

    use DatabaseTransaction;

    … your test code …

}
```
