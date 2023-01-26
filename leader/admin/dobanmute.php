<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();
$banner = getUsername();

if ($access > 0) {
	$user    = $_POST["user"];
	$message = $_POST["message"];
	$end     = $_POST["end"];
	
	$mute    = array_key_exists("mute", $_POST);
	$deafen  = array_key_exists("deafen", $_POST);
	$block   = array_key_exists("block", $_POST);

	$query = pdo_prepare("INSERT INTO `bans` SET `username` = :username, `mute` = :mute, `deafen` = :deafen, `block` = :block, `message` = :message, `end` = FROM_UNIXTIME(:end), `sender` = :sender");

	$query->bind(":username", $user);
	$query->bind(":mute", $mute);
	$query->bind(":deafen", $deafen);
	$query->bind(":block", $block);
	$query->bind(":message", $message);
	$query->bind(":end", $end);
	$query->bind(":sender", $banner);

	$result = $query->execute();

	if ($mute) {
		postNotify("mute", $user, 0, $banner);
	}
	if ($deafen) {
		postNotify("deafen", $user, 0, $banner);
	}
	if ($block) {
		postNotify("ban", $user, 0, $banner);
		postNotify("kick", $user, 0, $banner);
	}

	if ($result)
		echo("GOOD\n");
	else
		echo("BAD\n");
}

?>