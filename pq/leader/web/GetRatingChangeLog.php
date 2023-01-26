<?php
define("PQ_RUN", true);
require_once("../Framework.php");

if (!Login::isPrivilege("pq.mod.editRatings")) {
	header("HTTP/1.1 401 Unauthorized");
	die();
}

$mission = requireParam("missionId");
$query = $db->prepare(
	"SELECT * FROM `ex82r_mission_change_log`
	WHERE `mission_id` = :mission_id
	ORDER BY `id` DESC");
$query->bindValue(":mission_id", $mission);
$query->execute();

$results = $query->fetchAll(PDO::FETCH_ASSOC);

echo(json_encode($results));
