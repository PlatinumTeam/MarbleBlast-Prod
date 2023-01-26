<?php
define("PQ_RUN", true);
require_once("../Framework.php");

if (!Login::isPrivilege("pq.mod.editRatings")) {
	header("HTTP/1.1 401 Unauthorized");
	die();
}

$mission = Mission::getByParams();
$info = [
	"missionInfo" => $mission->missionInfo,
	"ratingInfo" => $mission->ratingInfo,
	"difficultyInfo" => $mission->difficultyInfo,
	"gameInfo" => $mission->gameInfo
];

echo(json_encode($info));
