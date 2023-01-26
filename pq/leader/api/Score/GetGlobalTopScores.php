<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$mission = Mission::getByParams(true, false);
if ($mission === null) {
	error("Need mission");
}
$modifiers = param("modifiers") ?? 0;
$scores = $mission->getTopScores($modifiers);

if (Login::isPrivilege("pq.mod.extendedScores")) {
	if ($mission->gameInfo["game_type"] === "Single Player") {
		$columns = [
			["name" => "placement", "display" => "#", "type" => "place", "tab" => "1", "width" => "40"],
			["name" => "id", "display" => "Id", "type" => "string", "tab" => "40", "width" => "70"],
			["name" => "name", "display" => "Player", "type" => "string", "tab" => "110", "width" => "-140"],
			["name" => "rating", "display" => "Rating", "type" => "score", "tab" => "-290", "width" => "75"],
			["name" => "gems_1_point", "display" => "R", "type" => "string", "tab" => "-215", "width" => "35"],
			["name" => "gems_2_point", "display" => "Y", "type" => "string", "tab" => "-180", "width" => "35"],
			["name" => "gems_5_point", "display" => "B", "type" => "string", "tab" => "-145", "width" => "35"],
			["name" => "gems_10_point", "display" => "P", "type" => "string", "tab" => "-110", "width" => "35"],
			["name" => "score", "display" => "Score", "tab" => "-75", "width" => "75"]
		];
	} else {
		$columns = [
			["name" => "placement", "display" => "#", "type" => "place", "tab" => "1", "width" => "40"],
			["name" => "id", "display" => "Id", "type" => "string", "tab" => "40", "width" => "70"],
			["name" => "name", "display" => "Player", "type" => "string", "tab" => "110", "width" => "-145"],
			["name" => "gems_1_point", "display" => "R", "type" => "string", "tab" => "-215", "width" => "35"],
			["name" => "gems_2_point", "display" => "Y", "type" => "string", "tab" => "-180", "width" => "35"],
			["name" => "gems_5_point", "display" => "B", "type" => "string", "tab" => "-145", "width" => "35"],
			["name" => "gems_10_point", "display" => "P", "type" => "string", "tab" => "-110", "width" => "35"],
			["name" => "score", "display" => "Score", "tab" => "-75", "width" => "75"]
		];
	}
} else {
	if ($mission->gameInfo["game_type"] === "Single Player") {
		$columns = [
			["name" => "placement", "display" => "#", "type" => "place", "tab" => "1", "width" => "40"],
			["name" => "name", "display" => "Player", "type" => "string", "tab" => "40", "width" => "-190"],
			["name" => "score", "display" => "Score", "tab" => "-145", "width" => "-75"],
			["name" => "rating", "display" => "Rating", "type" => "score", "tab" => "0", "width" => "75"]
		];
	} else {
		//Multiplayer
		$columns = [
			["name" => "placement", "display" => "#", "type" => "place", "tab" => "1", "width" => "40"],
			["name" => "name", "display" => "Player", "type" => "string", "tab" => "40", "width" => "-75"],
			["name" => "score", "display" => "Score", "tab" => "-75", "width" => "75"]
		];
	}
}

$results = [
	"columns"   => $columns,
	"missionId" => $mission->id
];
$results["scores"] = $scores;


techo(json_encode($results));
