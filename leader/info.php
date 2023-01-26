<?php
list($username, $password) = getPostValues("username", "password");
$time = getServerTime();
$access = getUserAccess(getPostValue("username"));

$debuglogging = getServerPref("debuglogging");
if ($debuglogging) {
	if ($debuglogging == 2 || $access > 0)
		echo("LOGGING\n");
}

$query = pdo_prepare("SELECT `id` FROM `chat` ORDER BY `id` DESC LIMIT 1");
$result = $query->execute();

echo("LASTCHAT " . ($result->fetchIdx(0) - 20) . "\n");

$query = pdo_prepare("SELECT `id` FROM `notify` ORDER BY `id` DESC LIMIT 1");
$result = $query->execute();

echo("LASTNOTIFY " . $result->fetchIdx(0) . "\n");
echo("ACCESS " . getUserAccess($username) . "\n");
echo("SERVERTIME $time\n");

$query = pdo_prepare("SELECT `rating` FROM `users` WHERE `username` = :user");
$query->bind(":user", $username, PDO::PARAM_STR);
$result = $query->execute();

$current = intVal($result->fetch("rating"));

echo("CURRATING $current\n");

$pingtime         = getServerPref("pingtime");
$slowpingtime     = getServerPref("slowpingtime");
$userpingtime     = getServerPref("userpingtime");
$slowuserpingtime = getServerPref("slowuserpingtime");
$welcome          = str_replace("\n", "\\n", getServerPref("welcome"));
$default          = escapeName(getServerPref("defaultname"));
$ip = "localhost";
if ($_SERVER['HTTP_X_FORWARD_FOR'])
	$ip = $_SERVER['HTTP_X_FORWARD_FOR'];
else
	$ip = $_SERVER['REMOTE_ADDR'];

echo("PINGTIME $pingtime\n");
echo("SLOWPINGTIME $slowpingtime\n");
echo("USERPINGTIME $userpingtime\n");
echo("SLOWUSERPINGTIME $slowuserpingtime\n");
echo("WELCOME $welcome\n");
echo("DEFAULT $default\n");
echo("ADDRESS $ip\n");

//Chat help
echo("HELP INFO " . getServerPref("chathelp") . "\n");
echo("HELP FORMAT " . getServerPref("chathelpformat") . "\n");
echo("HELP CMDLIST " . getServerPref("chathelpcmdlist" . ($access > 0 ? "mod" : "")) . "\n");

if (getPostValue("username") != "") {
	include("mpachievementcheck.php");
	mpDumpAchievements($username);
	echo("\n");

	// Dump friend list
	include("friendlist.php");
}
?>