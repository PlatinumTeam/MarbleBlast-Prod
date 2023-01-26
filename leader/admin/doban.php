<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();
$banner = getUsername();

if ($access > 0) {
	$toban = $_POST["user"];
	$message = $_POST["message"];
	$banType = $_POST["banType"];
	postNotify("ban", $toban, 0, "$banner $message");
	postNotify("kick", $toban, 0, "$banner $message");
	$query = pdo_prepare("DELETE FROM `loggedin` WHERE `username` = :toban LIMIT 1");
	$query->bind(":toban", $toban, PDO::PARAM_STR);
	$result = $query->execute();

	if ($banType > 1) {
		$query = jPrepare("UPDATE `bv2xj_users` SET `block` = 1 WHERE `username` = :toban");
		$query->bind(":toban", $toban, PDO::PARAM_STR);
		$result = $query->execute();
	}

	$query = pdo_prepare("UPDATE `users` SET `access` = -3, `banned` = :banType, `banreason` = :message WHERE `username` = :toban AND `access` < :access");
	$query->bind(":banType", $banType);
	$query->bind(":message", $message, PDO::PARAM_STR);
	$query->bind(":toban", $toban, PDO::PARAM_STR);
	$query->bind(":access", $access, PDO::PARAM_INT);
	$result = $query->execute();
	if ($result)
		echo("GOOD\n");
	else
		echo("BAD\n");
}

?>