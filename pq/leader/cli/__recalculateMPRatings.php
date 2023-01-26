<?php
define("PQ_RUN", true);
require_once("../Framework.php");

function setRatingColumn($userId, $column, $rating) {
	global $db;

	//Get current rating for them for messages
	$query = $db->prepare("SELECT `$column` FROM `ex82r_user_ratings` WHERE `user_id` = :user_id");
	$query->bindValue(":user_id", $userId);
	$query->execute();
	$current = $query->fetchColumn(0);

	if ($current != $rating) {
		echo("Update $column for $userId, $current --> $rating\n");
	}

	$query = $db->prepare("UPDATE `ex82r_user_ratings` SET `$column` = :rating WHERE `user_id` = :user_id");
	$query->bindValue(":rating", $rating);
	$query->bindValue(":user_id", $userId);
	$query->execute();
}

function recalcMatch($row) {
	global $db;

	$matchId = $row["id"];

	$mQuery = $db->prepare("
		SELECT
			ex82r_match_scores.user_id AS user_id,
			ex82r_match_scores.id AS match_score_id,
			ex82r_user_scores.id AS score_id,
			score,
			time_percent AS `timePercent`,
			placement AS `place`,
			rating
		 FROM ex82r_match_scores
		 JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		WHERE match_id = :match_id
	");
	$mQuery->bindValue(":match_id", $matchId);
	$mQuery->execute();

	$matchScores = $mQuery->fetchAll(PDO::FETCH_ASSOC);
	$mission = Mission::getById($row["mission_id"]);

	//Get scores array
	$scores = $matchScores;
	MPRatings::getScoreRatings($scores, $mission);

	for ($i = 0; $i < count($scores); $i ++) {
		$new = $scores[$i];
		$old = $matchScores[$i];

		if ($old["rating"] < $new["rating"] && $matchId > 200167) {
			$timePercent = $old["rating"] / $new["rating"];
			echo("Match {$matchId} {$old["match_score_id"]} time% update on {$mission->name} for user {$old["user_id"]}: 1 --> $timePercent\n");

			$uQuery = $db->prepare("UPDATE ex82r_match_scores SET time_percent = :percent WHERE id = :id");
			$uQuery->bindValue(":percent", $timePercent);
			$uQuery->bindValue(":id", $old["match_score_id"]);
			$uQuery->execute();
		} else if ($old["rating"] != $new["rating"]) {
			echo("Match {$matchId} {$old["match_score_id"]} update for user {$old["user_id"]} on {$mission->name}: {$old["rating"]} --> {$new["rating"]} \n");

			$uQuery = $db->prepare("UPDATE ex82r_user_scores SET rating = :rating WHERE id = :id");
			$uQuery->bindValue(":rating", $new["rating"]);
			$uQuery->bindValue(":id", $old["score_id"]);
			$uQuery->execute();
		}
	}
}

//Multiplayer ratings

/*
//Recalculate all matches
$query = $db->prepare("
	SELECT ex82r_matches.* FROM ex82r_matches
	JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
	JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
	WHERE rating_column = 'rating_mp'
	AND ex82r_matches.id > 200167
	ORDER BY ex82r_matches.id ASC
");
$query->execute();

$db->beginTransaction();
while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	recalcMatch($row);
}
$db->rollBack();
*/

$query = $db->prepare("
	SELECT ex82r_match_scores.`user_id` AS `user_id`, SUM(`rating`) AS `rating` FROM ex82r_matches
	  JOIN ex82r_match_scores ON ex82r_matches.id = ex82r_match_scores.match_id
	  JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
	  JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
	  JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
	WHERE player_count > 1
	  AND rating_column = 'rating_mp'
	GROUP BY ex82r_match_scores.user_id
");
$query->execute();
while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	$userId = $row["user_id"];
	$rating = $row["rating"];
	setRatingColumn($userId, "rating_mp", $rating);
}

echo("Got multiplayer ratings\n");
