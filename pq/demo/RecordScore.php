<?php
require("Database.php");

$uid       = requireParam("uid");
$mission   = requireParam("mission");
$score     = requireParam("score");
$scoreType = requireParam("scoreType");

$totalBonus = param("totalBonus") ?? 0;
$gemCount   = param("gemCount") ?? 0;

$gems     = [];
$gems[1]  = param("gems1")  ?? 0;
$gems[2]  = param("gems2")  ?? 0;
$gems[5]  = param("gems5")  ?? 0;
$gems[10] = param("gems10") ?? 0;

if ($gems[1] > 0) {
	$gemCount = $gems[1] + $gems[2] + $gems[5] + $gems[10];
}

$userInfo = getUserInfoByUid($uid);
$missionInfo = getMissionInfoByBasename($mission);

$query = $db->prepare("INSERT INTO `lw3qp_mission_scores` SET
	`userid` = :userid,
	`missionid` = :missionid,
	`score` = :score,
	`score_type` = :scoreType,
	`total_bonus` = :totalBonus,
	`gem_count` = :gemCount,
	`gems_1_point` = :gems1,
	`gems_2_point` = :gems2,
	`gems_5_point` = :gems5,
	`gems_10_point` = :gems10");

$query->bindParam(":userid", $userInfo["id"]);
$query->bindParam(":missionid", $missionInfo["id"]);
$query->bindParam(":score", $score);
$query->bindParam(":scoreType", $scoreType);
$query->bindParam(":totalBonus", $totalBonus);
$query->bindParam(":gemCount", $gemCount);
$query->bindParam(":gems1", $gems[1]);
$query->bindParam(":gems2", $gems[2]);
$query->bindParam(":gems5", $gems[5]);
$query->bindParam(":gems10", $gems[10]);

if ($query->execute()) {
	techo("SUCCESS");
} else {
	techo("FAILURE");
}
