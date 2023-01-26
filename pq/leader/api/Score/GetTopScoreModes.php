<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$mission = Mission::getByParams(true, false);
if ($mission === null) {
	error("Need mission");
}

$results = [
    "missionId" => $mission->id,
];

$ddScores = $mission->getTopScores(Modifiers::DoubleDiamond);
$ddColumns = [
    ["name" => "placement", "display" => "#", "type" => "place", "tab" => "1", "width" => "40"],
    ["name" => "name", "display" => "Player", "type" => "string", "tab" => "40", "width" => "-75"],
    ["name" => "score", "display" => "Time", "type" => "time", "tab" => "-75", "width" => "75"]
];
$ddResults = [
	"columns"   => $ddColumns,
	"missionId" => $mission->id,
    "scores" => $ddScores
];
$results["dd"] = $ddResults;

$q100Scores = $mission->getTopScores(Modifiers::QuotaHundred);
$q100Columns = [
    ["name" => "placement", "display" => "#", "type" => "place", "tab" => "1", "width" => "40"],
    ["name" => "name", "display" => "Player", "type" => "string", "tab" => "40", "width" => "-75"],
    ["name" => "score", "display" => "Time", "type" => "time", "tab" => "-75", "width" => "75"]
];
$q100Results = [
    "columns"   => $q100Columns,
    "missionId" => $mission->id,
    "scores" => $q100Scores
];
$results["quota100"] = $q100Results;

$query = $db->prepare("
    SELECT `uniques`.`user_id`, `username`, SANITIZE_NAME(`name`) AS `name`, `time` FROM
      -- Get min of id of the scores so it doesn't show duplicates
      (SELECT `bests`.`user_id`, MIN(ex82r_user_lap_times.id) AS id FROM
        -- Best for each user
        (SELECT `user_id`, MIN(`time`) AS `minTime`
         FROM ex82r_user_lap_times
           JOIN prod_joomla.bv2xj_users ON ex82r_user_lap_times.user_id = prod_joomla.bv2xj_users.id
         WHERE `mission_id` = :id
               AND `block` = 0
         GROUP BY `user_id`
        ) AS `bests`
        -- Combine with the scores table to get the rest of the info
        JOIN ex82r_user_lap_times
          ON ex82r_user_lap_times.`user_id` = `bests`.`user_id`
             AND ex82r_user_lap_times.`time` = `bests`.`minTime`
      WHERE `mission_id` = :id2
      GROUP BY `bests`.`user_id`, `time`
      ) AS `uniques`
      -- Join back with scores for extra info
      JOIN ex82r_user_lap_times ON uniques.id = ex82r_user_lap_times.id
      -- And get some user info
      JOIN `prod_joomla`.`bv2xj_users`
        ON `uniques`.`user_id` = `prod_joomla`.`bv2xj_users`.`id`
    ORDER BY `time` ASC, ex82r_user_lap_times.id ASC
");
$query->bindValue(":id", $mission->id);
$query->bindValue(":id2", $mission->id);
$query->execute();
$lapScores = $query->fetchAll(PDO::FETCH_ASSOC);

for ($i = 0; $i < count($lapScores); $i ++) {
	if ($i > 0 && $lapScores[$i]["time"] === $lapScores[$i - 1]["time"]) {
		$lapScores[$i]["placement"] = $lapScores[$i - 1]["placement"];
	} else {
		$lapScores[$i]["placement"] = $i + 1;
	}
}

$lapColumns = [
    ["name" => "placement", "display" => "#", "type" => "place", "tab" => "1", "width" => "40"],
    ["name" => "name", "display" => "Player", "type" => "string", "tab" => "40", "width" => "-75"],
    ["name" => "time", "display" => "Time", "type" => "time", "tab" => "-75", "width" => "75"]
];
$lapResults = [
    "columns"   => $lapColumns,
    "missionId" => $mission->id,
    "scores" => $lapScores
];
$results["lap"] = $lapResults;

$query = $db->prepare("
	SELECT users.username AS username, SANITIZE_NAME(users.name) AS name, eggs.time AS time
	FROM ex82r_user_eggs eggs
	JOIN (
	    SELECT MIN(id) AS id
	    FROM ex82r_user_eggs eggs
	    JOIN (
	        SELECT user_id, MIN(time) AS time
	        FROM ex82r_user_eggs eggs
	        WHERE mission_id = :id
	          AND time < 5999999
	        GROUP BY user_id
	    ) AS min_times
	    ON min_times.user_id = eggs.user_id
	    AND min_times.time = eggs.time
	    AND eggs.mission_id = :id2
	    GROUP BY min_times.user_id
	) AS bests
	ON bests.id = eggs.id
	JOIN prod_joomla.bv2xj_users users ON eggs.user_id = users.id
	WHERE block = 0
	ORDER BY eggs.time ASC, eggs.id ASC
");
$query->bindValue(":id", $mission->id);
$query->bindValue(":id2", $mission->id);
$query->execute();
$eggScores = $query->fetchAll(PDO::FETCH_ASSOC);

for ($i = 0; $i < count($eggScores); $i ++) {
	if ($i > 0 && $eggScores[$i]["time"] === $eggScores[$i - 1]["time"]) {
		$eggScores[$i]["placement"] = $eggScores[$i - 1]["placement"];
	} else {
		$eggScores[$i]["placement"] = $i + 1;
	}
}

$eggColumns = [
    ["name" => "placement", "display" => "#", "type" => "place", "tab" => "1", "width" => "40"],
    ["name" => "name", "display" => "Player", "type" => "string", "tab" => "40", "width" => "-75"],
    ["name" => "time", "display" => "Time", "type" => "time", "tab" => "-75", "width" => "75"]
];
$eggResults = [
    "columns"   => $eggColumns,
    "missionId" => $mission->id,
    "scores" => $eggScores
];
$results["egg"] = $eggResults;

techo(json_encode($results));
