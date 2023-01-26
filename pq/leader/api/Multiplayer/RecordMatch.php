<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$mission    = Mission::getByParams(true, true);
$scores     = flattenPOSTArray(param("scores"));
$players    = param("players")    ?? count($scores);
$port       = param("port")       ?? 28000;
$teamMode   = param("teammode")   ?? false;
$scoreType  = param("scoreType")  ?? "score";
$totalBonus = param("totalBonus") ?? 0;
$teams      = $teamMode ? flattenPOSTArray(param("teams")) : [];
$extraModes = param("extraModes");

if ($mission === null) {
	error("need mission");
}

if ($extraModes != null) {
	$extraModes = implode(" ", $extraModes);
}

//Ignore guest players
$scores = array_filter($scores, function($player) {
	return substr($player["username"], 0, 6) !== "Guest_";
});

//Get the ratings for each player
MPRatings::getScoreRatings($scores, $mission);

if (array_key_exists("HTTP_X_FORWARDED_FOR", $_SERVER)) {
	$remote_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
} else {
	$remote_ip = $_SERVER["REMOTE_ADDR"];
}

//Insert the mp game record
$query = $db->prepare("
	INSERT INTO `ex82r_matches` SET
	`mission_id` = :mission_id,
	`player_count` = :player_count,
	`server_address` = :server_address,
	`server_port` = :server_port
");
$query->bindValue(":mission_id", $mission->id);
$query->bindValue(":player_count", $players);
$query->bindValue(":server_address", $remote_ip);
$query->bindValue(":server_port", $port);
$query->execute();
$matchId = $db->lastInsertId();

//If this is teams mode we should record the teams and their SQL ids
if ($teamMode) {
	for ($i = 0; $i < count($teams); $i ++) {
		$team = $teams[$i];
		$query = $db->prepare("
			INSERT INTO `ex82r_match_teams` SET
			`match_id` = :match_id,
			`color` = :color,
			`name` = :name
		");
		$query->bindValue(":match_id", $matchId);
		$query->bindValue(":color", $team["color"]);
		$query->bindValue(":name", $team["name"]);
		$query->execute();
		$teams[$i]["id"] = $db->lastInsertId();
	}
}


$results = [];

//Need to insert rows for each player
foreach ($scores as $player) {
	$user = User::get(JoomlaSupport::getUserId($player["username"]));

	//User doesn't exist, don't rate them
	if ($user === null)
		continue;

	$sort = getScoreSorting(["score" => $player["score"], "type" => $scoreType]);

	//Score record
	$query = $db->prepare("
		INSERT INTO ex82r_user_scores SET
		`user_id`       = :user_id,
		`mission_id`    = :mission_id,
		`score`         = :score,
		`score_type`    = :scoreType,
		`total_bonus`   = :totalBonus,
		`rating`        = :rating,
		`gem_count`     = :gemCount,
		`gems_1_point`  = :gems1,
		`gems_2_point`  = :gems2,
		`gems_5_point`  = :gems5,
		`gems_10_point` = :gems10,
		`modifiers`     = :modifiers,
		`extra_modes`   = :extra_modes,
		`sort`          = :sort
	");
	$query->bindValue(":user_id",     $user->id);
	$query->bindValue(":mission_id",  $mission->id);
	$query->bindValue(":score",       $player["score"]);
	$query->bindValue(":scoreType",   $scoreType);
	$query->bindValue(":totalBonus",  $totalBonus);
	$query->bindValue(":rating",      $player["rating"]);
	$query->bindValue(":gemCount",    $player["gemCount"]);
	$query->bindValue(":gems1",       $player["gems1"]);
	$query->bindValue(":gems2",       $player["gems2"]);
	$query->bindValue(":gems5",       $player["gems5"]);
	$query->bindValue(":gems10",      $player["gems10"]);
	$query->bindValue(":modifiers",   $player["modifiers"]);
	$query->bindValue(":extra_modes", $extraModes);
	$query->bindValue(":sort",        $sort);
	requireExecute($query);
	$scoreId = $db->lastInsertId();

	//Now get a mp game score record
	$query = $db->prepare("
		INSERT INTO `ex82r_match_scores` SET 
		`match_id` = :match_id,
		`user_id` = :user_id,
		`score_id` = :score_id,
		`team_id` = :team_id,
		`placement` = :placement,
		`time_percent` = :time_percent
	");
	$query->bindValue(":match_id", $matchId);
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":score_id", $scoreId);
	$query->bindValue(":placement", $player["place"]);
	$query->bindValue(":time_percent", $player["timePercent"]);
	$teamId = null;
	if ($teamMode) {
		foreach ($teams as $team) {
			if ($team["number"] == $player["team"]) {
				$teamId = $team["id"];
				break;
			}
		}
	}
	$query->bindValue(":team_id", $teamId);
	requireExecute($query);

	//And give the player their rating
	$query = $db->prepare("
		UPDATE `ex82r_user_ratings`
		SET `rating_mp` = `rating_mp` + :rating
		WHERE `user_id` = :user_id
	");
	$query->bindValue(":rating", $player["rating"] ?? 0);
	$query->bindValue(":user_id", $user->id);
	requireExecute($query);

	if ($players > 1 && !$mission->custom && $mission->gameInfo["rating_column"] === 'rating_mp') {
		//Update their win streaks
		if ($player["place"] === 1) {
			$query = $db->prepare("
				UPDATE `ex82r_user_streaks`
				SET mp_games = mp_games + 1
				WHERE user_id = :user_id
			");
			$query->bindValue(":user_id", $user->id);
			requireExecute($query);
		} else {
			$query = $db->prepare("
				UPDATE `ex82r_user_streaks`
				SET mp_games = 0
				WHERE user_id = :user_id
			");
			$query->bindValue(":user_id", $user->id);
			requireExecute($query);
		}
	}

	//Winter mode snowball counts
	if (array_key_exists("snowballs", $player)) {
		$snowballs = $player["snowballs"];
		$snowballHits = $player["snowballhits"];

		$query = $db->prepare("
			INSERT INTO ex82r_user_event_snowballs SET
				score_id = :score_id,
				snowballs = :snowballs,
				hits = :hits
		");
		$query->bindValue(":score_id", $scoreId);
		$query->bindValue(":snowballs", $snowballs);
		$query->bindValue(":hits", $snowballHits);
		requireExecute($query);
	}

	$user->update();

	$results[] = [
		"username" => $player["username"],
	    "rating" => $user->ratings["rating_mp"],
	    "change" => $player["rating"],
		"place" => $player["place"]
	];
}

techo(json_encode($results));
