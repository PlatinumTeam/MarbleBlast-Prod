<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0) {
	$todel = $_POST["user"];
	$query = pdo_prepare("DELETE FROM `users` WHERE `username` = :todel AND `access` < :access");
	$query->bind(":todel", $todel);
	$query->bind(":access", $access);
	$result = $query->execute();
	if ($result)
		echo("GOOD\n");
	else
		echo("BAD\n");
}

?>
