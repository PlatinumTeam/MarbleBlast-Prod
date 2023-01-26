<?php

define("PQ_RUN", true);
require_once("../Framework.php");

Login::requirePrivilege("pq.admin.updateMissions");
$gameType = param("gameType") ?? "Single Player";
$missions = json_decode(param("missions"), true);
if ($missions === null) {
	error("Can't decode: " . json_last_error_msg());
}

define("DELETE_MISSING", param("deleteMissing") ?? false);

define("DRY_RUN", param("dryRun") ?? true);

function updateMission($mission) {
	global $db;

	$query = $db->prepare("SELECT `id` FROM `ex82r_mission_games` WHERE `name` = :name");
	$query->bindValue(":name", $mission["game"]);
	$query->execute();
	if ($query->rowCount()) {
		$game = $query->fetchColumn(0);
	} else {
		if (DELETE_MISSING) {
			//Game doesn't exist anymore, erase
			deleteMission($mission);
		}
		return false;
	}
	$query = $db->prepare("SELECT `id` FROM `ex82r_mission_difficulties` WHERE `name` = :name AND `game_id` = :game");
	$query->bindValue(":name", $mission["difficulty"]);
	$query->bindValue(":game", $game);
	$query->execute();
	if ($query->rowCount()) {
		$diff = $query->fetchColumn(0);
	} else {
		if (DELETE_MISSING) {
			//Difficulty doesn't exist anymore, erase
			deleteMission($mission);
		}
		return false;
	}

	$mission["game_id"] = $game;
	$mission["difficulty_id"] = $diff;
	$mission["is_custom"] = "0";

	$update = false;

	$old = Mission::getById($mission["id"]);
	if ($old === null) {
		$update = true;
		echo("NEW: {$mission["name"]}\n");
		createMission($mission);
	} else {
		foreach ($old->missionInfo as $key => $value) {
			if ($mission[$key] != $value && $mission[$key] != 0) {
				$update = true;
				echo("UPDATE: {$mission["file"]}: $key from $value to {$mission[$key]}\n");

			}
		}
		foreach ($mission["rating_info"] as $key => $value) {
			if ($old->ratingInfo[$key] != $value && ($value != 0 || $key === "gem_count_10")) {
				$update = true;
				echo("UPDATE: {$mission["file"]}: $key from {$old->ratingInfo[$key]} to {$mission["rating_info"][$key]}\n");
			}
		}
	}
	if (!$update) {
		return false;
	}
	if (DRY_RUN) {
		return true;
	}

	$query = $db->prepare("
		UPDATE `ex82r_missions` SET
		`game_id` = :game,
		`difficulty_id` = :diff,
		`name` = :name,
		`file` = :file,
		`modification` = :modification,
		`basename` = :basename,
		`gamemode` = :mode,
		`sort_index` = :index,
		`hash` = :hash
		WHERE `id` = :id
	");

	$query->bindValue(":game", $game);
	$query->bindValue(":diff", $diff);
	$query->bindValue(":name", $mission["name"]);
	$query->bindValue(":file", $mission["file"]);
	$query->bindValue(":modification", $mission["modification"]);
	$query->bindValue(":basename", $mission["basename"]);
	$query->bindValue(":mode", $mission["gamemode"]);
	$query->bindValue(":index", $mission["sort_index"]);
	$query->bindValue(":hash", $mission["hash"]);
	$query->bindValue(":id", $mission["id"]);
	requireExecute($query);

	//Update rating info too
	$updateRating = false;
	$query = "UPDATE `ex82r_mission_rating_info` SET";
	foreach ($mission["rating_info"] as $column => $value) {
		if ($old->ratingInfo[$column] != $value && $value != 0) {
			$query .= "`$column` = :$column";
			$query .= ",";
			$updateRating = true;
		}
	}
	//Don't do this if nothing is getting an update though
	if ($updateRating) {
		$query = substr($query, 0, strlen($query) - 1);
		$query .= " WHERE `mission_id` = :id";

		$query = $db->prepare($query);
		foreach ($mission["rating_info"] as $column => $value) {
			if ($old->ratingInfo[$column] != $value && $value != 0) {
				$query->bindValue(":$column", $value);
			}
		}
		$query->bindValue(":id", $mission["id"]);
		requireExecute($query);
	}

	return true;
}

function deleteMission($mission) {
	global $db;

	if (!DRY_RUN) {
		$query = $db->prepare("DELETE FROM `ex82r_missions` WHERE `id` = :id");
		$query->bindValue(":id", $mission["id"]);
		requireExecute($query);
	}

	echo("DELETE: {$mission["id"]} {$mission["file"]}\n");
}

function createMission(&$mission) {
	global $db;

	if (!DRY_RUN) {
		$query = $db->prepare("INSERT INTO ex82r_missions SET game_id = :game, difficulty_id = :difficulty");
		$query->bindValue(":game", $mission["game_id"]);
		$query->bindValue(":difficulty", $mission["difficulty_id"]);
		requireExecute($query);

		$mission["id"] = $db->lastInsertId();
	} else {
		$mission["id"] = time();
	}

	createRatingInfo($mission);
}

function createRatingInfo($mission) {
	global $db;

	if (!DRY_RUN) {
		$query = $db->prepare("INSERT INTO ex82r_mission_rating_info SET mission_id = :id");
		$query->bindValue(":id", $mission["id"]);
		requireExecute($query);
	}
}

function updateMissions($missions, $gameType) {
	global $db;

	//Cleanup
	$query = $db->prepare("SELECT ex82r_missions.* FROM ex82r_missions
LEFT JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
WHERE ex82r_mission_rating_info.mission_id IS NULL");
	requireExecute($query);
	$missing = $query->fetchAll(PDO::FETCH_ASSOC);
	foreach ($missing as $mission) {
		createRatingInfo($mission);
	}

	//Find stuff that is deleted
	$query = $db->prepare("SELECT ex82r_missions.* FROM `ex82r_missions`
JOIN `ex82r_mission_games` ON ex82r_missions.game_id = ex82r_mission_games.id
JOIN ex82r_mission_difficulties ON ex82r_missions.difficulty_id = ex82r_mission_difficulties.id
WHERE ex82r_mission_games.game_type = :gameType AND is_custom = 0");
	$query->bindValue(":gameType", $gameType);
	requireExecute($query);
	$results = $query->fetchAll(PDO::FETCH_ASSOC);

	//Mark stuff with dupe names
	foreach ($results as &$result) {
		foreach ($results as $other) {
			if ($result["id"] === $other["id"])
				continue;

			if ($result["name"] === $other["name"]) {
				$result["dupe"] = true;
			}
		}
	}
	unset($result);

	foreach ($results as &$result) {
		//See if we have it
		$found = false;
		foreach ($missions as &$mission) {
			if (isset($mission["id"])) {
				continue;
			}
			if ($mission["file"] === $result["file"]) {
				//Definitely the same
				$found = true;
				$mission["id"] = $result["id"];
				break;
			}
		}
		unset($mission);
		if (!$found) {
			//RIP
			$result["notFound"] = 1;
		}
	}
	unset($result);

	foreach ($results as $result) {
		if ($result["notFound"]) {
			$found = false;
			//See if something is close
			foreach ($missions as &$mission) {
				if (isset($mission["id"])) {
					continue;
				}

				if ((levenshtein($mission["basename"], $result["basename"]) < 5)
					|| (levenshtein($mission["name"], $result["name"]) < 5)) {
					echo("PROBABLE MOVE: {$result["file"]} to {$mission["file"]}\n");
					//Probably a rename
					$found = true;
					$mission["id"] = $result["id"];
					break;
				}
			}
			unset($mission);

			if (!$found && DELETE_MISSING) {
				//No, it's gone
				deleteMission($result);
			}
		}
	}

	//First find any missions we've added
	foreach ($missions as $mission) {
		$new = !isset($mission["id"]);

		if (updateMission($mission)) {
			if ($new) {
				echo("NEWFILE {$mission["file"]}\n");
			}
		} else {

			//Deleted
			if ($new) {
				echo("UNKNOWN GAME {$mission["game"]} {$mission["difficulty"]}\n");
			}
		}
	}

	if (DRY_RUN) {
		echo("(But not actually)\n");
	} else {
		echo("Finished.\n");
	}
}

$db->beginTransaction();

function requireTransactional(PDOStatement $query) {
	global $db;
	if (!$query->execute()) {
		$db->rollBack();
		error("SQL Error: ");
		techo("Error code: " . $query->errorCode());
		techo(json_encode($query->errorInfo()));
	}
}

updateMissions($missions, $gameType);

$db->commit();
