<?php
define("PQ_RUN", true);
require_once("../Framework.php");

if (!Login::isPrivilege("pq.mod.editRatings")) {
	header("HTTP/1.1 401 Unauthorized");
	die();
}

$id = requireParam("scoreId");

//Update their rating accordingly
$query = $db->prepare("SELECT * FROM ex82r_user_scores WHERE `id` = :id");
$query->bindValue(":id", $id);
$query->execute();

if ($query->rowCount()) {
	$score = $query->fetch();

	//Rating before we remove this score
	$query = $db->prepare("
		SELECT `rating` FROM ex82r_user_scores
		WHERE user_id = :user_id
		AND mission_id = :mission_id
		ORDER BY sort ASC
		LIMIT 1
	");
	$query->bindValue(":user_id", $score["user_id"]);
	$query->bindValue(":mission_id", $score["mission_id"]);
	$query->bindValue(":score_id", $id);
	$query->execute();

	$ratingWith = 0;
	if ($query->rowCount()) {
		$ratingWith = $query->fetchColumn(0);
	}

	//Rating after we remove this score
	$query = $db->prepare("
		SELECT `rating` FROM ex82r_user_scores
		WHERE user_id = :user_id
		AND mission_id = :mission_id
		AND id != :score_id
		ORDER BY sort ASC
		LIMIT 1
	");
	$query->bindValue(":user_id", $score["user_id"]);
	$query->bindValue(":mission_id", $score["mission_id"]);
	$query->bindValue(":score_id", $id);
	$query->execute();

	$ratingWithout = 0;
	if ($query->rowCount()) {
		$ratingWithout = $query->fetchColumn(0);
	}

	$query = $db->prepare("
		SELECT rating_column FROM ex82r_mission_games
		JOIN ex82r_missions ON ex82r_mission_games.id = ex82r_missions.game_id
		WHERE ex82r_missions.id = :mission_id
	");
	$query->bindValue(":mission_id", $score["mission_id"]);
	$query->execute();

	$ratingField = $query->fetchColumn(0);

	//What the change will do to our rating
	$change = $ratingWithout - $ratingWith;
	$query = $db->prepare("
		UPDATE ex82r_user_ratings
		SET rating_general = rating_general + :change,
		$ratingField = $ratingField + :change2
		WHERE user_id = :user_id
	");
	$query->bindValue(":change", $change);
	$query->bindValue(":change2", $change);
	$query->bindValue(":user_id", $score["user_id"]);
	$query->execute();

	$query = $db->prepare("DELETE FROM ex82r_user_scores WHERE `id` = :id");
	$query->bindValue(":id", $id);
	$success = $query->execute();

	//Send it to discord
	$mission = Mission::getById($score["mission_id"]);
	$user = User::get($score["user_id"]);

	if ($score["score_type"] === "time") {
		$prettyScore .= formatTime(round($score["score"]), true);
	} else {
		$prettyScore .= round($score["score"]);
	}

	$message = "DELETE SCORE: \"{$mission->name}\" ({$mission->gameInfo["display"]} {$mission->difficultyInfo["display"]}): {$user->joomla["name"]} {$prettyScore}";

	DiscordLink::getInstance()->sendMessage("XXXXXXXXXXXXXX", $message);

	echo(json_encode([
		"info" => $score,
		"change" => $change,
		"error" => ($success ? "none" : $query->errorCode())
	]));
}