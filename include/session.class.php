<?php

define('SESSION_TIME', 1800); // 30 minutes

class Session
{
	public static function logIn($user_id, $permission)
	{
		global $db;
		$user = $db->querySingle("SELECT * FROM user WHERE user_id = '" . $user_id . "' LIMIT 1;");
		if (!$user)
			user_error('Could not login', ERROR);

		$_SESSION['login'] = array(
			'user_id' => $user_id,
			'username' => $user['username'],
			'email' => $user['email'],
			'permission' => $permission,
			'time' => time()
		);
	}

	public static function logOut()
	{
		unset($_SESSION['login']);
	}

	public static function loggedIn()
	{
		if (!isset($_SESSION['login']))
			return false;

		if ($_SESSION['login']['time'] + SESSION_TIME > time())
			return true;

		self::logOut();
		return false;
	}

	public static function refreshLogin()
	{
		if (self::loggedIn())
			$_SESSION['login']['time'] = time();
	}

	public static function isUser()
	{
		return self::loggedIn();
	}

	public static function isAdmin()
	{
		return (self::loggedIn() ? $_SESSION['login']['permission'] == 'admin' : false);
	}

	public static function getUserId()
	{
		return (self::loggedIn() ? $_SESSION['login']['user_id'] : false);
	}

	public static function getUsername()
	{
		return (self::loggedIn() ? $_SESSION['login']['username'] : false);
	}

	public static function getEmail()
	{
		return (self::loggedIn() ? $_SESSION['login']['email'] : false);
	}

	public static function getPermission()
	{
		return (self::loggedIn() ? $_SESSION['login']['permission'] : false);
	}

	public static function getTimeleft()
	{
		return (self::loggedIn() ? ($_SESSION['login']['time'] + SESSION_TIME - time()) : false);
	}
}

?>