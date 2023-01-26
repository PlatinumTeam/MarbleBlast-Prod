<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$mission = Mission::getByParams(true, false);
if ($mission === null) {
	error("Need mission");
}

$modifiers = param("modifiers") ?? 0;

$query = $db->prepare("
	SELECT
	       ex82r_user_scores.id,
	       `user_id`,
	       `username`,
	       SANITIZE_NAME(`name`) AS `name`,
	       `score`,
	       `score_type`,
	       `modifiers`,
	       `total_bonus`,
	       `gem_count`,
	       `rating`,
	       `gems_1_point`,
	       `gems_2_point`,
	       `gems_5_point`,
	       `gems_10_point`,
	       `origin`,
	       `timestamp`
	FROM ex82r_user_scores
	JOIN prod_joomla.bv2xj_users ON ex82r_user_scores.user_id = bv2xj_users.id
	WHERE mission_id = :id
	AND modifiers & :modifiers = :modifiers2
	AND block = 0
	ORDER BY sort ASC
");
$query->bindValue(":id", $mission->id);
$query->bindValue(":modifiers", $modifiers);
$query->bindValue(":modifiers2", $modifiers);
$query->execute();

$scores = $query->fetchAll(PDO::FETCH_ASSOC);

for ($i = 0; $i < count($scores); $i ++) {
	$scores[$i]["placement"] = $i + 1;
}

$results = [
	"columns"   => [
	],
	"missionId" => $mission->id
];
$results["scores"] = $scores;

techo(json_encode($results));
