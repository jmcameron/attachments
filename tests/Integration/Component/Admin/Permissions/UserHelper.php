<?php

namespace Tests\Integration\Component\Admin\Permissions;

class UserHelper
{
	protected static $users = [
		['id' => 42, 'name' => 'admin', 'username' => 'admin', 'email' => 'admin@example.com', 'password' => 'hashed_password', 'block' => 0,
			'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
			'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
			'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
		['id' => 43, 'name' => 'jmc', 'username' => 'jmc', 'email' => 'jmc@example.com', 'password' => 'hashed_password', 'block' => 0,
			'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
			'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
			'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
		['id' => 50, 'name' => 'joe', 'username' => 'joe', 'email' => 'joe@example.com', 'password' => 'hashed_password', 'block' => 0,
			'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
			'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
			'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
		['id' => 51, 'name' => 'art', 'username' => 'art', 'email' => 'art@example.com', 'password' => 'hashed_password', 'block' => 0,
			'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
			'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
			'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
		['id' => 52, 'name' => 'ed', 'username' => 'ed', 'email' => 'ed@example.com', 'password' => 'hashed_password', 'block' => 0,
			'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
			'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
			'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
		['id' => 53, 'name' => 'pub', 'username' => 'pub', 'email' => 'pub@example.com', 'password' => 'hashed_password', 'block' => 0,
			'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
			'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
			'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
		['id' => 54, 'name' => 'manny', 'username' => 'manny', 'email' => 'manny@example.com', 'password' => 'hashed_password', 'block' => 0,
			'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
			'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
			'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
		['id' => 55, 'name' => 'adam', 'username' => 'adam', 'email' => 'adam@example.com', 'password' => 'hashed_password', 'block' => 0,
			'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
			'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
			'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
	];

	public static function getUserId(string $username): int | null
	{
		$result = array_find(static::$users, fn($item) => $item['username'] == $username);

		if (!$result)
			return null;

		if (count($result)) {
			return $result['id'];
		}

		return null;
	}

	public static function getUserProps(int $user_id): array | null
	{
		return array_find(static::$users, fn($user) => $user['id'] == $user_id);
	}
}