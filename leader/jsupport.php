<?php
if (!defined('_JEXEC')) {
	define( '_JEXEC', 1 );
	define( 'DS', DIRECTORY_SEPARATOR );
	define('JPATH_BASE', dirname(__DIR__)); //this is when we are in the root
	define('__DIR__', JPATH_BASE);

	require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
	require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

	$mainframe = JFactory::getApplication('site');
	$mainframe->initialise();

	jimport("joomla.user.authorization");
	jimport("joomla.user.authentication");
}

function getLogin($username, $password) {
	ob_start();
	$user = JFactory::getUser(JUserHelper::getUserId($username));

	//print_r($user);

	if ($user->id == 0) {
		$ret = 6; //User does not exist
		echo("User does not exist!\n");
	} else if ($user->guest) {
		$ret = 6; //User does not exist
		echo("User is a guest!\n");
	}/* else if ($user->block && $user->activation != "") {
		$ret = 19; //You're banned
		echo("User is not activated\n");
	} */ else if (!checkPassword($username, $password)) {
		$ret = 6; //User / pass wrong
		echo("Password is wrong!\n");
	} else {
		checkCreateUser($user);
		$ret = 7; //Logged in success
		//echo("Check creating user\n");
	}
	ob_end_clean();
	return $ret;
}

function getLoginErr($username, $password) {
	//ob_start();
	$user = JFactory::getUser(JUserHelper::getUserId($username));

	//print_r($user);

	if ($user->id == 0) {
		$ret = "User does not exist!";
	} else if ($user->guest) {
		$ret = "User is a guest!";
	} else if (!checkPassword($username, $password)) {
		$ret = "Password is wrong!";
	} else {
		checkCreateUser($user);
		$ret = "Check creating user";
	}
	//ob_end_clean();
	return $ret;
}


// http://forum.codejoomla.com/open-source/joomla/authenticate-external-app-against-joomla-db-1958.html
function checkPassword( $username, $password )
{
	$credentials = array("username" => $username, "password" => $password);
	$options = array('remember' => false, 'silent' => false);

	// Get the global JAuthentication object.
	$authenticate = JAuthentication::getInstance();
	$response = $authenticate->authenticate($credentials, $options);

	if (defined("SOCKET_SERVER")) {
		// echo("Checking: $username => $password\n");
		// print_r($response);
	}

	if ($response->status === JAuthentication::STATUS_SUCCESS) {
		return true;
	} else {
		echo($response->error_message);
	}
	return false;
}

function getUserPass($username) {
	$sql1 = jPrepare("SELECT password FROM #__users WHERE username = :username");
	$sql1->bind(":username", $username);
	$query1 = $sql1->execute();
	$row = $query1->fetch();
	$dbpass = $row['password'];

	unset($config);

	return $dbpass;
}

function sanitize($name) {
	return substr(str_replace(array("[", "]"), "", mb_convert_encoding($name, "ASCII")), 0, 24);
//	return substr(str_replace(array("[", "]", " "), "", mb_convert_encoding($name, "ASCII")), 0, 24);
}

function getUser($username = null) {
	$uid = null;
	if ($username == null)
		$uid = &JFactory::getUser()->id;
	else
		$uid = JUserHelper::getUserId($username);

	$query = jPrepare("SELECT `username` FROM #__users WHERE `id` = :uid");
	$query->bind(":uid", $uid);
	$result = $query->execute();

	if (!$result->rowCount())
		return $username;

	return sanitize($result->fetchIdx(0));
}

function getDisplay($username = null) {
	$uid = null;
	if ($username == null)
		$uid = &JFactory::getUser()->id;
	else
		$uid = JUserHelper::getUserId($username);

	$query = jPrepare("SELECT `name` FROM #__users WHERE `id` = :uid");
	$query->bind(":uid", $uid);
	$result = $query->execute();

	if (!$result->rowCount())
		return $username;

	return sanitize($result->fetchIdx(0));
}

function getColor($username = null) {
	$uid = null;
	if ($username == null)
		$uid = &JFactory::getUser()->id;
	else
		$uid = JUserHelper::getUserId($username);

	$query = jPrepare("SELECT `colorValue` FROM #__users WHERE `id` = :uid");
	$query->bind(":uid", $uid);
	$result = $query->execute();

	if (!$result->rowCount())
		return "000000";

	return sanitize($result->fetchIdx(0));
}

