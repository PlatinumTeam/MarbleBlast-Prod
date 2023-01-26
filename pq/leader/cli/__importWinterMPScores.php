<?php
define("PQ_RUN", true);
require_once("../Framework.php");

function unphiltime($time) {
	return $time + 1344917000;
}

// To clean
//DELETE FROM ex82r_mission_scores WHERE mission_id IN (
//	SELECT ex82r_missions.id
//  FROM ex82r_missions
//    JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
//  WHERE game_id = 6
//); DELETE FROM ex82r_matches;

$jdb = JoomlaSupport::db();

//Get all the user ids
$query = $jdb->prepare("SELECT `id`, LOWER(`username`) FROM `bv2xj_users`");
$query->execute();

$userLookup = [];
while (($row = $query->fetch()) !== false) {
	$user = strtolower(trim($row[1]));
	$userLookup[$user] = User::get($row[0]);
}

echo("Got all users\n");

//Get all the missions
$query = $db->prepare("
	SELECT `ex82r_missions`.`id`, `basename` FROM `ex82r_missions`
	  JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
	WHERE game_id = 19
");
$query->execute();

$missionLookup = [];
while (($row = $query->fetch()) !== false) {
	$id = $row[0];
	$basename = $row[1];
	$mission = Mission::getById($id);
	$missionLookup[$basename] = $mission;
}

echo("Got all missions\n");

$counter = 0;

$db->beginTransaction();

$query = $pdb->prepare("SELECT *, LOWER(`username`) AS `user` FROM `serverscores` WHERE modes LIKE '%snowball%'");
$query->execute();

$inserted = 0;

$currentMatch = [
	"key" => "",
	"players" => [],
	"mission" => null
];

while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	$username = trim($row["user"]);
	$score = $row["score"];
	$basename = $row["level"];
	$time = date("c", strtotime($row["timestamp"]));

	if (!array_key_exists($username, $userLookup)) {
		echo("Invalid user at    {$time} on {$basename} score of {$score} for {$username}\n");
		continue;
	}

	//Hunt shit that fucks up everything
	if (in_array($basename, ["MaximoCenter", "Lupis", "Nadir", "Marbleland", "SkateBattleRoyale"])) {
		$basename = $basename . "_Hunt";
	}

	if (!array_key_exists($basename, $missionLookup)) {
		//Maybe we added _hunt
		$basename = $basename . "_Hunt";
	}
	if (!array_key_exists($basename, $missionLookup)) {
		$basename = substr($basename, 0, -5);
		echo("Invalid mission at {$time} on {$basename} score of {$score} for {$username} \n");
		continue;
	}

//	echo("Found score at     {$time} on {$basename} score of {$score} for {$username}\n");

	$mission = $missionLookup[$basename];
	/* @var Mission $mission */

	$gamemode = strtolower($row["modes"]);
	$modes = explode(" ", $gamemode);
	//Remove obvious
	array_splice($modes, array_search("hunt", $modes), 1);
	array_splice($modes, array_search("spooky", $modes), 1);
	array_splice($modes, array_search("snowball", $modes), 1);
	//Remove stupid
	if (array_search("matan", $modes) !== false) array_splice($modes, array_search("matan", $modes), 1);
	if (array_search("glass", $modes) !== false) array_splice($modes, array_search("glass", $modes), 1);
	if (array_search("balanced", $modes) !== false) array_splice($modes, array_search("balanced", $modes), 1);
	$extraModes = implode(" ", $modes);
	$gamemode = "hunt";

	$gems1 = $row["gems1"];
	$gems2 = $row["gems2"];
	$gems5 = $row["gems5"];
	$gems10 = 0;
	//Uh
	$gems10 = ($score - $gems1 - ($gems2 * 2) - ($gems5 * 5)) / 10;

	$scoreType = "score";
//	echo("Mission: {$mission->basename}/{$row["level"]} Extra: $extraModes Score: $score Type: $scoreType\n");

	if ($currentMatch["key"] !== $row["key"]) {
		if ($currentMatch["key"] !== "") {
			//Put the old match through
			runMatch($currentMatch);
			$counter ++;
			if ($counter % 1000 == 0) {
				echo("Processed $counter matches...\n");
			}
		}

		unset($currentMatch["players"]);
		$currentMatch["players"] = [];
		$currentMatch["mission"] = $mission;
		$currentMatch["server_address"] = substr($row["server"], 0, strpos($row["server"], ":"));
		$currentMatch["server_port"] = substr($row["server"], strpos($row["server"], ":") + 1);
		$currentMatch["key"] = $row["key"];
		$currentMatch["teams"] = $row["teams"];
	}

	//Get the new rating for this mission
	$scoreInfo = [
		"username" => $username,
		"score" => $score,
		"scoreType" => $scoreType,
		"bonus" => 0,
		"gemCount" => $gems1 + $gems2 + $gems5 + $gems10,
		"team" => $row["team"],
		"gems" => [
			1 => $gems1,
			2 => $gems2,
			5 => $gems5,
			10 => $gems10
		],
		"time" => $time,
		"timePercent" => 1,
	];

	$currentMatch["players"][] = $scoreInfo;
}
runMatch($currentMatch);

