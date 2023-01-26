<?php

/**
 * Class JoomlaSupport
 * Provides access to Joomla's systems without having to deal with the mess
 * that is Joomla's systems
 */
class JoomlaSupport {
	/**
	 * Global Joomla database object
	 * @var Database $db
	 */
	static $db;

	/**
	 * Initialize Joomla support for the current script
	 */
	public static function init() {
		if (!defined("_JEXEC")) {
			define("_JEXEC", 1);
			define("DS", DIRECTORY_SEPARATOR);
			define("JPATH_BASE", dirname(dirname(__DIR__)));
			define("__DIR__", JPATH_BASE);

			require_once(JPATH_BASE . "/includes/defines.php");
			require_once(JPATH_BASE . "/includes/framework.php");

			//Make sure to construct the application
			JFactory::getApplication('site');

			jimport("joomla.user.authorization");
			jimport("joomla.user.authentication");

			restore_error_handler();
			restore_exception_handler();

			self::$db = new Database("joomla", [/* "bv2xj_" => "bv2xj_" */]);
		}
	}

	/**
	 * Check if a user can log in with the given password
	 * @param string $username The user's username
	 * @param string $password The user's password
	 * @return bool If the user can login
	 */
	public static function checkLogin($username, $password) {
		self::init();

		$user = JFactory::getUser(JUserHelper::getUserId($username));

		if ($user->id == 0) {
			return false;
		} else if ($user->guest) {
			return false;
		} else if (!self::checkPassword($username, $password)) {
			return false;
		} else if (!self::checkAccountActivation($username)) {
			return false;
		}
		return true;
	}

	/**
	 * Check if a user can log in with the given key
	 * @param string $username The user's username
	 * @param string $key The user's key
	 * @return bool If the user can login
	 */
	public static function checkLoginKey($username, $key) {
		self::init();

		$user = JFactory::getUser(JUserHelper::getUserId($username));

		if ($user->id == 0) {
			return false;
		} else if ($user->guest) {
			return false;
		} else if (!self::checkKey($username, $key)) {
			return false;
		} else if (!self::checkAccountActivation($username)) {
			return false;
		}
		return true;
	}

	public static function checkAccountActivation($username) {
		self::init();

		$user = JFactory::getUser(JUserHelper::getUserId($username));
		//If they are not activated then they are block with activation set
		return !($user->block && $user->activation !== "");
	}

	/**
	 * Check if a username and password match for a given user
	 * @param string $username The user's username
	 * @param string $password The user's password
	 * @return bool If the password matches
	 */
	static function checkPassword($username, $password) {
		$credentials = array("username" => $username, "password" => $password);
		$options = array('remember' => false, 'silent' => false);

		// Get the global JAuthentication object.
		$authenticate = JAuthentication::getInstance();
		$response = $authenticate->authenticate($credentials, $options);

		return $response->status === JAuthentication::STATUS_SUCCESS;
	}

	/**
	 * Check if a username and key match for a given user
	 * @param string $username The user's username
	 * @param string $key The user's key
	 * @return bool If the key matches
	 */
	static function checkKey($username, $key) {
		// TODO: LOL this is still using the mbp database
		return Platinum\checkKey($username, $key);
	}

	/**
	 * Check if the user is logged in with cookies from the website
	 * @return bool If they are logged in
	 */
	public static function checkCookieLogin() {
		self::init();
		$user = JFactory::getUser();

		return $user->id !== 0;
	}

	/**
	 * Get the user's login username from their cookies, or null if they are not logged in
	 * @return string|null
	 */
	public static function getCurrentUsername() {
		self::init();
		$user = JFactory::getUser();

		return $user->id === 0 ? null : $user->username;
	}

	/**
	 * Get a user's id from their username
	 * @param string $username The user's username
	 * @return int The user's id
	 */
	public static function getUserId($username) {
		self::init();
		return JUserHelper::getUserId($username);
	}

	/**
	 * Get a user's raw username
	 * @param int $userId The id of the user
	 * @return string The user's username
	 */
	public static function getUsername($userId) {
		self::init();

		$user = JFactory::getUser($userId);
		return $user->username;
	}

	/**
	 * Get a user's formatted display name
	 * @param int $userId The id of the user
	 * @return string The user's display name
	 */
	public static function getDisplayName($userId) {
		self::init();

		$user = JFactory::getUser($userId);
		//Make sure we clean up anything nasty
		return sanitizeDisplayName($user->name);
	}

	/**
	 * Get the path to a user's avatar image
	 * @param int $userId The user's id
	 * @return string The path to their avatar image
	 */
	public static function getUserAvatarPath($userId) {
		self::init();

		$query = self::$db->prepare("SELECT `avatar` FROM `bv2xj_kunena_users` WHERE `userid` = :id");
		$query->bindValue(":id", $userId);
		$query->execute();

		return $query->fetchColumn(0);
	}

	/**
	 * Get the Joomla database object
	 * @return Database
	 */
	public static function db() {
		self::init();
		return self::$db;
	}
}

