<?php
define("MINIMUM_ACCESS", 1);

function documentHeader($title = "Untitled") {
?>
<html>
	<head>
		<title>MBP Administration: <?=$title?></title>
		<!--[if lt IE 9]>
			<script src="../assets/js/html5shiv.js"></script>
		<![endif]-->
		<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
		<script type="text/javascript" src="assets/js/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="assets/bootstrap/js/bootstrap.min.js"></script>
	</head>
	<body style="padding: 60px 0px; height: 100%">
<?php
}

function documentFooter() {
?>
   </body>
</html>
<?php
}

$navbarCreation = false;

function navbarCreate($title) {
	global $navbarCreation;

	if ($navbarCreation)
		throw new Exception("Navbar Already Initialized.", 1);
	else {
		?>
<div class="navbar navbar-fixed-top">
	<div class="navbar-inner">
		<a class="brand" style="margin-left: 20px" href="admin.php"><?=$title?></a>
			<ul class="nav">
		<?php
	}

	$navbarCreation = true;
}

function navbarAddItem($title, $link = NULL, $active = false) {
	global $navbarCreation;

	if (!$navbarCreation)
		throw new Exception("Navbar not Initialized.", 1);
	else {
		if (is_array($link) && $link !== NULL) {
			echo("<li class=\"dropdown\">
				  <a href=\"javascript:void(0);\" class=\"dropdown-toggle\" id=\"dcontrols\" data-toggle=\"dropdown\">$title <b class=\"caret\"></b></a>
				  <ul class=\"dropdown-menu\" aria-labelledby=\"dcontrols\">");
			foreach ($link as $title => $page) {
				if ($page !== NULL)
					echo("<li><a href=\"$page\">$title</a></li>");
				else
					echo("<li class=\"disabled\"><a href=\"javascript:void(0);\">$title</a></li>");
			}
			echo("</ul></li>");
		} else
			echo("<li" . ($active ? " class=\"active\"" : "") . ">" . ($link !== NULL ? "<a href=\"$link\">" : "<p style=\"margin: 0px 10px;\" class=\"navbar-text\">") . $title . ($link !== NULL ? "</a>" : "</p>") . "</li>");
	}
}

