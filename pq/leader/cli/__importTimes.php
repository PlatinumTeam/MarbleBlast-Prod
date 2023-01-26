<?php
define("PQ_RUN", true);
require_once("../Framework.php");

//Takes about 1h to run, fair warning

function unphiltime($time) {
	return $time + 1344917000;
}

$jdb = new Database("joomla", "bv2xj_");

//Get all the user ids
$query = $jdb->prepare("SELECT `id`, LOWER(`username`) FROM `bv2xj_users`");
$query->execute();

$userLookup = [];
while (($row = $query->fetch()) !== false) {
	$user = trim($row[1]);
	$userLookup[$user] = User::get($row[0]);
}

//Get all the missions
$query = $db->prepare("
	SELECT `ex82r_missions`.`id`, `basename` FROM `ex82r_missions`
	JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
	WHERE game_type = 'Single Player'
");
$query->execute();

$missionLookup = [];
while (($row = $query->fetch()) !== false) {
	$id = $row[0];
	$basename = $row[1];
	$mission = Mission::getById($id);
	$missionLookup[stripLevel($basename)] = $mission;
}

$db->prepare("DELETE FROM ex82r_user_scores")->execute();

$query = $pdb->prepare("SELECT *, LOWER(`username`) AS `user` FROM `scores`");
$query->execute();

$inserted = 0;

while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	$username = trim($row["user"]);
	$score = $row["score"];
	$basename = stripLevel($row["level"]);
	$origin = $row["origin"];
	$modifiers = $row["modifiers"];
	$oldRating = $row["rating"];
	$time = date("c", unphiltime($row["time"]));

	if (!array_key_exists($username, $userLookup)) {
		echo("Invalid user    at {$time} on {$basename} score of {$score} for {$username}\n");
		continue;
	}
	if (!array_key_exists($basename, $missionLookup)) {
		echo("Invalid mission at {$time} on {$basename} score of {$score} for {$username} \n");
		continue;
	}

	$user = $userLookup[$username];
	$mission = $missionLookup[$basename];

	//Get the new rating for this mission
	$scoreInfo = [
		"score" => $score,
		"type" => "time",
	    "modifiers" => $modifiers,
	    "bonus" => 0,
	    "gemCount" => $mission->ratingInfo["gem_count"],
	    "gems" => [
	    	1 => $mission->ratingInfo["gem_count"],
		    2 => 0,
		    5 => 0,
		    10 => 0
	    ],
	    "time" => $time
	];
	$rating = SPRatings::getScoreRating($scoreInfo, $mission);

	if ($rating != $oldRating) {
		echo("Invalid rating  at {$time} on {$basename} score of {$score} gives {$rating} now, {$oldRating} before \n");
	}

	//Insert it
	doInsert($scoreInfo, $mission, $user);

	$inserted ++;
	if ($inserted % 1000 == 0) {
		echo("\rInserted {$inserted}");
	}
}

function doInsert($scoreInfo, Mission $mission, User $user) {
	global $db;

	//Use any modifiers that the score rater gives us
	$modifiers = $scoreInfo["modifiers"];

	$sort = getScoreSorting($scoreInfo);

//What their last best score is so we can give them the difference in rating
	$bests = $user->getBestScores($mission, 1);
	if (count($bests) == 1) {
		$lastBest = $bests[0];
	} else {
		//Dummy last best result
		$lastBest = [
			"rating" => 0
		];
	}

	$query = $db->prepare("INSERT INTO ex82r_user_scores SET
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
	$userId = $user->id;
	$missionId = $mission->id;

	$query->bindValue(":user_id", $userId);
	$query->bindValue(":mission_id", $missionId);
	$query->bindValue(":score", $scoreInfo["score"]);
	$query->bindValue(":scoreType", $scoreInfo["type"]);
	$query->bindValue(":totalBonus", $scoreInfo["bonus"]);
	$query->bindValue(":rating", $scoreInfo["rating"]);
	$query->bindValue(":gemCount", $scoreInfo["gemCount"]);
	$query->bindValue(":gems1", $scoreInfo["gems"][1]);
	$query->bindValue(":gems2", $scoreInfo["gems"][2]);
	$query->bindValue(":gems5", $scoreInfo["gems"][5]);
	$query->bindValue(":gems10", $scoreInfo["gems"][10]);
	$query->bindValue(":sort", $sort);
	$query->bindValue(":time", $scoreInfo["time"]);

//Get their position on the scoreboards, BEFORE we send the score in (as that will
// corrupt this index). Also +1 since this is # of people better, not position.
	$position = $mission->getScorePlacement($scoreInfo) + 1;
	if ($position === 1) {
		//World record, flag it
		$modifiers |= Modifiers::WasWorldRecord;
	}

	$query->bindValue(":modifiers", $modifiers);

	if ($query->execute()) {
		//If it's a laps mission we should also store their lap time
		$lapTime = param("lapTime");
		if (stristr($mission->gamemode, "laps") !== false && $lapTime !== null) {
			$query = $db->prepare("
			INSERT INTO ex82r_user_lap_times SET
			`user_id` = :user_id,
			`mission_id` = :mission_id,
			`time` = :time
		");
			$query->bindValue(":user_id", $userId);
			$query->bindValue(":mission_id", $missionId);
			$query->bindValue(":time", $lapTime);
			$query->execute();

			//And we don't care about the rest
		}

		//Now we need to give them rating
		$ratingIncrease = $scoreInfo["rating"] - $lastBest["rating"];
		if ($ratingIncrease > 0) {
			$ratingColumn = $scoreInfo["rating_column"];
			//Increase both general and the column for this game
			$query = $db->prepare("UPDATE `ex82r_user_ratings`
			SET `rating_general` = `rating_general` + :increase,
			    $ratingColumn = $ratingColumn + :increase2
			WHERE `user_id` = :user_id
		");
			$query->bindValue(":increase", $ratingIncrease);
			$query->bindValue(":increase2", $ratingIncrease);
			$query->bindValue(":user_id", $userId);
			$query->execute();
		}

		//100% bonus for quota, if this is the only 100% time they have
		if (($scoreInfo["modifiers"] & Modifiers::QuotaHundred) &&
		    $user->getScoreCount($mission, Modifiers::QuotaHundred) == 1
		) {
			//They got the 100% in quota, give them a bonus
			$query = $db->prepare("UPDATE `ex82r_user_ratings`
			SET `rating_quota_bonus` = `rating_quota_bonus` + :bonus,
			    `rating_general` = `rating_general` + :bonus2
			WHERE `user_id` = :user_id");
			$query->bindValue(":bonus", $scoreInfo["quota_bonus"]);
			$query->bindValue(":bonus2", $scoreInfo["quota_bonus"]);
			$query->bindValue(":user_id", $userId);
			$query->execute();
		}
	} else {
		print_r($query->errorInfo());
	}
}
