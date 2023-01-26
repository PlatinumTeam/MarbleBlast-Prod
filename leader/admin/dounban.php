<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0) {
	$toban = $_POST["user"];
	$query = pdo_prepare("UPDATE `users` SET `access` = 0, `banned` = 0 WHERE `username` = :toban");
	$query->bind(":toban", $toban, PDO::PARAM_STR);
	$result = $query->execute();
	if ($result)
		echo("GOOD\n");
	else
		echo("BAD\n");

	//Unban them from joomla as well
	require_once("../jsupport.php");
	$config = new JConfig();

	$dsn = "mysql:dbname=" . $config->db . ";host=" . $config->host;
	$con = null;
	try {
		$con = new SpDatabaseConnection($dsn, $config->user, $config->password);
	} catch (SpDatabaseLoginException $e) {
		die("Could not open database connection.");
	}
	if ($con == null) {
		die("Could not connect to database.");
	}

	$query = $con->prepare("UPDATE {$config->dbprefix}users SET `block` = 0 WHERE username = :username");
	$query->bind(":username", $toban);
	$query->execute();

	$con = null;
}

?>
