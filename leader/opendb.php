<?php
// For metrics purposes
$scriptStart = time();

// Start the database connection
require("database.php");
require("config.php");

if (!isset($socketserver)) {
	// All your webchat scripts need to pass "webchat" as a postvar
	if (!$allow_web_testing && !$allow_nonwebchat && $_SERVER["HTTP_USER_AGENT"] != "Torque 1.0" && !array_key_exists("webchat", $_POST)) {
		// Otherwise, we'll just assume you're a hacker and say nope.
		header("HTTP/1.1 403 Forbidden");
		die();
	}

	// We need this header because config.php sends a 404 not found to hide it
	// from the general web.
	header("HTTP/1.1 200 OK");
}

$dsn = "mysql:dbname=" . $mysql_data . ";host=" . $mysql_host;
// Connect + select
try {
	global $lb_connection;
   $lb_connection = new SpDatabaseConnection($dsn, $mysql_user, $mysql_pass);
} catch (SpDatabaseLoginException $e) {
	die("Could not open database connection.");
}
if ($lb_connection == null) {
	die("Could not connect to database.");
}
$lb_connectionOpen = true;

// Used for doing prepared queries
$prepares = 0;
function pdo_prepare($statement = "") {
   global $lb_connection, $prepares, $socketserver;

   // Stop crashing!
	if (isset($socketserver)) {
   	checkServer();
   }

   $prepares ++;

   if (!$lb_connection) {
   	echo("Something has happened to \$lb_connection...\n");
   	try {
   		global $mysql_data, $mysql_host, $mysql_user, $mysql_pass;
			$dsn = "mysql:dbname=" . $mysql_data . ";host=" . $mysql_host;
		   $lb_connection = new SpDatabaseConnection($dsn, $mysql_user, $mysql_pass);
		} catch (SpDatabaseLoginException $e) {
			throw $e;
		}
		if ($lb_connection == null) {
	   	throw (new SpDatabaseException("Server is gone."));
	   }
   }

   try {
	   return $lb_connection->prepare($statement);
	} catch (Exception $e) {
		echo("Caught an exception preparing a statement!\n");
		debug_print_backtrace();
		throw $e;
	}
}

if (!isset($socketserver)) {
	// Fixing repeated actions due to TCP bugs
	if (empty($ignore_keys)) {
		$sig = validateKey();

		if ($sig != 26) //26 is "good key"
			sig($sig);
	}

	// Shhhhhhhhhhhh
	sneakyTracking();

	// Lock downs
	if (getServerPref("lockdown") && !(checkPostLogin() == 7 && getUserPrivilege(getUsername()) > 0)) {
		sig(29); //Lock down
	}

	// So we don't have problems
	checkDustyLogins();

	if (!isset($ignore_version)){
		require_once("version.php");

		// Required server versions
		if (getServerPref("lockversion") && isTorque()) {
			if (!checkVersion(false)) {
				sig(30); //Update Client
				die();
			}
		}
	}

	// Check for bans

//	$address = $_SERVER["REMOTE_ADDR"];
//	$query = pdo_prepare("SELECT `address` FROM `bannedips` WHERE `address` = :address");
//	$query->bind(":address", $address);
//	$result = $query->execute();
//	if ($result->rowCount()) {
//		sig(27); // FUCKING BANNED
//	}
}

/**
 * Closes the database connection. Do not call manually.
 * This will be called normally when the script finishes.
 * @version 0.1
 * @package leader
 * @access public
 */
function closeDB() {
	global $lb_connection, $lb_connectionOpen, $prepares, $scriptStart;

	if (!$lb_connectionOpen)
		return;

	if (isTorque() && getServerPref("debuglogging")) {
		$time = time() - $scriptStart;
		techo("Ran {$_SERVER["PHP_SELF"]} with exactly {$prepares} SQL queries");
		techo("Ran {$_SERVER["PHP_SELF"]} in {$time} ms");
	}

	$lb_connection = null;

	// Looks ugly in web-interface
	if (isTorque())
		sig(1);
}

/**
 * Checks for old logins and kicks anyone who hasn't pinged in > 30 seconds
 * @version 0.1
 * @package leader
 * @access public
 */
