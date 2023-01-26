<?php
$allow_nonwebchat = true;
$ignore_keys = true;

// Open the database connection
require_once("opendb.php");

require_once("version.php");
if (isTorque() && !checkVersion(false)) {
   sig(30); //Update Client
}

// Only add if we are from torque or submitting

if (isTorque() || isSubmitting())
   echo("ONLINE\n");
else
   echo("This server is online!<br>");

$query = pdo_prepare("SELECT (SELECT COUNT(*) FROM `loggedin`) + (SELECT COUNT(*) FROM `jloggedin` WHERE `username` NOT IN (SELECT `username` FROM `loggedin`))");
$result = $query->execute();
$rows = $result->fetchIdx(0);

if (isTorque() || isSubmitting())
   echo("PLAYERS $rows\n");
else
   echo("Currently $rows players online.<br>");

$query = pdo_prepare("SELECT `time` FROM `notify` ORDER BY `time` DESC LIMIT 0, 1");
$result = $query->execute();
$row = $result->fetchIdx();

$lastTime = $row[0];
$lastUpdate = getServerTime() - $lastTime;

if (isTorque() || isSubmitting())
   echo("LASTUPDATE $lastTime\nLASTUPDATEAGO $lastUpdate\n");
else
   echo("The last update was at server time $lastTime, or $lastUpdate seconds ago.<br>");

echo(PHP_EOL);
?>
