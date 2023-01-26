<?php
define("PQ_RUN", true);
require_once("../Framework.php");

$username = "XXXXXXXXX";
$user = User::get(JoomlaSupport::getUserId($username));
$gameType = "Single Player";

$query = $db->prepare("
	DELETE ex82r_user_scores FROM ex82r_user_scores
	JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
	JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
	WHERE user_id = :user_id
	AND game_type = :game_type
");
$query->bindValue(":user_id", $user->id);
$query->bindValue(":game_type", $gameType);
$query->execute();

//Reset rating columns
$query = $db->prepare("
	SELECT rating_column FROM ex82r_mission_games WHERE game_type = :game_type
");
$query->bindValue(":game_type", $gameType);
$query->execute();
$columns = $query->fetchAll(PDO::FETCH_COLUMN);

foreach ($columns as $column) {
	$query = $db->prepare("
		UPDATE ex82r_user_ratings SET `$column` = 0 WHERE user_id = :user_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->execute();
}