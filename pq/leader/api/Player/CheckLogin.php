<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$username = Login::getCurrentUsername();
$userId = JoomlaSupport::getUserId($username);
if ($userId === null) {
	// User does not exist
	techo(json_encode(["success" => false, "reason" => "username"]));
	die();
}
$user = User::get($userId);

if ($user->joomla["block"] && $user->joomla["activation"] != null) {
	techo(json_encode(["success" => false, "reason" => "activate"]));
	die();
}

if (!Login::isLoggedIn()) {
	techo(json_encode(["success" => false, "reason" => "password"]));
	die();
}

if ($user->joomla["block"]) {
	$reason = $user->leaderboards["banreason"];
	if (!$reason)
		$reason = "";
	techo(json_encode(["success" => false, "reason" => "banned", "ban_reason" => $reason]));
	die();
}

$version = requireParam("version");
if ($version < getLatestVersion()) {
	techo(json_encode(["success" => false, "reason" => "version"]));
	die();
}

$result = [
	"success" => true,
	"username" => $username,
	"id" => $userId
];

//Easy things
$result["access"] = $user->leaderboards["access"];
$result["key"] = getKey($user);
$result["display"] = sanitizeDisplayName($user->joomla["name"]);
$result["color"] = $user->joomla["colorValue"];
$result["titles"] = [
	"flair" => getTitle($user->joomla["titleFlair"]),
	"prefix" => getTitle($user->joomla["titlePrefix"]),
	"suffix" => getTitle($user->joomla["titleSuffix"])
];

$result["settings"] = getSettings($user);

techo(json_encode($result));

function getSettings(User $user) {
	global $pdb;
	$jdb = JoomlaSupport::db();

	$debuglogging = Platinum\getServerPref("debuglogging");

	$settings = [];

	if ($debuglogging) {
		if ($debuglogging == 2 || Login::isUserPrivilege($user, "pq.test.debugLogging"))
			$settings[] = "INFO LOGGING";
	}

	// Basic things like your access

	$time = Platinum\getServerTime();

	$settings[] = "INFO ACCESS " . $user->leaderboards["access"];
	$settings[] = "INFO DISPLAY " . $user->getDisplayName();
	$settings[] = "INFO SERVERTIME $time";

	// Various other settings and informations

	$welcome = Platinum\getWelcomeMessage(Login::isUserPrivilege($user, "pq.mod.chat"), $user->isGuest());
	$default = Platinum\escapeName(Platinum\getServerPref("defaultname"));
	// Address
	if (array_key_exists("HTTP_X_FORWARDED_FOR", $_SERVER)) {
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} else {
		$ip = $_SERVER["REMOTE_ADDR"];
	}

	$settings[] = "INFO WELCOME $welcome";
	$settings[] = "INFO DEFAULT $default";
	$settings[] = "INFO ADDRESS $ip";

	// Chat help

	$settings[] = "INFO HELP INFO " . Platinum\getServerPref("chathelp");
	if (Login::isUserPrivilege($user, "pq.chat.formatting"))
		$settings[] = "INFO HELP FORMAT " . Platinum\getServerPref("chathelpformat");
	$settings[] = "INFO HELP CMDLIST " . Platinum\getServerPref("chathelpcmdlist" . (Login::isUserPrivilege($user, "pq.mod.chat") ? "mod" : ""));
	$settings[] = "INFO PRIVILEGE " . Login::getUserPrivilege($user);

	// Friends list

	foreach (getFriends($user) as $friend) {
		$settings[] = $friend;
	}
	foreach (getBlocks($user) as $block) {
		$settings[] = $block;
	}

	// Status list

	$query = $pdb->prepare("SELECT * FROM `statuses`");
	$query->execute();

	if ($query->rowCount()) {
		while ((list ($status, $display) = $query->fetch(PDO::FETCH_NUM)) !== false) {
			$settings[] = "STATUS $status $display";
		}
	}

	// Colors

	$query = $pdb->prepare("SELECT * FROM `chatcolors`");
	$query->execute();

	if ($query->rowCount()) {
		while ((list ($ident, $color) = $query->fetch(PDO::FETCH_NUM)) !== false) {
			$settings[] = "COLOR $ident $color";
		}
	}

	// Flairs

	$query = $jdb->prepare("SELECT `title` FROM `bv2xj_user_titles` WHERE `position` = 0");
	$query->execute();
	while (($flair = $query->fetchColumn()) !== false) {
		$settings[] = "FLAIR $flair";
	}

	if (Platinum\getServerPref("wintermode")) {
		$settings[] = "WINTER";
	}
	if (Platinum\getServerPref("spookyevent")) {
		$settings[] = "2SPOOKY";
	}

	$settings[] = "LOGGED";
	return $settings;
}

function getFriends(User $user) {
	global $pdb;

	$friends = [];

	$query = $pdb->prepare("SELECT `username` FROM `users` WHERE `id` IN (SELECT `friendid` FROM `friends` WHERE `username` = :username)");
	$query->bindValue(":username", $user->getUsername());
	$query->execute();

	$friends[] = "FRIEND START";
	if ($query->rowCount()) {
		while (($friend = $query->fetchColumn()) !== false) {
			$friend = Platinum\escapeName($friend);
			$display = Platinum\escapeName(User::get(JoomlaSupport::getUserId($friend))->getDisplayName());
			$friends[] = "FRIEND NAME $friend $display";
		}
	}
	$friends[] = "FRIEND DONE";

	return $friends;
}

function getBlocks(User $user) {
	global $pdb;

	$blocks = [];

	$query = $pdb->prepare("SELECT `block` FROM `blocks` WHERE `username` = :username");
	$query->bindValue(":username", $user->getUsername());
	$query->execute();
	$blocks[] = "BLOCK START";
	if ($query->rowCount()) {
		while (($block = $query->fetchColumn()) !== false) {
			$block = Platinum\escapeName($block);
			$display = Platinum\escapeName(User::get(JoomlaSupport::getUserId($block))->getDisplayName());
			$blocks[] = "BLOCK NAME $block $display";
		}
	}
	$blocks[] = "BLOCK DONE";

	return $blocks;
}

/**
 * @param int $id
 * @return string
 */
function getTitle($id) {
	$jdb = JoomlaSupport::db();
	$query = $jdb->prepare("SELECT `title` FROM `bv2xj_user_titles` WHERE `id` = :id");
	$query->bindValue(":id", $id);
	$query->execute();

	if ($query->rowCount()) {
		return $query->fetchColumn(0);
	} else {
		return "";
	}
}

function getLatestVersion() {
	global $pdb;
	$query = $pdb->prepare("SELECT `version` FROM `versions` ORDER BY `id` DESC LIMIT 1");
	$query->execute();
	return $query->fetchColumn();
}

function getKey(User $user) {
	global $pdb;
	//Get their chat key, or generate a new one if we need to
	$query = $pdb->prepare("SELECT `chatkey` FROM `users` WHERE `username` = :username");
	$query->bindValue(":username", $user->getUsername());
	$query->execute();
	$chatkey = $query->fetchColumn();
	if ($chatkey == "" || $chatkey == false) {
		//No key found, make them a new one
		$chatkey = strRand(32);
		$query = $pdb->prepare("UPDATE `users` SET `chatkey` = :key WHERE `username` = :username");
		$query->bindValue(":key", $chatkey);
		$query->bindValue(":username", $user->getUsername());
		$query->execute();
	}

	return $chatkey;
}