function checkDustyLogins() {
	// If they're pinging, we don't want to log them out!
	$skip = "";
	$currentUser = "";
	if (isJoomla()) {
		require_once("jsupport.php");
		$currentUser = getUser();
	} else
      $currentUser = getUsername();
	if ($currentUser != "") {
		$skip = $currentUser;
		global $dont_update_ping;
		if (empty($dont_update_ping)) {
			// Nope.
			$table = (isJoomla() ? "jloggedin" : "loggedin");
			$query = pdo_prepare("UPDATE `$table` SET `time` = :time, `address` = :address, `display` = :display WHERE `username` = :user");
			$query->bind(":time", getServerTime(), PDO::PARAM_INT);
			$query->bind(":display", getDisplayName($currentUser), PDO::PARAM_STR);
			$query->bind(":user", $currentUser, PDO::PARAM_STR);
			$query->bind(':address', $_SERVER["REMOTE_ADDR"]);
			$query->execute();
		}
		// Update their timestamp so we can see them, don't do this to admin pages (because I like being sneaky)
		global $admin_page;
		if (!isset($admin_page)) {
			$query = pdo_prepare("UPDATE `users` SET `lastaction` = CURRENT_TIMESTAMP WHERE `username` = :user");
			$query->bind(":user", $currentUser, PDO::PARAM_STR);
			$query->execute();
		}
	}

	if (getServerPref("lagoutenabled") == "0")
		return;

	$lagoutTime = intval(getServerPref("lagouttime"));

	// If it's been > $lagoutTime secs, too bad
	$now = getServerTime();
	$toobad = $now - $lagoutTime;
	$query = pdo_prepare("SELECT * FROM `loggedin` WHERE `time` < :time");
	$query->bind(":time", $toobad, PDO::PARAM_INT);
	$result = $query->execute();

	global $kick_myself;

	while (($assoc = $result->fetch()) !== false) {
		$user = $assoc["username"];
		// Don't kick the person who's sending the request
		if ($user == $skip && $user != "" && !isset($kick_myself))
			continue;
		if (getUserBanned($row["username"]) == 0) {
			if ($user != "") {
				if (!$assoc["joomla"])
					postNotify("lagout", $user, 1);
				postNotify("logout", $user, -1);
			}
		}
		$query = pdo_prepare("DELETE FROM `loggedin` WHERE `username` = :user LIMIT 1");
		$query->bind(":user", $user, PDO::PARAM_STR);
		$query->execute();

		if (isGuest($user)) {
			$query = pdo_prepare("DELETE FROM `users` WHERE `username` = :user LIMIT 1");
			$query->bind(":user", $user, PDO::PARAM_STR);
			$query->execute();
		}

		// Track their login time
	   $loginTime = $assoc["time"];

	   // Clean up
	   deleteTrackDataType("lastlogin", $user);

	   $totalTime = getServerTime() - $loginTime;
//	   trackData("logintime", $user, $totalTime);
//	   trackData("lagouts", $user);
	}

	$lagoutTime = intval(getServerPref("jlagouttime"));
	$toobad = $now - $lagoutTime;
	$query = pdo_prepare("SELECT * FROM `jloggedin` WHERE `time` < :time");
	$query->bind(":time", $toobad, PDO::PARAM_INT);
	$result = $query->execute();

	while (($assoc = $result->fetch()) !== false) {
		$user = $assoc["username"];
		// Don't kick the person who's sending the request
		if ($user == $skip && $user != "" && !isset($kick_myself))
			continue;
		if (getUserBanned($row["username"]) == 0) {
			if ($user != "") {
				if (!$assoc["joomla"])
					postNotify("lagout", $user, 1);
				postNotify("logout", $user, -1);
			}
		}
		$query = pdo_prepare("DELETE FROM `jloggedin` WHERE `username` = :user LIMIT 1");
		$query->bind(":user", $user, PDO::PARAM_STR);
		$query->execute();

		if (isGuest($user)) {
			$query = pdo_prepare("DELETE FROM `users` WHERE `username` = :user LIMIT 1");
			$query->bind(":user", $user, PDO::PARAM_STR);
			$query->execute();
		}
	}
}

/**
 * Validates whether a given tcpobject key has been used before
 * @version 0.1
 * @package leader
 * @access private
 */

