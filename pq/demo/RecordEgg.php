<?php
require("Database.php");

$uid = requireParam("uid");
$mission = requireParam("mission");

$userInfo = getUserInfoByUid($uid);
$missionInfo = getMissionInfoByBasename($mission);

$query = $db->prepare("SELECT COUNT(*) FROM `@_mission_eggs` WHERE `userid` = :userid AND `missionid` = :missionid");
$query->bindParam(":userid", $userInfo["id"]);
$query->bindParam(":missionid", $missionInfo["id"]);
$query->execute();
if ($query->fetchColumn(0) == 0) {
	$query = $db->prepare("INSERT INTO `@_mission_eggs` SET `userid` = :userid, `missionid` = :missionid");
	$query->bindParam(":userid", $userInfo["id"]);
	$query->bindParam(":missionid", $missionInfo["id"]);

	if ($query->execute()) {
		techo("SUCCESS");
	} else {
		techo("FAILURE");
	}
} else {
	techo("ALREADY");
}

