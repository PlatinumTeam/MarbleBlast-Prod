<?php
require_once("Database.php");

$levelID = param("levelID");

//If they try to use a basename instead
if ($levelID === null) {
	$basename = param("levelBasename");
	if ($basename === null) {
		error("ARGUMENT levelID or levelBasename");
	}

	$mission = getMissionInfoByBasename($basename);
	$levelID = $mission["id"];
} else {
	$mission = getMissionInfoById($levelID);
	$basename = $mission["basename"];
}

//Get all times
$query = $db->prepare(
"SELECT `bests`.`userid`, `best`, `score_type`, `total_bonus`, `gem_count` FROM
-- Best for each user
  (SELECT `userid`, MIN(`score`) AS `best`
      FROM `lw3qp_mission_scores`
      WHERE `missionid` = :id
      AND `score_type` = 'time'
      GROUP BY `userid`
  ) AS `bests`
-- Combine with the scores table to get the rest of the info
  JOIN `lw3qp_mission_scores`
    ON `lw3qp_mission_scores`.`userid` = `bests`.`userid`
    AND `lw3qp_mission_scores`.`score` = `bests`.`best`
AND `missionid` = :id 
ORDER BY `best` ASC");

$query->bindParam(":id", $levelID);
$query->execute();
$times = $query->fetchAll(PDO::FETCH_ASSOC);

//Get all scores
$query = $db->prepare(
"SELECT `bests`.`userid`, `best`, `score_type`, `total_bonus`, `gem_count` FROM
-- Best for each user
  (SELECT `userid`, MAX(`score`) AS `best`
      FROM `lw3qp_mission_scores`
      WHERE `missionid` = :id
      AND `score_type` = 'score'
      GROUP BY `userid`
  ) AS `bests`
-- Combine with the scores table to get the rest of the info
  JOIN `lw3qp_mission_scores`
    ON `lw3qp_mission_scores`.`userid` = `bests`.`userid`
    AND `lw3qp_mission_scores`.`score` = `bests`.`best`
AND `missionid` = :id 
ORDER BY `best` DESC");

$query->bindParam(":id", $levelID);
$query->execute();
$scores = $query->fetchAll(PDO::FETCH_ASSOC);

//Remove scores where a time exists
$scores = array_filter($scores, function($row) use($times) {
	foreach ($times as $timeRow) {
		if ($timeRow["userid"] === $row["userid"])
			return false;
	}
	return true;
});

$results = ["columns" => [
	["name" => "userid", "display" => "Player", "type" => "string", "tab" => "40"],
    ["name" => "best", "display" => "Score", "tab" => "260"],
    ["name" => "total_bonus", "display" => "Time Travel", "type" => "time", "tab" => "345"],
    ["name" => "gem_count", "display" => "Total Gems", "type" => "score", "tab" => "430"]
], "basename" => $basename];
//And combine them
$results["scores"] = array_merge($times, $scores);

techo(json_encode($results));
