<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0) {
	$tomute = $_POST["user"];
	$query = pdo_prepare("UPDATE `users` SET `muteIndex` = 0 WHERE `username` = :tomute");
	$query->bind(":tomute", $tomute, PDO::PARAM_STR);
	$result = $query->execute();
	if ($result)
		echo("GOOD\n");
	else
		echo("BAD\n");
}

?>
