<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

Login::requireLogin();
$user = Login::getCurrentUser();

$query = $db->prepare("
	SELECT DISTINCT `bests`.`mission_id`, `score`, `score_type` FROM
	-- Select all time scores
	(
		SELECT `mission_id`, MIN(`sort`) AS `minSort`
		FROM ex82r_user_scores
		WHERE `user_id` = :user_id
		GROUP BY `mission_id`
	) AS `bests`
	-- Join the scores table so we can get other info
	JOIN ex82r_user_scores
	  ON ex82r_user_scores.`mission_id` = `bests`.`mission_id`
	  AND ex82r_user_scores.`sort` = `bests`.`minSort`
");
$query->bindValue(":user_id", $user->id);
$query->execute();
$scores = $query->fetchAll(PDO::FETCH_ASSOC);

//Make it associative
$result = [];
foreach ($scores as $score) {
	$result[$score["mission_id"]] = $score;
}

//Get quota 100s too
$query = $db->prepare("
	SELECT DISTINCT `bests`.`mission_id`, `score`, `score_type` FROM
	  -- Select all time scores
	  (
	    SELECT `mission_id`, MIN(`sort`) AS `minSort`
	    FROM ex82r_user_scores
	    WHERE `user_id` = :user_id
	    -- That are quota 100s
	    AND `modifiers` & :modifiers = :modifiers2
	    GROUP BY `mission_id`
	  ) AS `bests`
	  -- Join the scores table so we can get other info
	  JOIN ex82r_user_scores
	    ON ex82r_user_scores.`mission_id` = `bests`.`mission_id`
	       AND ex82r_user_scores.`sort` = `bests`.`minSort`
");
$query->bindValue(":user_id", $user->id);
$query->bindValue(":modifiers", Modifiers::QuotaHundred);
$query->bindValue(":modifiers2", Modifiers::QuotaHundred);
$query->execute();
$quota100 = $query->fetchAll(PDO::FETCH_ASSOC);

//Make it associative
$result["quota100"] = [];
foreach ($quota100 as $score) {
	$result["quota100"][$score["mission_id"]] = $score;
}

//And best lap times
$query = $db->prepare("
	SELECT DISTINCT `bests`.`mission_id`, `time` FROM
	  -- Select all time scores
	  (
	    SELECT `mission_id`, MIN(`time`) AS `minTime`
	    FROM ex82r_user_lap_times
	    WHERE `user_id` = :user_id
	    GROUP BY `mission_id`
	  ) AS `bests`
	  -- Join the scores table so we can get other info
	  JOIN ex82r_user_lap_times
	    ON ex82r_user_lap_times.`mission_id` = `bests`.`mission_id`
	   AND ex82r_user_lap_times.`time` = `bests`.`minTime`
");
$query->bindValue(":user_id", $user->id);
$query->execute();
$lapTimes = $query->fetchAll(PDO::FETCH_ASSOC);

//Make it associative
$result["lapTime"] = [];
foreach ($lapTimes as $score) {
	$result["lapTime"][$score["mission_id"]] = $score;
}

//World record scores
$query = $db->prepare("
	SELECT
	    user_scores.mission_id
	FROM 
	(
	    SELECT 
	        MIN(inner_scores.id) AS score
	    FROM
	    (
	        SELECT
	            scores.mission_id,
	            MIN(scores.sort) AS score
	        FROM ex82r_user_scores scores
	        WHERE scores.disabled = 0
	        GROUP BY scores.mission_id
	    ) AS tops
	    LEFT JOIN ex82r_user_scores inner_scores ON 
	        inner_scores.mission_id = tops.mission_id AND 
	        inner_scores.sort = tops.score
	    INNER JOIN ex82r_missions miss ON
	            miss.id = inner_scores.mission_id AND
	            miss.is_custom = 0
	    INNER JOIN ex82r_mission_games games ON 
	            games.id = miss.game_id AND 
	            games.game_type = 'Single Player'
        AND inner_scores.user_id = :user_id
	    GROUP BY inner_scores.mission_id
	) derived
	INNER JOIN ex82r_user_scores user_scores ON 
	    user_scores.id = derived.score AND 
	    user_scores.user_id = :user_id2
");
$query->bindValue(":user_id", $user->id);
$query->bindValue(":user_id2", $user->id);
$query->execute();
$records = $query->fetchAll(PDO::FETCH_COLUMN);

//Make it associative
$result["record"] = $records;

techo(json_encode($result));
