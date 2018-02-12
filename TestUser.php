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

use Exception;
use OC;
use OCP\IUser;
use OCP\IUserManager;

trait TestUser {

	/** IUserManager */
	private $userManager;

	/**
	 * @return IUserManager
	 */
	private function getUserManager() {
		if (is_null($this->userManager)) {
			$this->userManager = OC::$server->getUserManager();
		}
		return $this->userManager;
	}

	private function getRandomUid() {
		return 'testuser' . rand(0, PHP_INT_MAX);
	}

	/**
	 * @return IUser
	 * @throws Exception
	 */
	protected function createTestUser() {
		$userManager = $this->getUserManager();
		$uid = $this->getRandomUid();
		while ($userManager->userExists($uid)) {
			$uid = $this->getRandomUid();
		}

		$user = $userManager->createUser($uid, 'password');
		if ($user === false) {
			throw new Exception('could not create test user');
		}

		return $user;
	}

}
