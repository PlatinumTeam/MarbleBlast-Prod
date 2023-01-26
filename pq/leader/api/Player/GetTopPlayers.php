<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$query = $db->prepare("SELECT DISTINCT `rating_column` FROM `ex82r_mission_games` WHERE `rating_column` IS NOT NULL AND disabled = 0");
$query->execute();
$games = $query->fetchAll(PDO::FETCH_ASSOC);

//So we get general ratings too
$games[] = ["rating_column" => "rating_general"];

$users = [];

foreach ($games as $gameRow) {
	$column = $gameRow["rating_column"];

	if ($column === null) {
		continue;
	}

	$users[$column]["display"] = [];
	$users[$column]["rating"] = [];
	$users[$column]["username"] = [];

	$query = $db->prepare("
		SELECT CONVERT(`bv2xj_users`.`name` USING UTF8) AS `display`, `$column` AS `rating`, `bv2xj_users`.`username` FROM `ex82r_user_ratings`
		JOIN `prod_joomla`.`bv2xj_users`
		  ON `ex82r_user_ratings`.`user_id` = `prod_joomla`.`bv2xj_users`.`id`
		WHERE `$column` > 0
		AND block = 0
		ORDER BY `$column` DESC
	");
	requireExecute($query);
	while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
		$users[$column]["display"][] = sanitizeDisplayName($row["display"]);
		$users[$column]["rating"][] = $row["rating"];
		$users[$column]["username"][] = $row["username"];
	}
}

techo(json_encode($users));
