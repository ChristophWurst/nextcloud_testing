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
use OCP\Http\Client\IClientService;
use RuntimeException;
use function getenv;

trait Selenium {

	/** @var RemoteWebDriver */
	protected $webDriver;

	protected function startSeleniumDriver() {
		$capabilities = [
			WebDriverCapabilityType::BROWSER_NAME => $this->getBrowser(),
		];

		if ($this->isRunningOnCI()) {
			// Ref https://docs.github.com/en/free-pro-team@latest/actions/reference/environment-variables
			if (($runId = getenv('GITHUB_RUN_ID')) !== false) {
				$capabilities['build'] = $runId;
			}
			if (($tunnelId = getenv('SAUCE_TUNNEL_ID')) !== false) {
				$capabilities['tunnel-identifier'] = $tunnelId;
			}

			$capabilities['name'] = $this->getTestName();
			$capabilities['extendedDebugging'] = true;
			if (($user = getenv('SAUCE_USERNAME')) === false || ($accessKey = getenv('SAUCE_ACCESS_KEY')) === false) {
				throw new RuntimeException("SAUCE credentials are missing");
			}

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
		/** @var \PHPUnit\Framework\TestCase $this */
		return 'Test ' . self::class . '::' . $this->getName();
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
		$httpClient = \OCP\Server::get(IClientService::class)->newClient();
		$httpClient->put('https://saucelabs.com/rest/v1/' . getenv('SAUCE_USERNAME') . "/jobs/$sessionId", [
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
		return getenv('CI') !== false;
	}

}