function validateKey() {
	if (isTorque()) {
		if (array_key_exists("key", $_POST)) {
			$username = getUsername();
			$key = getPostValue("key");
			$query = pdo_prepare("SELECT * FROM `usedkeys` WHERE `key` = :key AND `username` = :user LIMIT 1");
			$query->bind(":key", $key, PDO::PARAM_STR);
			$query->bind(":user", $username, PDO::PARAM_STR);
			$result = $query->execute();

			if ($result->rowCount())
				return 25; //Invalid Key
			else {
				// Add it to the database and remove any old keys
				$oldKeyCount = 5;
				$time = getServerTime();

				// Insert the new key into the database
				$query = pdo_prepare("INSERT INTO `usedkeys` (`username`, `key`, `time`) VALUES (:user, :key, :time)");
				$query->bind(":user", $username, PDO::PARAM_STR);
				$query->bind(":key", $key, PDO::PARAM_STR);
				$query->bind(":time", $time, PDO::PARAM_INT);
				$query->execute();

				// Limit the number of keys in the database
				$query = pdo_prepare("SELECT * FROM `usedkeys` WHERE `username` = :user ORDER BY `time` ASC");
				$query->bind(":user", $username, PDO::PARAM_STR);
				$result = $query->execute();
				$rows = $result->rowCount();

				// While loop incase they somehow have more than $oldKeyCount
				while ($rows > $oldKeyCount) {
					// The first row we fetch should be the oldest
					$row = $result->fetch();
					$query = pdo_prepare("DELETE FROM `usedkeys` WHERE `key` = :key LIMIT 1");
					$query->bind(":key", $row["key"], PDO::PARAM_STR);
					$query->execute();
					$rows --;
				}
				return 26;
			}
		} else
			return 24; //No Key
	} else // Non-torque users don't have this problem
		return 26;
}

/**
 * Check if the script is being executed by the Torque
 * @version 0.1
 * @package leader
 * @access public
 * @return Boolean true if the script is being loaded by Torque
 */
function isTorque() {
	// All your webchat scripts need to pass this
	if (array_key_exists("webchat", $_POST))
		return false;
	return $_SERVER["HTTP_USER_AGENT"] == "Torque 1.0";
}

/**
 * Check if the script is being submitted from a webchat form
 * @version 0.1
 * @package leader
 * @access public
 * @return Boolean true if the script is being submitted from a webchat form
 */
function isSubmitting() {
	// Only for web
	if ($_SERVER["HTTP_USER_AGENT"] == "Torque 1.0")
		return false;
	// True if we're submitting
	return array_key_exists("submitting", $_POST) || array_key_exists("webchat", $_POST);
}

/**
 * Sends the header and then dies
 * @version 0.1
 * @package leader
 * @access public
 */
function headerDie($header) {
	header($header);
	die();
}

/**
 * Does sneaky tracking things
 * (Look away, mortals!)
 * @version 0.1
 * @package leader
 * @access private
 */

function sneakyTracking() {
	// I love how easy this is to do in PHP
	$ip = "localhost";
	if (array_key_exists('HTTP_X_FORWARD_FOR', $_SERVER) && $_SERVER["HTTP_X_FORWARD_FOR"])
		$ip = $_SERVER['HTTP_X_FORWARD_FOR'];
	else
		$ip = $_SERVER['REMOTE_ADDR'];

	$username = getUsername();

	// Don't check invalid logins
	if (checkPostLogin() != 7 && $username != "")
		return;

	//First time for them with this address

	$query = pdo_prepare("INSERT INTO `addresses` (`username`, `address`, `hits`, `firstHit`) VALUES (:user, :ip, :hits, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE `hits` = `hits` + 1");
	$query->bind(":user", $username, PDO::PARAM_STR);
	$query->bind(":ip", $ip, PDO::PARAM_STR);
	$query->bind(":hits", 1, PDO::PARAM_INT);
	$query->execute();
}

/**
 * Check if $_POST has the keys specified
 * @version 0.1
 * @package leader
 * @access public
 * @var string A list of strings to check (variable length)
 * @return Boolean true if all the keys are found in $_POST
 */
function checkPostVars() {
	$args = func_num_args();
	for ($i = 0; $i < $args; $i ++) {
		$arg = func_get_arg($i);
		if (getPostValue($arg) == "")
			return false;
	}
	return true;
}

/**
 * Generates a salt for a password
 * @version 0.1
 * @package leader
 * @access public
 * @return string The generated salt
 */
function generateSalt($length = 16) {
	// Valid chars
	$chars = "abcdef0123456789";
	$ret = "";
	$charsLen = strlen($chars);
	for ($i = 0; $i < $length; $i ++) {
		$chr = substr($chars, rand(0, $charsLen), 1);
		$ret .= $chr;
	}
	return $ret;
}

/**
 * Salts a password using SHA-1
 * @version 0.1
 * @package leader
 * @access public
 * @return string The salted password
 */
function salt($password, $salt) {
	return sha1(sha1($password) . $salt);
}

/**
 * Salts a password using MD5
 * @version 0.1
 * @package leader
 * @access public
 * @return string The salted password
 */
function saltMD5($password, $salt) {
	return md5(md5($password) . $salt);
}

/**
 * Attempts to login with the username and password in $_POST
 * @version 0.1
 * @package leader
 * @access public
 * @return int The SIG code (see sig.php) for the login attempt
 */
