<?php

/*
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

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Guzzle\Http\Client;

trait Selenium {

	/** @var RemoteWebDriver */
	protected $webDriver;

	protected function startSeleniumDriver() {
		$capabilities = [
			WebDriverCapabilityType::BROWSER_NAME => $this->getBrowser(),
		];

		if ($this->isRunningOnCI()) {
			$capabilities['tunnel-identifier'] = getenv('TRAVIS_JOB_NUMBER');
			$capabilities['build'] = getenv('TRAVIS_BUILD_NUMBER');
			$capabilities['name'] = $this->getTestName();
			$user = getenv('SAUCE_USERNAME');
			$accessKey = getenv('SAUCE_ACCESS_KEY');
			$this->webDriver = RemoteWebDriver::create("http://$user:$accessKey@ondemand.saucelabs.com/wd/hub", $capabilities);
		} else {
			$user = getenv('SAUCE_USERNAME');
			$accessKey = getenv('SAUCE_ACCESS_KEY');
			$this->webDriver = RemoteWebDriver::create("http://$user:$accessKey@localhost:4445/wd/hub", $capabilities);
		}
	}

	private function getBrowser() {
		$fromEnv = getenv('SELENIUM_BROWSER');
		if ($fromEnv !== false) {
			return $fromEnv;
		}
		return WebDriverBrowserType::FIREFOX;
	}

	private function getTestName() {
		if ($this->isRunningOnCI()) {
			return 'PR' . getenv('TRAVIS_PULL_REQUEST') . ', Build ' . getenv('TRAVIS_BUILD_NUMBER') . ', Test ' . self::class . '::' . $this->getName();
		} else {
			return 'Test ' . self::class . '::' . $this->getName();
		}
	}

	protected function stopSeleniumDriver($failed) {
		$sessionId = $this->webDriver->getSessionID();

		$this->webDriver->quit();
		$this->webDriver = null;

		if ($this->isRunningOnCI()) {
			$this->reportTestStatusToSauce($sessionId, $failed);
		}
	}

	/**
	 * @param string $sessionId sauce labs job id
	 * @param bool $failed
	 */
	private function reportTestStatusToSauce($sessionId, $failed) {
		$httpClient = new Client();
		$httpClient->put("https://saucelabs.com/rest/v1/nextcloud-totp/jobs/$sessionId", [
			'auth' => [
				getenv('SAUCE_USERNAME'),
				getenv('SAUCE_ACCESS_KEY'),
			],
			'json' => [
				'passed' => !$failed,
			],
		]);
	}

	private function isRunningOnCI() {
		return getenv('TRAVIS') !== false;
	}

}
