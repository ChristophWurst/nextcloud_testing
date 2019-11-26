<?php declare(strict_types=1);

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
use PHPUnit\Framework\MockObject\MockObject;

class ServiceMockObject
{

	/** @var string */
	private $class;

	/** @var array */
	private $parameters;

	/** @var object */
	private $service;

	public function __construct(string $class,
								array $parameters,
								$service)
	{
		$this->class = $class;
		$this->parameters = $parameters;
		$this->service = $service;
	}

	/**
	 * @return object
	 */
	public function getService()
	{
		return $this->service;
	}

	/**
	 * @param string $name
	 * @return mixed|MockObject
	 */
	public function getParameter(string $name)
	{
		if (!isset($this->parameters[$name])) {
			throw new InvalidArgumentException("Class $this->class does not have a constructor parameter named $name");
		}

		return $this->parameters[$name];
	}

}
