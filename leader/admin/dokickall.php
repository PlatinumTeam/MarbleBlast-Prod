<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$kicker = getUsername();
$access = getAccess();

if ($access > 0) {
	$query = pdo_prepare("SELECT `username` FROM `loggedin`");
	$result = $query->execute();
	while (($name = $result->fetchIdx(0)) !== false) {
		postNotify("kick", $name, 1, "$kicker $message");
		postNotify("logout", $name, -1);
	}

	$query = pdo_prepare("UPDATE `users` SET `kicknext` = 1 WHERE `username` IN (SELECT `username` FROM `loggedin`)");
	$result = $query->execute();
	if ($result)
		echo("GOOD\n");
	else
		echo("BAD\n");
}

?>