function checkPostLogin() {
	if (isJoomla()) {
		require_once("jsupport.php");
		return getLogin(getUsername(), getPostValue("password"));
	}
	// If we have the specified variables
	if (checkPostVars("username")) {
		// Get the variables
		$username = getUsername();
		$password = getPostValue("password");
		// Check the variables
		if ($username == "" || $password == "")
			return 2; //Missing Info (die)

		$password = deGarbledeguck($password);
		// Check if the user account exists
		$query = pdo_prepare("SELECT * FROM `users` WHERE `username` = :user");
		$query->bind(":user", $username, PDO::PARAM_STR);
		$result = $query->execute();

		if (!$result->rowCount()) {
			require_once("jsupport.php");
			return getLogin($username, $password);
		}

		// Get server password + sent password
		$assoc = $result->fetch();
		$salt = $assoc["salt"];

		if ($assoc["joomla"]) {
			// Let the joomla things take care of this
			require_once("jsupport.php");
			return getLogin($username, $password);
		}

		// Check if they are banned
		// if ($assoc["banned"] == 1)
		// 	return 27; //Banned (die)

		if (isGuest($username)) {
			// No passwords for guests
			return 7; //Logged in success
		}

		$password = array_key_exists("password", $_POST) ? salt($password, $salt) : (array_key_exists("password", $_COOKIE) ? $_COOKIE["password"] : "");
		$serverPassword = $assoc["pass"];

		// Compare them!
		if ($password != $serverPassword)
			return 6; //Password is wrong (die)
		return 7; //Logged in success
	} else
		return 2; //Missing info (die)
}

/**
 * Gets the access code for the specified user
 * @version 0.2
 * @package leader
 * @access public
 * @var $user The user to get the access of
 * @return int The access code for the user
 */
function getUserAccess($user) {
	// @revision 0.2 added check for guests.  If it is a guest,
	// their access is 3
	if (isGuest($user))
		return 3;

	// This should definitely be checked
	$query = pdo_prepare("SELECT `access` FROM `users` WHERE `username` = :user");
	$query->bind(":user", $user, PDO::PARAM_STR);
	$result = $query->execute();
	if (!$result->rowCount())
		return -1;
	return $result->fetch("access");
}

/**
 * Gets the privilege level for the specified user
 * @version 0.2
 * @package leader
 * @access public
 * @var $user The use to get the privilege of
 * @return int The privilege level for the user
 */
function getUserPrivilege($user) {
	$accessMap = [
		"-3" => 0, //Banned
		"-2" => 0, //???
		"-1" => 0, //???
		"0" => 0, //User
		"1" => 1, //Mod
		"2" => 2, //Admin
		"3" => 0, //Guest
	    "4" => 1, //Dev
	];
	$access = getUserAccess($user);
	return $accessMap[$access];
}

/**
 * Lets us know if the user specified is a guest
 * @version 0.1
 * @package leader
 * @access public
 * @var $user The user to check
 * @return boolean if the user is a guest
 */
function isGuest($user = "") {
	if ($user ==  "")
		$user = getUsername();
	return (stristr($user, "Guest_") !== false);
}

/**
 * Gets if a user is banned from the leaderboards
 * @version 0.1
 * @package leader
 * @access public
 * @var $user The user to check ban status
 * @return boolean If the user is banned
 */
function getUserBanned($user) {
	// This should definitely be checked
	$query = pdo_prepare("SELECT `banned` FROM `users` WHERE `username` = :user");
	$query->bind(":user", $user, PDO::PARAM_STR);
	$result = $query->execute();
	if (!$result->rowCount())
		return 0;
	return $result->fetch("banned");
}

/**
 * Gets the location code for the specified user
 * @version 0.1
 * @package leader
 * @access public
 * @var $user The user to get the location of
 * @return int The location code for the user
 */
function getUserLocation($user) {
	// This should definitely be checked
	if (!getUserLoggedIn($user))
		return 0;
	$table = (isJoomla() ? "jloggedin" : "loggedin");
	$query = pdo_prepare("SELECT `location` FROM `$table` WHERE `username` = :user");
	$query->bind(":user", $user, PDO::PARAM_STR);
	$result = $query->execute();
	if (!$result->rowCount())
		return -1;
	return $result->fetch("location");
}

/**
 * Gets whether or not the specified user is logged in
 * @version 0.1
 * @package leader
 * @access public
 * @var $user The user to check for logged in
 * @var $key A key to check
 * @return boolean Whether or not the specified user is logged in
 */
