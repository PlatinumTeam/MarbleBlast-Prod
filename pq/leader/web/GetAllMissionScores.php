<?php
define("PQ_RUN", true);
require_once("../Framework.php");

$mission = Mission::getByParams(true, false);
if ($mission === null) {
	error("Need mission");
}

$modifiers = param("modifiers") ?? 0;

$query = $db->prepare("
	SELECT ex82r_user_scores.*, bv2xj_users.username FROM ex82r_user_scores
	JOIN prod_joomla.bv2xj_users ON ex82r_user_scores.user_id = bv2xj_users.id
	WHERE mission_id = :id
	AND modifiers & :modifiers = :modifiers2
	ORDER BY sort ASC
");
$query->bindValue(":id", $mission->id);
$query->bindValue(":modifiers", $modifiers);
$query->bindValue(":modifiers2", $modifiers);
$query->execute();

techo(json_encode($query->fetchAll(PDO::FETCH_ASSOC)));
