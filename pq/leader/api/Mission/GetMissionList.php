<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

if (Login::isLoggedIn()) {
	$override = Login::isPrivilege("pq.test.missionList");
} else {
	$override = 0;
}

//SP/MP
$gameType = param("gameType") ?? 'Single Player';

//Get a list of all the missions that are available to play
$missions = [];

//All of the official missions, already sorted for us by the database
$query = $db->prepare("
	SELECT `ex82r_missions`.*, `has_egg` FROM `ex82r_missions`
	JOIN `ex82r_mission_games` ON `game_id` = `ex82r_mission_games`.`id`
	JOIN `ex82r_mission_difficulties` ON `difficulty_id` = `ex82r_mission_difficulties`.`id`
	JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
	WHERE `game_type` = :gameType AND `is_local` = 0
	AND ((ex82r_mission_rating_info.disabled = 0 AND ex82r_mission_games.disabled = 0 AND ex82r_mission_difficulties.disabled = 0) OR :override = 1)
	ORDER BY `game_id` ASC, `difficulty_id` ASC, `sort_index` ASC
");
$query->bindValue(":gameType", $gameType);
$query->bindValue(":override", $override);
$query->execute();
while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	$game = $row["game_id"];
	$difficulty = $row["difficulty_id"];

	//Create the game if we haven't seen it yet
	if (!array_key_exists($game, $missions)) {
		$missions[$game] = [];
	}
	//Create the difficulty too
	if (!array_key_exists($difficulty, $missions[$game])) {
		$missions[$game][$difficulty] = [];
	}

	//Add this mission to its difficulty
	$missions[$game][$difficulty][] = $row;
}

//All the games
$query = $db->prepare("
	SELECT `id`, `name`, `display`, `force_gamemode`, `has_blast` FROM `ex82r_mission_games`
	WHERE `game_type` = :gameType AND (disabled = 0 OR :override = 1)
	ORDER BY `sort_index` ASC
");
$query->bindValue(":gameType", $gameType);
$query->bindValue(":override", $override);
$query->execute();
$games = $query->fetchAll(PDO::FETCH_ASSOC);

//All the difficulties
$query = $db->prepare("
	SELECT `ex82r_mission_difficulties`.* FROM `ex82r_mission_difficulties`
	JOIN `ex82r_mission_games` ON `game_id` = `ex82r_mission_games`.`id`
	WHERE `game_type` = :gameType
	AND ((ex82r_mission_games.disabled = 0 AND ex82r_mission_difficulties.disabled = 0) OR :override = 1)
	ORDER BY `sort_index` ASC
");
$query->bindValue(":gameType", $gameType);
$query->bindValue(":override", $override);
$query->execute();
$difficulties = $query->fetchAll(PDO::FETCH_ASSOC);

//Build the associative array into an indexed one because Torque can't sort missions on its own.
$output = [
	"games" => [],
	"gameType" => $gameType
];
foreach ($games as $game) {
	//Have arrays of difficulties for each game
	$game["difficulties"] = [];

	//And an array of missions for each game/difficulty associative
	foreach ($difficulties as $difficulty) {
		if ($difficulty["game_id"] !== $game["id"])
			continue;
		$difficulty["missions"] = $missions[$game["id"]][$difficulty["id"]] ?? [];
		$game["difficulties"][] = $difficulty;
	}
	//Nothing associative
	$output["games"][] = $game;
}

techo(json_encode($output));
