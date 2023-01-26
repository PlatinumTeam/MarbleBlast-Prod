<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

Login::requireLogin();

$user = Login::getCurrentUser();
$mission = Mission::getByParams(true, false);
if ($mission === null) {
	error("ARGUMENT invalid mission");
}

$scores = $user->getBestScores($mission, 5);
$result = [
    "scores" => $scores,
	"missionId" => $mission->id
];

if (stristr($mission->gamemode, "laps") !== false) {
	//Laps mission, get their best lap time as well
	$query = $db->prepare("
		SELECT `mission_id`, `time` FROM ex82r_user_lap_times
			WHERE `user_id` = :user_id
			AND `mission_id` = :mission_id
		ORDER BY `time` ASC
		LIMIT 1
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":mission_id", $mission->id);
	$query->execute();

	if ($query->rowCount()) {
		$result["bestLap"] = $query->fetch(PDO::FETCH_ASSOC);
	}
}

techo(json_encode($result));
