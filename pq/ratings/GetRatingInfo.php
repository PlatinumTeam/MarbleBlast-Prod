<?php
define("PQ_RUN", true);
require_once("../leader/Framework.php");

$mission = Mission::getByParams();
$ratingInfo = $mission->ratingInfo;
unset($ratingInfo["disabled"]);
unset($ratingInfo["notes"]);
$info = [
	"missionInfo" => $mission->missionInfo,
	"ratingInfo" => $ratingInfo,
	"difficultyInfo" => $mission->difficultyInfo,
	"gameInfo" => $mission->gameInfo
];

echo(json_encode($info));
