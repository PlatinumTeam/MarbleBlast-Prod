<?php

require_once("../database.php");
require_once(dirname(dirname(dirname(__DIR__))) . "/db.php");

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

if (!isTorque() && !isset($master_server))
	die("ERR:0:The master server must be accessed from Torque.\n");

/**
 * @var string The hostname for the MySQL connection
 * @version 3.0
 * @package master
 * @access public
 */
$mysql_host = MBDB::getDatabaseHost("platinum");
/**
 * @var string The username for the MySQL connection
 * @version 3.0
 * @package master
 * @access public
 */
$mysql_user = MBDB::getDatabaseUser("platinum");
/**
 * @var string The password for the MySQL connection
 * @version 3.0
 * @package master
 * @access public
 */
$mysql_pass = MBDB::getDatabasePass("platinum");
/**
 * @var string The database for the MySQL connection
 * @version 3.0
 * @package master
 * @access public
 */
$mysql_data = MBDB::getDatabaseName("platinum");

// Connect + select
$dsn = "mysql:dbname={$mysql_data};host={$mysql_host}";
try {
   $lb_connection = new SpDatabaseConnection($dsn, $mysql_user, $mysql_pass);
} catch (SpDatabaseLoginException $e) {
   die("ERR:1:Could not connect to MySQL.\n");
}
$lb_connectionOpen = true;

function pdo_prepare($statement = "") {
   global $lb_connection;

   return $lb_connection->prepare($statement);
}

/**
 * Detects if a server with the given name exists
 * @version 3.0
 * @package master
 * @access public
 * @var $name The name of the server to check
 * @return boolean Whether or not a server with the given name exists
 */

function serverExists($name) {
	//Sanitize!
	$query = pdo_prepare("SELECT `key` FROM `servers` WHERE `name` = :name");
	$query->bind(":name", $name);
	$result = $query->execute();

	if (!$result)
		return false;

	if ($result->rowCount() > 0) {
		if ((list($key) = @$result->fetchIdx()) !== false)
			return $key;
		return true;
	}
	return false;
}

/**
 * Detects if a server with the given ip and port exists
 * @version 3.0
 * @package master
 * @access public
 * @var $ip The IP address of the server to check
 * @var $port The port of the server to check
 * @return boolean Whether or not a server with the given ip and port exists
 */

function serverExistsIP($ip, $port) {
	//Sanitize!
	$port = (int)$port;

	//We select key incase we actually do get a server
	$query = pdo_prepare("SELECT `key` FROM `servers` WHERE `address` = :ip AND `port` = :port");
	$query->bind(":ip", $ip);
	$query->bind(":port", $port);
	$result = $query->execute();

	if (!$result)
		return false;

	//Check and return
	if ($result->rowCount() > 0) {
		if ((list($key) = @$result->fetchIdx()) !== false)
			return $key;
		return true;
	}
	return false;
}

/**
 * Detects if a server with the given ip and port exists on the new master server system
 * @version 3.0
 * @package master
 * @access public
 * @var $ip The IP address of the server to check
 * @var $port The port of the server to check
 * @return boolean Whether or not a server with the given ip and port exists
 */

function serverExistsIPNew($ip, $port) {
	//Sanitize!
	$port = (int)$port;

	//We select key incase we actually do get a server
	$query = pdo_prepare("SELECT `key` FROM `mpservers` WHERE `address` = :ip AND `port` = :port");
	$query->bind(":ip", $ip);
	$query->bind(":port", $port);
	$result = $query->execute();

	if (!$result)
		return false;

	//Check and return
	if ($result->rowCount() > 0) {
		if ((list($key) = @$result->fetchIdx()) !== false)
			return $key;
		return true;
	}
	return false;
}

/**
 * Detects if a server with the given key exists
 * @version 3.0
 * @package master
 * @access public
 * @var $key The key of the server to check
 * @return boolean Whether or not a server with the given key exists
 */

function serverExistsKey($key) {
	//Selecting address because I can't figure out blank queries
	$query = pdo_prepare("SELECT `address` FROM `servers` WHERE `key` = :key");
	$query->bind(":key", $key);
	$result = $query->execute();

	if (!$result)
		return false;

	if ($result->rowCount() > 0)
		return true;
	return false;
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
	$query->bind(":key", $key);
   $result = $query->execute();

	if (!$result)
		return false;

   // If it doesn't exist, return nothing
   if (!$result->rowCount())
      return "";

   // Otherwise, this is simple
   $assoc = $result->fetch();
   return $assoc["value"];
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

function isGuest($user = "") {
	if ($user ==  "")
		$user = getPostValue("username");
	return (stristr($user, "Guest_") !== false) || $user == "Guest";
}

function shutdown() {
   global $lb_connection;

   $lb_connection = null;

   echo("FINISH\n");
}

/**
 * Encodes a name so torque can parse it
 * @version 0.1
 * @package leader
 * @access public
 * @var $name The username to encode
 * @return The username with all spaces escaped to "-SPC-"
 */

function escapeName($name) {
	$name = str_replace(" ", "-SPC-", $name);
	return $name;
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
		require_once("../jsupport.php");
		$username = getUser();
	}

	$query = pdo_prepare("SELECT `display`, `joomla` FROM `users` WHERE `username` = :username");
	$query->bind(":username", $username, PDO::PARAM_STR);
	$result = $query->execute();
	if ($result->rowCount()) {
		$row = $result->fetchIdx();
		if ($row[1]) {
			require_once("../jsupport.php");
			return getDisplay($username);
		} else
			return $row[0];
	}

	return $username;
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
	$access = getUserAccess($user);
	if ($access == 3)
		return 0;
	return $access;
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
	$query = pdo_prepare("SELECT `banned`,`access` FROM `users` WHERE `username` = :user");
	$query->bind(":user", $user, PDO::PARAM_STR);
	$result = $query->execute();
	if (!$result->rowCount())
		return -1;
	list($banned, $access) = $result->fetchIdx();

	if ($banned)
		return -3;

	return $access;
}

register_shutdown_function('shutdown');
?>