$db->commit();

function runMatch($match) {
	global $db, $pdb, $userLookup, $missionLookup;

	/* @var Mission $mission */
	$mission = $match["mission"];
	MPRatings::getScoreRatings($match["players"], $mission);

	$count = count($match["players"]);
	//Insert the mp game record
	$query = $db->prepare("
		INSERT INTO `ex82r_matches` SET
		`mission_id` = :mission_id,
		`player_count` = :player_count,
		`server_address` = :server_address,
		`server_port` = :server_port
	");
	$query->bindValue(":mission_id", $mission->id);
	$query->bindValue(":player_count", $count);
	$query->bindValue(":server_address", $match["server_address"]);
	$query->bindValue(":server_port", $match["server_port"]);
	requireExecute($query);
	$matchId = $db->lastInsertId();

	//If this is teams mode we should record the teams and their SQL ids
	$teams = [];
	if ($match["teams"]) {
		$query = $pdb->prepare("SELECT * FROM serverteams WHERE scorekey = :key ORDER BY id ASC");
		$query->bindValue(":key", $match["key"]);
		$query->execute();
		$playerTeams = $query->fetchAll(PDO::FETCH_NUM);

		for ($i = 0; $i < count($playerTeams); $i++) {
			$playerTeam = $playerTeams[$i];
			$teamName = $playerTeam[2];

			//Do we already have this team?
			$teamId = null;
			foreach ($teams as $testTeam) {
				if ($testTeam["name"] == $teamName) {
					$teamId = $testTeam["id"];
					break;
				}
			}
			if ($teamId !== null) {
				echo("{$playerTeam[0]} is on team $teamName id $teamId\n");
				$username = strtolower($playerTeam[0]);
				foreach ($match["players"] as &$player) {
					if (strtolower($player["username"]) === $username) {
						$player["teamId"] = $teamId;
						echo("Found team id $teamId for $username\n");
						break;
					}
				}
				unset($player);
				continue;
			}
			//Insert this team and give them some info
			$query = $db->prepare("
				INSERT INTO `ex82r_match_teams` SET
				`match_id` = :match_id,
				`color` = :color,
				`name` = :name
			");
			$query->bindValue(":match_id", $matchId);
			$query->bindValue(":color", $playerTeam[3]);
			$query->bindValue(":name", $playerTeam[2]);
			requireExecute($query);
			$teamId = $db->lastInsertId();
			echo("{$playerTeam[0]} is on team $teamName id $teamId\n");

			$teams[] = [
				"id"   => $teamId,
				"name" => $playerTeam[2]
			];

			//And assign
			$username = strtolower($playerTeam[0]);
			foreach ($match["players"] as &$player) {
				if (strtolower($player["username"]) === $username) {
					$player["teamId"] = $teamId;
					echo("Created team id $teamId for $username\n");
					break;
				}
			}
			unset($player);
		}
		//For people whose teams didn't submit (old times)
		foreach ($match["players"] as &$player) {
			if ($player["teamId"] === null) {
				//Create a dummy team
				$teamName = "Unnamed team " . $player["team"];

				//Do we already have this team?
				$teamId = null;
				foreach ($teams as $testTeam) {
					if ($testTeam["name"] == $teamName) {
						$teamId = $testTeam["id"];
						break;
					}
				}
				if ($teamId !== null) {
					echo("{$player["username"]} is on team $teamName id $teamId\n");
					$player["teamId"] = $teamId;
					echo("Found team id $teamId for {$player["username"]}\n");
					continue;
				}

				//Insert this team and give them some info
				$query = $db->prepare("
					INSERT INTO `ex82r_match_teams` SET
					`match_id` = :match_id,
					`color` = :color,
					`name` = :name
				");
				$query->bindValue(":match_id", $matchId);
				$query->bindValue(":color", $player["team"] % 8);
				$query->bindValue(":name", $teamName);
				requireExecute($query);
				$teamId = $db->lastInsertId();

				echo("{$player["username"]} is on team $teamName id $teamId\n");
				echo("Created team id $teamId for {$player["username"]}\n");

				$teams[] = [
					"id"   => $teamId,
					"name" => $teamName
				];

				$player["teamId"] = $teamId;
			}
		} unset($player);
	}

	if ($match["teams"]) {
		foreach ($teams as &$team) {
			$team["score"] = 0;
			$team["count"] = 0;
			foreach ($match["players"] as $player) {
				if ($player["teamId"] == $team["id"]) {
					$team["score"] += $player["score"];
					$team["count"] ++;
				}
			}

			$query = $db->prepare("UPDATE `ex82r_match_teams` SET `player_count` = :player_count WHERE `id` = :id");
			$query->bindValue(":player_count", $team["count"]);
			$query->bindValue(":id", $team["id"]);
			$query->execute();
		} unset($team);

		//Sort teams by score
		usort($teams, function($a, $b) {
			return $b["score"] <=> $a["score"];
		});

		//Then mark team players with their scores
		$count = count($teams);
		for ($i = 0; $i < $count; $i++) {
			foreach ($match["players"] as &$player) {
				if ($player["teamId"] == $teams[$i]["id"]) {
					$player["place"] = $i + 1;
				}
			} unset($player);
		}

		$query = $db->prepare("UPDATE `ex82r_matches` SET `team_count` = :team_count WHERE `id` = :id");
		$query->bindValue(":team_count", $count);
		$query->bindValue(":id", $matchId);
		$query->execute();
	} else {
		//Sort players by score
		usort($match["players"], function($a, $b) {
			return $b["score"] <=> $a["score"];
		});
		$count = count($match["players"]);
		for ($i = 0; $i < $count; $i++) {
			$match["players"][$i]["place"] = $i + 1;
		}
	}

	foreach ($match["players"] as $player) {
		$user = $userLookup[strtolower($player["username"])];
		/* @var User $user */
		$sort = getScoreSorting(["score" => $player["score"], "type" => "score"]);

		echo("{$mission->basename}: {$player["username"]}: {$player["score"]} ({$player["scoreType"]})\n");

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
			`sort`          = :sort,
			`timestamp`     = :time
		");
		$query->bindValue(":user_id",    $user->id);
		$query->bindValue(":mission_id", $mission->id);
		$query->bindValue(":score",      $player["score"]);
		$query->bindValue(":scoreType",  $player["scoreType"]);
		$query->bindValue(":totalBonus", 0);
		$query->bindValue(":rating",     $player["rating"]);
		$query->bindValue(":gemCount",   $player["gemCount"]);
		$query->bindValue(":gems1",      $player["gems"]["1"]);
		$query->bindValue(":gems2",      $player["gems"]["2"]);
		$query->bindValue(":gems5",      $player["gems"]["5"]);
		$query->bindValue(":gems10",     $player["gems"]["10"]);
		$query->bindValue(":modifiers",  $player["modifiers"]);
		$query->bindValue(":sort",       $sort);
		$query->bindValue(":time",       $player["time"]);
		requireExecute($query);
		$scoreId = $db->lastInsertId();

		//Now get a mp game score record
		$query = $db->prepare("
			INSERT INTO `ex82r_match_scores` SET 
			`match_id` = :match_id,
			`user_id` = :user_id,
			`score_id` = :score_id,
			`team_id` = :team_id,
			`placement` = :placement
		");
		$query->bindValue(":match_id", $matchId);
		$query->bindValue(":user_id", $user->id);
		$query->bindValue(":score_id", $scoreId);
		$query->bindValue(":placement", $player["place"]);
		$query->bindValue(":team_id", $player["teamId"]);
		requireExecute($query);

		//And give the player their rating
		$query = $db->prepare("
			UPDATE `ex82r_user_ratings`
			SET `rating_mp` = `rating_mp` + :rating
			WHERE `user_id` = :user_id
		");
		$query->bindValue(":rating", $player["rating"]);
		$query->bindValue(":user_id", $user->id);
		requireExecute($query);
		$user->update();

		$results[] = [
			"username" => $player["username"],
			"rating" => $user->ratings["rating_mp"],
			"change" => $player["rating"],
			"place" => $player["place"]
		];
	}
}
