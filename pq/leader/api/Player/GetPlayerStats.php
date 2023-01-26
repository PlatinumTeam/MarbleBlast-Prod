<?php

define("PQ_RUN", true);
require_once("../../Framework.php");

/*
 * Stats list:
 * MBP:
 * * Beginner
 * * Intermediate
 * * Advanced
 * * Expert
 * * Total
 * * Platinum Times
 * * Ultimate Times
 * * Easter Eggs
 * * Total Rating
 * * Overall Rank
 * MBG:
 * * Beginner
 * * Intermediate
 * * Advanced
 * * Total
 * * Gold Times
 * * Total Rating
 * * Overall Rank
 * MBU:
 * * Beginner
 * * Intermediate
 * * Advanced
 * * Total
 * * Gold Times
 * * Ultimate Times
 * * Easter Eggs
 * * Total Rating
 * * Overall Rank
 * PQ:
 * * Tutorial
 * * Beginner
 * * Intermediate
 * * Advanced
 * * Expert
 * * Bonus
 * * Total
 * * Platinum Times
 * * Ultimate Times
 * * Awesome Times
 * * Nest Eggs
 * * Total Rating
 * * Overall Rank
 * Custom:
 * * Every category
 * * Total
 * * Platinum/Gold Times
 * * Ultimate Times
 * * Easter Eggs
 * * Total Rating
 * * Overall Rank
 * General:
 * * General Rating
 * * General Rank
 * * Total Time
 * * All Scores Time
 * * # of World Records ?
 */

Login::requireLogin();
//User info
$username = param("user") ?? Login::getCurrentUsername();
$userId = JoomlaSupport::getUserId($username);
$user = User::get($userId);

if (Login::isLoggedIn()) {
	$override = Login::isPrivilege("pq.test.missionList");
} else {
	$override = 0;
}

$stats = [
    "id" => $userId,
	"username" => $username,
	"display" => $user->getDisplayName(),
    "games" => [],
    "gameIds" => []
];