function navbarEnd() {
	global $navbarCreation;

	if (!$navbarCreation)
		throw new Exception("Navbar not Initialized.", 1);
	else
		echo("         </ul>
		</div>
	</div>");

	$navbarCreation = false;
}

function navbarAddSet($set) {
	$navbarsets["default"] = array("Home" => "admin.php");
	$navbarsets["mod"] = array("Controls" => array(
										  "Ban/Mute Players"         => is_file("ban.php")          ? "ban.php"          : NULL,
										  "Chat Log"                 => is_file("chatlog.php")      ? "chatlog.php"      : NULL,
										  "Kick Players"             => is_file("kick.php")         ? "kick.php"         : NULL,
										  "Set QOTD"                 => is_file("qotdadd.php")      ? "qotdadd.php"      : NULL,
										  "View Scores"              => is_file("scores.php")       ? "scores.php"       : NULL
										  ));
	$navbarsets["admin"] = array("Controls" => array(
										  "Achievement Viewer"       => is_file("achievements.php") ? "achievements.php" : NULL,
										  "Add Version"              => is_file("versionadd.php")   ? "versionadd.php"   : NULL,
										  "Ban/Mute Players"         => is_file("ban.php")          ? "ban.php"          : NULL,
										  "Chat Log"                 => is_file("chatlog.php")      ? "chatlog.php"      : NULL,
										  "Dedicated Servers"        => is_file("servers.php")      ? "servers.php"      : NULL,
										  "Kick Players"             => is_file("kick.php")         ? "kick.php"         : NULL,
										  "Modify Custom Categories" => is_file("categories.php")   ? "categories.php"   : NULL,
										  "Modify Super Challenges"  => is_file("scedit.php")       ? "scedit.php"       : NULL,
										  "Multiplayer Rating Test"  => is_file("mpratingtest.php") ? "mpratingtest.php" : NULL,
										  "Recalculate Scores"       => is_file("recalculate.php")  ? "recalculate.php"  : NULL,
										  "Server Settings"          => is_file("settings.php")     ? "settings.php"     : NULL,
										  "Set MOTD"                 => is_file("motdadd.php")      ? "motdadd.php"      : NULL,
										  "Set QOTD"                 => is_file("qotdadd.php")      ? "qotdadd.php"      : NULL,
										  "Tracking Data"            => is_file("tracking.php")     ? "tracking.php"     : NULL,
										  "Transfer User Data [wip]" => is_file("transferuser.php") ? "transferuser.php" : NULL,
										  "View Scores"              => is_file("scores.php")       ? "scores.php"       : NULL,
										  "View User Data"           => is_file("users.php")        ? "users.php"        : NULL
										  ));
   global $pma_url;
   if (isset($pma_url))
      $navbarsets["admin"] += array("phpMyAdmin" => $pma_url);

	$set = $navbarsets[$set];

	if ($set === NULL)
		throw new Exception("Invalid Navbar Set.", 1);

	$currentPage = $_SERVER["PHP_SELF"];
	$parts = explode("/", $currentPage);
	$file = $parts[count($parts) - 1];

	foreach ($set as $title => $page) {
		navbarAddItem($title, $page, $page === $file);
	}
}

function tableRow($content) {
	echo("<tr><td>$content</td></tr>");
}

function tableRowLink($href, $title, $mute = false) {
	if ($mute)
		tableRow("<p class=\"muted\" style=\"margin-bottom: 0px\">$title</p>");
	else
		tableRow("<a href=\"$href\">$title</a>");
}

function sidebarCreate() {
	$access = getAccess();
?>
<div class="span3 well">
	<h2 class="text-center">Controls</h2>
	<br>
	<table class="table"> <?php

//The table varies for different statuses
if ($access == 1) {
	tableRowLink("ban.php",          "Ban/Mute Players",         !is_file("ban.php"));
	tableRowLink("chatlog.php",      "Chat Log",                 !is_file("chatlog.php"));
	tableRowLink("kick.php",         "Kick Players",             !is_file("kick.php"));
	tableRowLink("qotdadd.php",      "Set QOTD",                 !is_file("qotdadd.php"));
	tableRowLink("scores.php",       "View Scores",              !is_file("scores.php"));
}
if ($access == 2) {
	tableRowLink("achievements.php", "Achievement Viewer",       !is_file("achievements.php"));
	tableRowLink("versionadd.php",   "Add Version",              !is_file("versionadd.php"));
	tableRowLink("ban.php",          "Ban/Mute Players",         !is_file("ban.php"));
	tableRowLink("chatlog.php",      "Chat Log",                 !is_file("chatlog.php"));
	tableRowLink("servers.php",      "Dedicated Servers",        !is_file("servers.php"));
	tableRowLink("kick.php",         "Kick Players",             !is_file("kick.php"));
	tableRowLink("categories.php",   "Modify Custom Categories", !is_file("categories.php"));
	tableRowLink("scedit.php",       "Modify Super Challenges",  !is_file("scedit.php"));
	tableRowLink("mpratingtest.php", "Multiplayer Rating Test",  !is_file("mpratingtest.php"));
	tableRowLink("recalculate.php",  "Recalculate Scores",       !is_file("recalculate.php"));
	tableRowLink("settings.php",     "Server Settings",          !is_file("settings.php"));
	tableRowLink("motdadd.php",      "Set MOTD",                 !is_file("motdadd.php"));
	tableRowLink("qotdadd.php",      "Set QOTD",                 !is_file("qotdadd.php"));
	tableRowLink("tracking.php",     "Tracking Data",            !is_file("tracking.php"));
	tableRowLink("transferuser.php", "Transfer User Data [wip]", !is_file("transferuser.php"));
	tableRowLink("scores.php",       "View Scores",              !is_file("scores.php"));
	tableRowLink("users.php",        "View User Data",           !is_file("users.php"));
}
tableRow("<br>");
if ($access == 2)
global $pma_url;
if (isset($pma_url))
   tableRowLink($pma_url, "phpMyAdmin");
tableRowLink("../logout.php?admin=true", "Log Out");

								 ?>
	</table>
</div>
<?php
}

function accessDenied() {
	?>
<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1>Access Denied</h1>
			<p>You do not have the required access level to view this page. <a href="admin.php">Back</a></p>
		</div>
	</div>
</div>
	<?php
}

function accessTitle($access = -1) {
	switch ($access) {
		case -2: case -3: return "Banned";
		case -1: return "Not Logged In";
		case 0:  return "User";
		case 1:  return "Moderator";
		case 2:  return "Administrator";
		default: return "Not Logged In";
	}
}


function statusTitle($status = 0) {
	switch ($status) {
		case -3: return "Banned";
		case -2: return "Loading";
		case -1: return "Invisible";
		case 0:  return "Chat";
		case 1:  return "Level Select";
		case 2:  return "Playing";
		case 3:  return "Webchat";
		case 4:  return "Challenge";
		case 5:  return "Super Challenge";
		case 6:  return "Hosting";
		case 7:  return "Game Lobby";
		case 8:  return "Dedicated";
		case 9:  return "Away";
		default: return "Chat";
	}
}

function getAccess() {
	$login = checkPostLogin();
	$access = $login == 7 ? getUserPrivilege(getPostValue("username")) : -1;
	if ($login == 27)
		return -2;
	if (isGuest(getPostValue("username")))
		return 0;
	return $access;
}

?>