function getUserLoggedIn($user, $key = "") {
	// This should definitely be checked
	if (isJoomla()) {
		require_once("jsupport.php");
		$user = getUser();
		if ($user == "") {
			echo ("/*DONGERS DONGERS DONGERS DONGERS*/\n");
			return false;
		}
	}
	$joomla = isJoomla();
	$table = ($joomla ? "jloggedin" : "loggedin");
	$query = pdo_prepare("SELECT `loginsess` FROM `$table` WHERE `username` = :user");
	$query->bind(":user", $user, PDO::PARAM_STR);
	$result = $query->execute();
	if ($result->rowCount()) {
		if ($key != "") {
			$sess = $result->fetchIdx(0);
			return $key == $sess;
		}
		//echo("/*FLATULENCE FLATULENCE FLATULENCE*/\n");
		return true;
	}
	else if (isJoomla()) {
		$user = getUsername();
		$time = getServerTime();
		$access = getUserAccess($user);
		$display = getDisplayName($user);

		$query = pdo_prepare("INSERT INTO `$table` (`username`, `display`, `access`, `location`, `time`, `logintime`, `joomla`) VALUES (:user, :display, :access, :location, :time, :logintime, :joomla)");
		$query->bind(":user", $user, PDO::PARAM_STR);
		$query->bind(":display", $display, PDO::PARAM_STR);
		$query->bind(":access", $access, PDO::PARAM_INT);
		$query->bind(":location", 3, PDO::PARAM_INT);
		$query->bind(":time", $time, PDO::PARAM_INT);
		$query->bind(":logintime", $time, PDO::PARAM_INT);
		$query->bind(":joomla", $joomla, PDO::PARAM_BOOL);

		try {
			$query->execute();
			//echo("/*SPLENDIFLOURIOUS*/\n");
		} catch (Exception $e) {
			print_r($e);
			echo("/*BLAH BLAH*/\n");
			return true;
		}

		postNotify("login", $user, -1, 3);

		return !!$query;
	}
	return false;
}

/**
 * Gets a postvalue either from $_POST (if exists) or $_COOKIE
 * @version 0.1
 * @package leader
 * @access public
 * @var $name string The postvalue to get
 * @return string The postvalue for the specified name
 */
function getPostValue($name) {
	// $_POST is always the most fresh
	if (array_key_exists($name, $_POST))
		return $_POST[$name];
	// Resort to cookies if needed
	if (array_key_exists($name, $_COOKIE))
		return $_COOKIE[$name];
	return "";
}

/**
 * Gets the postvalues either from $_POST (if exists) or $_COOKIE
 * @version 0.1
 * @package leader
 * @access public
 * @var array $names The postvalues to get
 * @return array The postvalues for the specified names
 */
function getPostValues() {
	$args = func_num_args();
	$ret = array();
	for ($i = 0; $i < $args; $i ++) {
		$name = func_get_arg($i);
      // $_POST is always the most fresh
      if (array_key_exists($name, $_POST)) {
         $ret[$i] = $_POST[$name];
         continue;
      }
      // Resort to cookies if needed
      if (array_key_exists($name, $_COOKIE)) {
         $ret[$i] = $_COOKIE[$name];
         continue;
      }
      $ret[$i] = "";
	}
	return $ret;
}

/**
 * Gets the player's username either from $_POST (if exists) or $_COOKIE
 * @version 0.1
 * @package leader
 * @access public
 * @return var The postvalue for the specified name
 */
function getUsername($username = "") {
	if ($username == "") {
		// $_POST is always the most fresh
		if (array_key_exists("username", $_POST))
			$username = $_POST["username"];
		// Resort to cookies if needed
		if (array_key_exists("username", $_COOKIE))
			$username = $_COOKIE["username"];
	}

	if ($username == "") {
		//Try from joomla
		require_once("jsupport.php");
		$username = getUser();
	}

	$query = pdo_prepare("SELECT `username` FROM `users` WHERE `username` = :username");
	$query->bind(":username", $username, PDO::PARAM_STR);
	$result = $query->execute();
	if ($result->rowCount())
		return $result->fetchIdx(0);

	// Best we got.
	return $username;
}

/**
 * Gets the player's display name from the database
 * @version 0.1
 * @package leader
 * @access public
 * @return var The postvalue for the specified name
 */
function getDisplayName($username = "") {
	if ($username == "") {
		// $_POST is always the most fresh
		if (array_key_exists("username", $_POST))
			$username = $_POST["username"];
		// Resort to cookies if needed
		if (array_key_exists("username", $_COOKIE))
			$username = $_COOKIE["username"];
	}

	if ($username == "") {
		//Try from joomla
		require_once("jsupport.php");
		$username = getUser();
	}

	$query = pdo_prepare("SELECT `display`, `joomla` FROM `users` WHERE `username` = :username");
	$query->bind(":username", $username, PDO::PARAM_STR);
	$result = $query->execute();
	if ($result->rowCount()) {
		$row = $result->fetchIdx();
		if ($row[1]) {
			require_once("jsupport.php");
			return getDisplay($username);
		} else
			return $row[0];
	}

	return $username;
}