//Get stats for every game
$query = $db->prepare("
	SELECT * FROM `ex82r_mission_games` WHERE `game_type` = 'Single Player'
	AND (`disabled` = 0 OR :override = 1)
");
$query->bindValue(":override", $override);
$query->execute();
$games = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($games as $game) {
	if ($game["rating_column"] === null) {
		continue;
	}

	$info = [
		"name" => $game["name"],
		"display" => $game["long_display"],
		"difficulties" => [],
		"rating" => $user->getRating($game["rating_column"]),
		"rank" => getRank($user, $game["rating_column"]),
		"has_platinum_times" => $game["has_platinum_times"],
		"has_ultimate_times" => $game["has_ultimate_times"],
		"has_awesome_times" => $game["has_awesome_times"],
		"has_easter_eggs" => $game["has_easter_eggs"],
		"platinum_time_name" => $game["platinum_time_name"],
		"ultimate_time_name" => $game["ultimate_time_name"],
		"awesome_time_name" => $game["awesome_time_name"],
		"easter_egg_name" => $game["easter_egg_name"],
	];

	//Get total level count for this game
	$query = $db->prepare("
		SELECT
			COUNT(*) AS `total_missions`,
			SUM(CASE WHEN has_egg THEN 1 ELSE 0 END) AS `total_eggs`,
			SUM(CASE WHEN platinum_time != 0 OR platinum_score != 0 THEN 1 ELSE 0 END) AS `total_platinums`,
			SUM(CASE WHEN ultimate_time != 0 OR ultimate_score != 0 THEN 1 ELSE 0 END) AS `total_ultimates`,
			SUM(CASE WHEN awesome_time != 0 OR awesome_score != 0 THEN 1 ELSE 0 END) AS `total_awesomes`
		FROM ex82r_missions mission
		JOIN ex82r_mission_rating_info info ON mission.id = info.mission_id
		JOIN ex82r_mission_difficulties difficulty on mission.difficulty_id = difficulty.id
		WHERE mission.game_id = :game_id
		AND ((info.disabled = 0 AND difficulty.disabled = 0) OR :override = 1)
        AND normally_hidden = 0
	");
	$query->bindValue(":game_id", $game["id"]);
	$query->bindValue(":override", $override);
	$query->execute();
	$totals = $query->fetch(PDO::FETCH_ASSOC);
	$info["totals"] = $totals;

	//Get total best times and completions for this game
	$query = $db->prepare("
		SELECT
			-- Count of all challenge scores/times except PHP knows what the bit flags are
		    SUM(CASE WHEN `modifiers` & :mod_platinum != 0 THEN 1 ELSE 0 END) AS `platinum_count`,
		    SUM(CASE WHEN `modifiers` & :mod_ultimate != 0 THEN 1 ELSE 0 END) AS `ultimate_count`,
		    SUM(CASE WHEN `modifiers` & :mod_awesome  != 0 THEN 1 ELSE 0 END) AS `awesome_count`,
		    -- Total level completion
		    COUNT(*) AS `completion`,
		    -- Total time of all levels
		    SUM(IF(score_type = 'time', score, par_time)) AS `total_time`
		FROM (
			-- Need to get first score id with this score as otherwise this will return
			-- 2 rows if someone gets the same time twice.
		    SELECT
		        `bests`.`mission_id`, MIN(ex82r_user_scores.`id`) AS `first`
		    FROM (
		        -- Select all scores
		        SELECT ex82r_user_scores.`mission_id`, MIN(`sort`) AS `minSort`
		        FROM ex82r_user_scores
		        JOIN ex82r_missions mission ON ex82r_user_scores.mission_id = mission.id
		        JOIN ex82r_mission_rating_info info ON mission.id = info.mission_id
		        JOIN ex82r_mission_difficulties difficulty on mission.difficulty_id = difficulty.id
		        WHERE `user_id` = :user_id
		        AND mission.game_id = :game_id
				AND ((info.disabled = 0 AND difficulty.disabled = 0) OR :override = 1)
                AND info.normally_hidden = 0
		        GROUP BY `mission_id`
		    ) AS `bests`
		    -- Join the scores table so we can get the id of the score
		    JOIN ex82r_user_scores
		      ON ex82r_user_scores.`mission_id` = `bests`.`mission_id`
		     AND ex82r_user_scores.`sort` = `bests`.`minSort`
		    GROUP BY `mission_id`
		) AS `uniques`
		-- Join the scores table again so we can get score info
		JOIN ex82r_user_scores ON ex82r_user_scores.`id` = `first`
		JOIN `ex82r_missions` ON `uniques`.`mission_id` = `ex82r_missions`.`id`
        JOIN `ex82r_mission_rating_info` ON `ex82r_missions`.`id` = `ex82r_mission_rating_info`.`mission_id`
	");
	$query->bindValue(":override", $override);
	$query->bindValue(":game_id", $game["id"]);
	$query->bindValue(":user_id", $user->id);
	//Specific challenges
	$query->bindValue(":mod_platinum", Modifiers::BeatPlatinumTime | Modifiers::BeatPlatinumScore);
	$query->bindValue(":mod_ultimate", Modifiers::BeatUltimateTime | Modifiers::BeatUltimateScore);
	$query->bindValue(":mod_awesome",  Modifiers::BeatAwesomeTime  | Modifiers::BeatAwesomeScore);
	$query->execute();
	$info["completion"] = $query->fetch(PDO::FETCH_ASSOC);

	//Get easter egg counts
	$query = $db->prepare("
		SELECT COUNT(*) FROM (
		    SELECT eggs.mission_id FROM ex82r_user_eggs eggs
		    JOIN ex82r_missions missions ON eggs.mission_id = missions.id
		    JOIN ex82r_mission_difficulties difficulty on missions.difficulty_id = difficulty.id
		    JOIN ex82r_mission_games game ON missions.game_id = game.id
		    JOIN ex82r_mission_rating_info info ON missions.id = info.mission_id
		    WHERE eggs.user_id = :user_id
		      AND info.has_egg
		      AND missions.game_id = :game_id
              AND info.normally_hidden = 0
		      AND ((info.disabled = 0
		      AND difficulty.disabled = 0
		      AND game.disabled = 0) OR :override = 1)
		    GROUP BY eggs.mission_id
		) AS egg_missions
	");
	$query->bindValue(":game_id", $game["id"]);
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":override", $override);
	$query->execute();
	$info["completion"]["egg_count"] = $query->fetchColumn(0);

	$stats["games"][$game["id"]] = $info;
	$stats["gameIds"][$game["name"]] = $game["id"];
}

//Get per-difficulty stats
$query = $db->prepare("
	SELECT `ex82r_mission_difficulties`.* FROM `ex82r_mission_difficulties`
	JOIN `ex82r_mission_games` ON `ex82r_mission_difficulties`.`game_id` = `ex82r_mission_games`.`id`
	WHERE `game_type` = 'Single Player'
	  AND ((ex82r_mission_games.disabled = 0 AND ex82r_mission_difficulties.disabled = 0) OR :override = 1)
");
$query->bindValue(":override", $override);
$query->execute();
$difficulties = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($difficulties as $difficulty) {
	$info = [
		"name" => $difficulty["name"],
		"display" => $difficulty["display"]
	];

	//Get total level count for this difficulty
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_missions mission
        JOIN `ex82r_mission_rating_info` info ON mission.`id` = info.`mission_id`
		JOIN ex82r_mission_difficulties difficulty on mission.difficulty_id = difficulty.id
		WHERE difficulty_id = :difficulty_id
		AND ((info.disabled = 0 AND difficulty.disabled = 0) OR :override = 1)
        AND normally_hidden = 0
	");
	$query->bindValue(":override", $override);
	$query->bindValue(":difficulty_id", $difficulty["id"]);
	$query->execute();
	$info["total_missions"] = $query->fetchColumn(0);

	//Get total best times for this difficulty
	$query = $db->prepare("
        SELECT
          -- Total level completion
          COUNT(*) AS `completion`,
          -- Hack- use the sort field to tell if the score is a time
          SUM(IF(uniques.minSort < 9000000, uniques.minSort, info.par_time)) AS `total_time`
        FROM (
            -- Select all scores
            SELECT DISTINCT `mission_id`, `user_id`, MIN(`sort`) AS `minSort`
            FROM ex82r_user_scores
            JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
            WHERE user_id = :user_id
            AND difficulty_id = :difficulty_id
            GROUP BY `mission_id`, `user_id`
          ) AS `uniques`
          -- Join the scores table again so we can get score info
          JOIN ex82r_missions mission ON mission.id = uniques.mission_id
          JOIN ex82r_mission_rating_info info ON mission.id = info.mission_id
        WHERE (disabled = 0 OR :override = 1)
        AND normally_hidden = 0
    ");
	$query->bindValue(":override", $override);
	$query->bindValue(":difficulty_id", $difficulty["id"]);
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	$completion = $query->fetch(PDO::FETCH_ASSOC);
	$info["completion"] = $completion["completion"];
	$info["total_time"] = $completion["total_time"];

	$stats["games"][$difficulty["game_id"]]["difficulties"][] = $info;
}

//General statistics
$general = [
	"rating" => $user->getRating("rating_general"),
    "rank" => getRank($user, "rating_general")
];
//Total time of all scores
$query = $db->prepare("
	SELECT SUM(IF(score_type = 'time', `score`, ex82r_mission_rating_info.par_time)) FROM (
	    SELECT DISTINCT `bests`.`mission_id`, `score`, `score_type`
	    FROM (
	    -- Select all time scores
	        SELECT ex82r_user_scores.`mission_id`, MIN(`sort`) AS `minSort`
	        FROM ex82r_user_scores
	        JOIN `ex82r_mission_rating_info` ON ex82r_user_scores.`mission_id` = `ex82r_mission_rating_info`.`mission_id`
	        WHERE `user_id` = :user_id
	        AND ex82r_mission_rating_info.disabled = 0
	        GROUP BY `mission_id`
	    ) AS `bests`
	    -- Join the scores table so we can get other info
	    JOIN ex82r_user_scores
	      ON ex82r_user_scores.`mission_id` = `bests`.`mission_id`
	     AND ex82r_user_scores.`sort` = `bests`.`minSort`
	) AS `uniques`
	JOIN ex82r_mission_rating_info
	ON ex82r_mission_rating_info.mission_id = uniques.mission_id
");
$query->bindValue(":user_id", $user->id);
$query->execute();
$general["total_time"] = $query->fetchColumn(0);

//All scores time
$query = $db->prepare("
SELECT
   (SELECT SUM(`score`) AS times_total
      FROM ex82r_user_scores
      JOIN `ex82r_mission_rating_info` ON ex82r_user_scores.`mission_id` = `ex82r_mission_rating_info`.`mission_id`
     WHERE `user_id` = :user_id
       AND `score_type` = 'time'
       AND ex82r_mission_rating_info.disabled = 0
   )
   +
   (SELECT SUM(par_time) AS scores_total
      FROM ex82r_user_scores
      JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
      JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
     WHERE user_id = :user_id2
       AND score_type = 'score'
   )
AS `total`
");
$query->bindValue(":user_id", $user->id);
$query->bindValue(":user_id2", $user->id);
$query->execute();
$general["grand_total"] = $query->fetchColumn(0);

//All scores time
$query = $db->prepare("
SELECT SUM(total_bonus)
  FROM ex82r_user_scores
 WHERE user_id = :user_id
 AND total_bonus IS NOT NULL
");
$query->bindValue(":user_id", $user->id);
$query->execute();
$general["total_bonus"] = $query->fetchColumn(0);

$stats["general"] = $general;

techo(json_encode($stats));

/**
 * @param User   $user
 * @param string $column
 * @return string
 */
function getRank(User $user, $column) {
	global $db;
	$query = $db->prepare("
		SELECT COUNT(*) FROM `ex82r_user_ratings`
		JOIN prod_joomla.bv2xj_users ON prod_joomla.bv2xj_users.id = ex82r_user_ratings.user_id
		WHERE `$column` > :rating
		AND block = 0
	");
	$query->bindValue(":rating", $user->ratings[$column]);
	$query->execute();

	//Since 0th place == #1
	return intval($query->fetchColumn(0)) + 1;
}

/**
 * Converts a date time timestamp into a human readable format. Uses years months and days.
 * eg. '1 year, 6 months, 24 days'
 * Src adapted from: http://stackoverflow.com/questions/2915864/php-how-to-find-the-time-elapsed-since-a-date-time
 * @param int $time An object containing the timestamp to convert
 * @return string
 */
function convertTimeReadablePrecise($time) {
	$startdate = new DateTime($time);
	$endDate   = new DateTime('now');
	$interval  = $endDate->diff($startdate);
	$days      = $interval->format('%d');
	$months    = $interval->format('%m');
	$years     = $interval->format('%y');

	$str = "";
	if ($years > 0) {
		$str = "$years year";
	}
	if ($years > 1) {
		$str .= "s";
	}

	if ($months > 0) {
		if ($str != "") {
			$str .= ", ";
		}
		$str .= "$months month";
	}
	if ($months > 1) {
		$str .= "s";
	}

	if ($days > 0) {
		if ($str != "") {
			$str .= ", ";
		}
		$str .= "$days day";
	}
	if ($days > 1) {
		$str .= "s";
	}

	return $str;
}


