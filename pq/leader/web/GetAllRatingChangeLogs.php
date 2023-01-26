<?php
define("PQ_RUN", true);
require_once("../Framework.php");

if (!Login::isPrivilege("pq.mod.editRatings")) {
	header("HTTP/1.1 401 Unauthorized");
	die();
}

$query = $db->prepare(
	"SELECT * FROM `ex82r_mission_change_log` AS `changeLog`
	JOIN `ex82r_missions` AS `missionInfo` ON `changeLog`.`mission_id` = `missionInfo`.`id`
	ORDER BY `changeLog`.`id` DESC
	LIMIT 50");
$query->execute();

$results = fetchAllTableAssociative($query);

echo(json_encode($results));
