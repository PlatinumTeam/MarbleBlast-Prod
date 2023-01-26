<?php

function techo($contents) {
	if ($_SERVER["HTTP_USER_AGENT"] === "Torque 1.0") {
		$script = basename($_SERVER["PHP_SELF"], ".php");
		echo("pq " . $script . " " . $contents . "\n");
	} else {
		echo($contents);
	}
}

function param($name) {
	if (array_key_exists($name, $_COOKIE))
		return $_COOKIE[$name];
	if (array_key_exists($name, $_POST))
		return $_POST[$name];
	return null;
}

function requireParam($name) {
	$value = param($name);
	if ($value === null)
		error("ARGUMENT {$name}");
	return $value;
}

function error($text) {
	techo($text);
	die();
}

function getMissionInfoByBasename($name) {
	global $db;
	$query = $db->prepare("SELECT * FROM `@_missions_official` WHERE `basename` = :name");
	$query->bindParam(":name", $name);
	$query->execute();
	$result = $query->fetch(PDO::FETCH_ASSOC);
	return $result;
}

function getMissionInfoById($id) {
	global $db;
	$query = $db->prepare("SELECT * FROM `@_missions_official` WHERE `id` = :id");
	$query->bindParam(":id", $id);
	$query->execute();
	$result = $query->fetch(PDO::FETCH_ASSOC);
	return $result;
}

function getUserInfoByUid($uid) {
	global $db;
	$query = $db->prepare("SELECT * FROM `@_users` WHERE `uid` = :uid");
	$query->bindParam(":uid", $uid);
	$query->execute();
	$result = $query->fetch(PDO::FETCH_ASSOC);
	return $result;
}

function formatTime($time, $timeBonus = false) {
	if ($time == 0 || $time == 5998999) {
		if ($timeBonus && $time == 0)
			return "00:00.000";
		return "99:59.999";
	}
	$neg = $time < 0;
	$time = abs($time);
	$ms = $time % 1000;
	$time = ($time - $ms) / 1000;
	$s  = $time % 60;
	$m  = ($time - $s) / 60;
	return ($neg ? "-" : "") . str_pad($m, 2, "0", STR_PAD_LEFT) . ":" . str_pad($s, 2, "0", STR_PAD_LEFT) . "." . str_pad($ms, 3, "0", STR_PAD_LEFT);
}