/**
 * Gets the player's title
 * @version 0.1
 * @package leader
 * @access public
 * @return var The postvalue for the specified name
 */
function getUserTitle($username = "") {
	if ($username == "") {
		// $_POST is always the most fresh
		if (array_key_exists("username", $_POST))
			$username = $_POST["username"];
		// Resort to cookies if needed
		if (array_key_exists("username", $_COOKIE))
			$username = $_COOKIE["username"];
	}

	if ($username == "") {
		//Try from joomla
		require_once("jsupport.php");
		$username = getUser();
	}

	$query = pdo_prepare("SELECT `title`, `joomla` FROM `users` WHERE `username` = :username");
	$query->bind(":username", $username, PDO::PARAM_STR);
	$result = $query->execute();
	if ($result->rowCount()) {
		$row = $result->fetchIdx();
		return $row[0];
	}

	return "";
}

/**
 * Post a notification to `notify`
 * @version 0.1
 * @package leader
 * @access public
 * @var $type The type of notification
 * @var $user The username that the notification pertains to
 * @var $access The access required to see the notification
 */
function postNotify($type, $user, $access = 0, $message = "") {
	// Quick hack to make sure it's a number
	$access += 0;
	$time = getServerTime();
	$query = pdo_prepare("INSERT INTO `notify` (`username`, `type`, `message`, `access`, `time`) VALUES (:user, :type, :message, :access, :time)");
	$query->bind(":user", $user, PDO::PARAM_STR);
	$query->bind(":type", $type, PDO::PARAM_STR);
	$query->bind(":message", $message, PDO::PARAM_STR);
	$query->bind(":access", $access, PDO::PARAM_INT);
	$query->bind(":time", $time, PDO::PARAM_INT);
	$query->execute();
}

/**
 * Returns a the server setting/preference for the specified key
 * @version 0.1
 * @package leader
 * @access public
 * @var $key The server setting/preference to get
 * @return string The server setting/preference for the given key, or "" is no
 * preference was found for that key.
 */
function getServerPref($key) {
	$query = pdo_prepare("SELECT `value` FROM `settings` WHERE `key` = :key");
	$query->bind(":key", $key, PDO::PARAM_STR);
	$result = $query->execute();

	// If it doesn't exist, return nothing
	if (!$result->rowCount())
		return "";

	// Otherwise, this is simple
	return $result->fetch("value");
}

/**
 * Sets a the server setting/preference for the specified key
 * @version 0.1
 * @package leader
 * @access public
 * @var $key The server setting/preference to set
 * @var $value The value to set
 */
function setServerPref($key, $value) {
	$query = pdo_prepare("UPDATE `settings` SET `value` = :value WHERE `key` = :key LIMIT 1;");
	$query->bind(":value", $value, PDO::PARAM_STR);
	$query->bind(":key", $key, PDO::PARAM_STR);
	$result = $query->execute();
	return $result ? true : false;
}

/**
 * Returns the server time
 * @version 0.1
 * @package leader
 * @access public
 * @return float The SIG code (see sig.php) for the login attempt
 */
function getServerTime() {
	return (float)round((float)time() - floatval(getServerPref("servertime")), 1);
}

/**
 * Encodes a name so torque can parse it
 * @version 0.1
 * @package leader
 * @access public
 * @var string $name The username to encode
 * @return string The username with all spaces escaped to "-SPC-"
 */

function escapeName($name) {
	$name = str_replace(" ", "-SPC-", $name);
	$name = str_replace("\t", "-TAB-", $name);
	$name = str_replace("\n", "-NL-", $name);
	$name = mb_convert_encoding($name, "ASCII");
	return $name;
}

/**
 * Synonym form escapeName
 * @version 0.1
 * @package leader
 * @access public
 * @var string $name The username to encode
 * @return string The username with all spaces escaped to "-SPC-"
 */

function encodeName($name) {
	$name = str_replace(" ", "-SPC-", $name);
	$name = str_replace("\t", "-TAB-", $name);
	$name = str_replace("\n", "-NL-", $name);
	$name = mb_convert_encoding($name, "ASCII");
	return $name;
}

/**
 * Decodes a name so PHP can parse it
 * @version 0.1
 * @package leader
 * @access public
 * @var string $name The username to decode
 * @return string The username with all "-SPC-" escaped spaces replaced
 */

function decodeName($name) {
	$name = str_replace("-SPC-", " ", $name);
	$name = str_replace("-TAB-", "\t", $name);
	$name = str_replace("-NL-", "\n", $name);
	return $name;
}