function getTitles($username = null) {
	$uid = null;
	if ($username == null)
		$uid = &JFactory::getUser()->id;
	else
		$uid = JUserHelper::getUserId($username);

	$query = jPrepare(
	"SELECT `title`, 'flair' FROM `#__user_titles` WHERE `id` = (SELECT `titleFlair` FROM `#__users` WHERE `id` = :uid)
		UNION
	SELECT `title`, 'prefix' FROM `#__user_titles` WHERE `id` = (SELECT `titlePrefix` FROM `#__users` WHERE `id` = :uid)
		UNION
	SELECT `title`, 'suffix' FROM `#__user_titles` WHERE `id` = (SELECT `titleSuffix` FROM `#__users` WHERE `id` = :uid)");
	$query->bind(":uid", $uid);
	$result = $query->execute();

	if (!$result->rowCount())
		return array("", "", "");

	$rows = $result->fetchAll();

	return array(
		$rows[0]["title"] ?? "",
		$rows[1]["title"] ?? "",
		$rows[2]["title"] ?? ""
	);
}

function awardTitle($username = null, $titleid = 0) {
	$uid = null;
	if ($username == null)
		$uid = &JFactory::getUser()->id;
	else
		$uid = JUserHelper::getUserId($username);

	//Make sure they don't have it first!
	$query = jPrepare("SELECT * FROM `#__user_titles_earned` WHERE `userid` = :uid AND `titleid` = :titleid");
	$query->bind(":uid", $uid);
	$query->bind(":titleid", $titleid);
	$result = $query->execute();

	if ($result->rowCount()) {
		return;
	}

	//Make sure the title exists before giving it to them
	$query = jPrepare("SELECT `position` FROM `#__user_titles` WHERE `id` = :titleid");
	$query->bind(":titleid", $titleid);
	$result = $query->execute();

	if (!$result->rowCount()) {
		return;
	}

	$position = $result->fetchIdx(0);
	$column = "";
	if      ($position == 0) $column = "titleFlair";
	else if ($position == 1) $column = "titlePrefix";
	else if ($position == 2) $column = "titleSuffix";

	//Actually give it to them
	$query = jPrepare("INSERT INTO `#__user_titles_earned` SET `userid` = :uid, `titleid` = :titleid");
	$query->bind(":uid", $uid);
	$query->bind(":titleid", $titleid);
	$query->execute();

	//And set it as their title, because why not
	if ($column != "") {
		$query = jPrepare("UPDATE `#__users` SET `$column` = :titleid WHERE `id` = :uid");
		$query->bind(":titleid", $titleid);
		$query->bind(":uid", $uid);
		$query->execute();
	}
}

function checkCreateUser($user) {
	// Make sure they have a leaderboards account
	$query = pdo_prepare("SELECT * FROM `users` WHERE `username` = :user LIMIT 1");
	$query->bind(":user", $user->username);
	$result = $query->execute();
	if (!$result->rowCount() && $result) {
		//echo("ATTEMPTING TO MAKE AN ACCOUNT\n");
		// Create them an account!
		$lower = strToLower($user->username);
		$email = $user->email;
		$query = pdo_prepare("INSERT INTO `users` (`display`, `username`, `pass`, `salt`, `email`, `showemail`, `secretq`, `secreta`, `joomla`) VALUES (:display, :username, :pass, :salt, :email, :showemail, :secretq, :secreta, :joomla)");
		$query->bind(":display", $user->username);
		$query->bind(":username", $lower);
		$query->bind(":pass", "nope");
		$query->bind(":salt", "nope");
		$query->bind(":email", $email);
		$query->bind(":showemail", 0);
		$query->bind(":secretq", "");
		$query->bind(":secreta", "");
		$query->bind(":joomla", 1);
		$query->execute();
	}
}

function getHasColor($username = null) {
	$uid = null;
	if ($username == null)
		$uid = &JFactory::getUser()->id;
	else
		$uid = JUserHelper::getUserId($username);

	$query = jPrepare("SELECT `hasColor` FROM #__users WHERE `id` = :uid");
	$query->bind(":uid", $uid);
	$result = $query->execute();

	if (!$result->rowCount())
		return false;

	return $result->fetchIdx(0);
}

function awardColor($username = null) {
	$uid = null;
	if ($username == null)
		$uid = &JFactory::getUser()->id;
	else
		$uid = JUserHelper::getUserId($username);

	//Pick a random color
	$color = rand(0, 0xFFFFFF);
	$color = sprintf("%x", $color);
	//Give it to them

	$query = jPrepare("UPDATE #__users SET `hasColor` = 1, `colorValue` = :color WHERE `id` = :uid");
	$query->bind(":uid", $uid);
	$query->bind(":color", $color);
	$result = $query->execute();
}

$jcon = null;
$jopen = false;

function jOpenDB() {
	global $jcon, $jopen;

	if ($jopen)
		return;

	$config = new JConfig();

	$dsn = "mysql:dbname=" . $config->db . ";host=" . $config->host;
	$jcon = null;
	try {
		$jcon = new SpDatabaseConnection($dsn, $config->user, $config->password);
		$jopen = true;
	} catch (SpDatabaseLoginException $e) {
		die("Could not open database connection.");
	}
	if ($jcon == null) {
		die("Could not connect to database.");
	}

	register_shutdown_function('jCloseDB');
}

function jCloseDB() {
	$jopen = false;
	$jcon = null;
}

function jPrepare($query) {
	/* @var SpDatabaseConnection $jcon */
	global $jcon;

	$config = new JConfig();
	$query = str_replace("#__", $config->dbprefix, $query);

	return $jcon->prepare($query);
}

jOpenDB();
