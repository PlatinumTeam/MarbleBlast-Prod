<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

Login::requireLogin();

$user = Login::getCurrentUser();

$query = $db->prepare("
	SELECT `mission_id`, MIN(`time`) AS `time`
	FROM ex82r_user_eggs
	WHERE `user_id` = :user_id
	GROUP BY `mission_id`
");
$query->bindValue(":user_id", $user->id);
$query->execute();

$times = $query->fetchAll(PDO::FETCH_ASSOC);

//Make it associative
$results = [];
foreach ($times as $time) {
	$results[$time["mission_id"]] = $time["time"];
}
techo(json_encode($results));
