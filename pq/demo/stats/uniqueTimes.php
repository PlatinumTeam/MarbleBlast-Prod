	<?php
require("../Database.php");

$levelID = $_POST["levelID"];

// First, grab what kind of mission is this as score missions order differently than time ones.
$query = $db->prepare("SELECT `score_type` FROM `lw3qp_mission_scores` WHERE `missionid` = :id");
$query->bindParam(":id", $levelID);
$query->execute();
$scoreType = $query->fetchColumn(0);

// Next, grab level info sorted by the time/score
$orderBy = $scoreType === "time" ? "ASC" : "DESC";
$sortFn = $scoreType === "time" ? "MIN" : "MAX";
$query = $db->prepare("
	SELECT * FROM 
	(SELECT `userid`, {$sortFn}(`score`) AS `best`, COUNT(*) AS `count` FROM `lw3qp_mission_scores`
	JOIN `lw3qp_users` ON `lw3qp_users`.id=`lw3qp_mission_scores`.userid
	WHERE `missionid` = :id
	GROUP BY `userid`) AS `bests`
	JOIN lw3qp_mission_scores
	ON bests.userid = lw3qp_mission_scores.userid
	AND bests.best = lw3qp_mission_scores.score
	JOIN lw3qp_users ON bests.userid = lw3qp_users.id
	ORDER BY score {$orderBy}
");
$query->bindParam(":id", $levelID);
if (!$query->execute()) {
	print_r($query->errorInfo());
}

if ($scoreType === "score") {
	$cols = ["Place", "User", "Time", "Time Bonus", "Completions", "Gem Count", "1 Point Gems", "2 Point Gems", "5 Point Gems", "10 Point Gems"];
} else {
	$cols = ["Place", "User", "Score", "Time Bonus", "Completions", "Gem Count"];
}

$result = ["cols" => $cols, "scores" => []];

$place = 0;
while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	$place ++;
	$user = $row["uid"];
	$score = ($scoreType === "time") ? formatTime($row["score"]) : $row["score"];
	$timeBonus = formatTime($row["total_bonus"], true);
	$gemCount = $row["gem_count"];
	$count = $row["count"];

	if ($scoreType === "score") {
		$gem1  = $row["gems_1_point"];
		$gem2  = $row["gems_2_point"];
		$gem5  = $row["gems_5_point"];
		$gem10 = $row["gems_10_point"];
		$result["scores"][] = [$place, $user, $score, $timeBonus, $count, $gemCount, $gem1, $gem2, $gem5, $gem10];
	} else {
		$result["scores"][] = [$place, $user, $score, $timeBonus, $count, $gemCount];
	}
}

echo(json_encode($result));
