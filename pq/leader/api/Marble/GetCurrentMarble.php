<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

//Super simple
$user = Login::getCurrentUser();

$query = $db->prepare("
	SELECT `marble_id`, `category_id` FROM `ex82r_user_current_marble_selection`
	JOIN `ex82r_marbles` ON `ex82r_user_current_marble_selection`.`marble_id` = `ex82r_marbles`.`id`
	WHERE `user_id` = :user_id
");
$query->bindValue(":user_id", $user->id);
$query->execute();

$info = $query->fetch(PDO::FETCH_ASSOC);

techo("{$info["category_id"]} {$info["marble_id"]}");
