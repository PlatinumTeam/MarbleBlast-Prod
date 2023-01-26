<?php
define("PQ_RUN", true);
require_once("../Framework.php");

//Get all the missions
$query = $db->prepare("
	SELECT ex82r_missions.id FROM `ex82r_missions`
	WHERE game_id = 4
");
$query->execute();

$awesomeTotal = 0;

while (($row = $query->fetch()) !== false) {
	$id = $row[0];
	$mission = Mission::getById($id);

	//Is it a score?
	if (stripos($mission->gamemode, "hunt") !== false) {
		//Awesome score
		$awesomeScoreInfo = ["type" => "score", "score" => $mission->ratingInfo["awesome_score"]];
	} else if (stripos($mission->gamemode, "gemmadness") !== false) {
		if ($mission->ratingInfo["awesome_score"] == 0) {
			//1ms less than awesome time
			$awesomeScoreInfo = [
				"type" => "time",
				"score" => $mission->ratingInfo["awesome_time"] - 1,
				"modifiers" => 0 | Modifiers::GemMadnessAll
			];
 		} else if ($mission->ratingInfo["awesome_score"] == $mission->ratingInfo["hunt_max_score"]) {
			//1ms less than par time-- but still a time
			$awesomeScoreInfo = [
				"type" => "time",
				"score" => $mission->ratingInfo["par_time"] - 1,
				"modifiers" => 0 | Modifiers::GemMadnessAll
			];
		} else {
			//Awesome score
			$awesomeScoreInfo = ["type" => "score", "score" => $mission->ratingInfo["awesome_score"]];
		}
	} else {
		//1ms less than awesome time
		$awesomeScoreInfo = ["type" => "time", "score" => $mission->ratingInfo["awesome_time"] - 1];
	}

	if ($awesomeScoreInfo["score"] == -1 || $awesomeScoreInfo["score"] == 0) {
		continue;
	}

	SPRatings::getScoreRating($awesomeScoreInfo, $mission);

	$awesomeTotal += $awesomeScoreInfo["rating"];

	$best = $mission->getTopScores(0, 10);
	if (count($best) === 0) {
		continue;
	}

	for ($i = 0; $i < count($best); $i ++) {
		$bestInfo = $best[$i];
		if ($bestInfo["user_id"] != 818 && $bestInfo["user_id"] != 263) {
			break;
		}
	}


	if (($bestInfo["modifiers"] & (Modifiers::BeatAwesomeTime | Modifiers::BeatAwesomeScore)) != 0) continue;

	echo("Best on {$mission->name}: {$bestInfo["score_type"]} {$bestInfo["score"]} awesome is {$mission->ratingInfo["awesome_score"]} {$mission->ratingInfo["awesome_time"]}\n");
}