/**
 * Decodes the garbledeguck() method in MBP
 * @version 0.1
 * @package leader
 * @access public
 * @var $string The string to decode
 * @return The decoded value of $string, using the de-garbledeguck method
 */

function deGarbledeguck($string) {
	/*
	// Weak "encrypts" a string so it can't be seen in clear-text
	function garbledeguck(%string) {
		%finish = "";
		for (%i = 0; %i < strlen(%string); %i ++) {
			%char = getSubStr(%string, %i, 1);
			%val = chrValue(%char);
			%val = 128 - %val;
			%hex = dec2hex(%val);
			%finish = %hex @ %finish; //Why not?
		}
		return %finish;
	}
	*/
	if (subStr($string, 0, 3) !== "gdg")
		return $string;
	$finish = "";
	for ($i = 3; $i < strLen($string); $i += 2) {
		$hex = subStr($string, $i, 2);
		$val = hexdec($hex);
		$char = chr(128 - $val);
		$finish = $char . $finish;
	}
	return $finish;
}

/**
 * Returns whether or not the user is accessing the site via Joomla Webchat
 * @version 0.1
 * @package leader
 * @access public
 * @return A boolean value of whether or not the user is using joomla webchat
 */

function isJoomla() {
	global $joomlaAuth;
	//echo("joomlaAuth is $joomlaAuth {$_POST['joomlaAuth']}\n");
	if (isset($joomlaAuth) && ($joomlaAuth == 1 || $joomlaAuth == "true"))
		return 1;
	if (array_key_exists("joomlaAuth", $_POST) && $_POST["joomlaAuth"])
		return 1;
	return 0;
}

/**
 * Returns the specified user-table field for the specified user
 * @version 0.1
 * @package leader
 * @access public
 * @var username The user's username for checking
 * @var field The field to get from the user's profile
 * @return The value of the field in the users table for the specified user
 */

function userField($username, $field) {
	global $lb_connection;

   // Oh hell no I don't trust myself. Escape the crap out of this one

   $query = pdo_prepare("SELECT `COLUMN_NAME` FROM `information_schema`.`columns` WHERE `TABLE_NAME` = 'users'");
   $result = $query->execute();

   if (!in_array(array("COLUMN_NAME" => $field), $result->fetchAll()))
      return "";

   // Yeah! Thought you could get past me?
   $field = "`" . str_replace("`", "``", $field) . "`";

	$query = pdo_prepare("SELECT $field FROM `users` WHERE `username` = :user");
	$query->bind(":user", $username, PDO::PARAM_STR);
	$result = $query->execute();

	return $result->fetchIdx(0);
}

/**
 * Detects if a player is logged in
 * @version 0.1
 * @package leader
 * @access public
 * @var player The player to check
 * @return If they're logged in
 */

//Migrated from superchallengeupdate.php
function isPlayerLoggedIn($player = NULL) {
   if ($player == NULL)
   	return false;
   if ($player == "")
   	return false;

   $query = pdo_prepare("SELECT `location` FROM `loggedin` WHERE `username` = :player");
   $query->bind(":player", $player);
   $result = $query->execute();
   if ($result && $result->rowCount())
   	return true;
   return false;
}

/**
 * Returns a random alphanumeric string of a given length
 * @version 0.1
 * @package leader
 * @access public
 * @var length The length of the string to return
 * @return A string of alphanumeric characters with $length length
 */

// I got this from some website but forgot to write it down.
function str_rand($length = 8) {
	 // Possible seeds
	$seeds = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

	 // Seed generator
	list($usec, $sec) = explode(' ', microtime());
	$seed = (float) $sec + ((float) $usec * 100000);
	mt_srand($seed);

	 // Generate
	$str = '';
	$seeds_count = strlen($seeds);
	for ($i = 0; $length > $i; $i ++) {
		$str .= $seeds{mt_rand(0, $seeds_count - 1)};
	}

	return $str;
}

/**
 * Prints a line out to torque that it will echo (show output off)
 * @version 0.1
 * @package leader
 * @access public
 * @var message The message to output
 */
function techo($message) {
	print("\x12$message\n");
}

/**
 * Post tracking data
 * @version 0.1
 * @package leader
 * @access public
 * @var $type The type of data
 * @var $user The username that the data pertains to
 * @var $data The actual data
 * @var $unique Insert a unique row
 */
