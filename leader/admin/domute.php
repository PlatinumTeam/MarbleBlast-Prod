<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();
$banner = getUsername();

if ($access > 0) {
	$tomute = $_POST["user"];
	$length = $_POST["length"];

	$query = pdo_prepare("UPDATE `users` SET `muteIndex` = :length WHERE `username` = :tomute AND `access` < :access");
	$query->bind(":length", $length / 30, PDO::PARAM_STR);
	$query->bind(":tomute", $tomute, PDO::PARAM_STR);
	$query->bind(":access", $access, PDO::PARAM_INT);
	$result = $query->execute();
	if ($result)
		echo("GOOD\n");
	else
		echo("BAD\n");
}

?>