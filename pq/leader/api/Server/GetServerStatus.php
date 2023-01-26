<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$version = getLatestVersion();
$online = $db->getSetting("online");

$query = $pdb->prepare("SELECT (SELECT COUNT(*) FROM `loggedin` WHERE location >= 0) + (SELECT COUNT(*) FROM `jloggedin` WHERE `username` NOT IN (SELECT `username` FROM `loggedin`) AND location >= 0)");
$query->execute();
$players = $query->fetchColumn(0);

techo(json_encode([
	"online" => $online,
	"version" => $version,
	"players" => $players
]));

function getLatestVersion() {
	global $db;

	$query = $db->prepare("SELECT version FROM `ex82r_versions` ORDER BY `id` DESC LIMIT 1");
	$query->execute();

	$version = $query->fetchColumn(0);
	return $version;
}
