<?php

/**
 * The MIT License
 *
 * Copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace ChristophWurst\Nextcloud\Testing;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase as Base;
use ReflectionClass;
use function in_array;

abstract class TestCase extends Base
{

	protected function createServiceMock(string $class, array $custom = []): ServiceMockObject
	{
		$reflectedClass = new ReflectionClass($class);
		$constructor = $reflectedClass->getConstructor();
		$indexedArgs = [];

		if ($constructor === null) {
			$service = new $class;
		} else {
			$orderedArgs = [];
			foreach ($constructor->getParameters() as $parameter) {
				if (isset($custom[$parameter->getName()])) {
					$indexedArgs[$parameter->getName()] = $orderedArgs[] = $custom[$parameter->getName()];
				} else if ($parameter->getType() !== null) {
					$indexedArgs[$parameter->getName()] = $orderedArgs[] = $this->createMock($parameter->getType()->getName());
				} else {
					throw new InvalidArgumentException("Can not defer mock for constructor parameter " . $parameter->getName() . " of class $class");
				}
			}
			$service = new $class(...$orderedArgs);
		}

		return new ServiceMockObject($class, $indexedArgs, $service);
	}

	protected function setUp(): void
	{
		parent::setUp();

		if (in_array(DatabaseTransaction::class, class_uses($this))) {
			$this->startTransaction();
		}
		if (in_array(Selenium::class, class_uses($this))) {
			$this->startSeleniumDriver();
		}
	}

	protected function tearDown(): void
	{
		parent::tearDown();

		if (in_array(DatabaseTransaction::class, class_uses($this))) {
			$this->rollbackTransation();
		}
		if (in_array(Selenium::class, class_uses($this))) {
			$this->stopSeleniumDriver(parent::hasFailed());
		}
	}

}