function trackData($type, $user, $data = "", $unique = false) {
	$query = pdo_prepare("SELECT `id` FROM `tracking` WHERE `username` = :user AND `type` = :type AND `data` = :data LIMIT 1");
	$query->bind(":user", $user, PDO::PARAM_STR);
	$query->bind(":type", $type, PDO::PARAM_STR);
	$query->bind(":data", $data, PDO::PARAM_STR);
	$result = $query->execute();

	if ($result->rowCount() && !$unique) {
		$id = $result->fetchIdx(0);

		$query = pdo_prepare("UPDATE `tracking` SET `count` = `count` + 1 WHERE `id` = :id");
		$query->bind(":id", $id, PDO::PARAM_INT);
		$query->execute();
	} else {
		$query = pdo_prepare("INSERT INTO `tracking` (`username`, `type`, `data`, `count`, `lastUpdate`, `firstUpdate`) VALUES (:user, :type, :data, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
		$query->bind(":user", $user, PDO::PARAM_STR);
		$query->bind(":type", $type, PDO::PARAM_STR);
		$query->bind(":data", $data, PDO::PARAM_STR);
		$query->execute();
	}
}

/**
 * Receive tracking data
 * @version 0.1
 * @package leader
 * @access public
 * @var $type The type of data
 * @var $user The username that the data pertains to
 */
function getTrackData($type, $user) {
	$query = pdo_prepare("SELECT `data` FROM `tracking` WHERE `username` = :user AND `type` = :type LIMIT 1");
	$query->bind(":user", $user, PDO::PARAM_STR);
	$query->bind(":type", $type, PDO::PARAM_STR);
	$result = $query->execute();

	if ($result->rowCount()) {
		$data = $result->fetchIdx(0);
		return $data;
	} else {
		return null;
	}
}

/**
 * Deletes a whole type of tracking data
 * @version 0.1
 * @package leader
 * @access public
 * @var $type The type of data
 * @var $user The username that the data pertains to
 */
function deleteTrackDataType($type, $user) {
	$query = pdo_prepare("DELETE FROM `tracking` WHERE `username` = :user AND `type` = :type");
	$query->bind(":user", $user, PDO::PARAM_STR);
	$query->bind(":type", $type, PDO::PARAM_STR);
	$result = $query->execute();

	if ($result->rowCount()) {
		return true;
	} else {
		return false;
	}
}

/**
 * Deletes specific tracking data
 * @version 0.1
 * @package leader
 * @access public
 * @var $type The type of data
 * @var $user The username that the data pertains to
 * @var $data The data to delete
 */
function deleteTrackData($type, $user, $data) {
	$query = pdo_prepare("DELETE FROM `tracking` WHERE `username` = :user AND `type` = :type AND `data` = :data");
	$query->bind(":user", $user, PDO::PARAM_STR);
	$query->bind(":type", $type, PDO::PARAM_STR);
	$query->bind(":data", $data, PDO::PARAM_STR);
	$result = $query->execute();

	if ($result->rowCount()) {
		return true;
	} else {
		return false;
	}
}

/**
 * Strips a level name to its "stripped" form
 * @version 0.1
 * @package leader
 * @access public
 * @var $name The unstripped level name
 * @return string The stripped level name
 */

function stripLevel($name) {
	$stripped = strtolower($name);
	$stripped = preg_replace('/[^a-z0-9]/s', '', $stripped);
	return $stripped;
}

/**
 * Generates a random string of alphanumeric characters
 * @version 0.1
 * @package leader
 * @access public
 * @var $length The length of the string
 * @return string The randomly generated string
 */
function strRand($length = 64) {
	$chars = "abcdefghijklmnopqrstuvwqyz0123456789";

	//Get random seed from microtime
	list($usec, $sec) = explode(" ", microtime());
	//Do some cool maths
	$seed = (float) $sec + ((float) $usec * 100000);
	//And set the seed
	mt_srand($seed);

	//Generate
	$str = "";
	$charc = strlen($chars);

	for ($i = 0; $length > $i; $i ++) {
		$str .= $chars{mt_rand(0, $charc - 1)};
	}

	return $str;
}

/**
 * Removes Torque's "1e+006" notation from a number
 * @version 0.1
 * @package leader
 * @access public
 * @var $number The number to fix
 * @return float The fixed number
 */
function removeScientificNotation($number = 0) {
	//Check for normal numbers
	if (strpos($number, "e") === FALSE)
		return $number;

	//Now grab the two parts and fix it
	$base = substr($number, 0, strpos($number, "e"));
	$exp  = substr($number, strpos($number, "e") + 1);

	//Simple power
	return $base * pow(10, $exp);
}

// We need to close the database! This will take care of it for us.
register_shutdown_function('closeDB');

if ((isset($admin_page) && $admin_page !== false)) {
	if (checkPostLogin() && getUserPrivilege(getUsername()) < 1)
		headerDie("Location: admin.php?continue=" . $_SERVER["REQUEST_URI"]);
}

?>
