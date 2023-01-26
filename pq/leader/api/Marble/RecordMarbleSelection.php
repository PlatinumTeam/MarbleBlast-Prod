<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

Login::requireLogin();

$user     = Login::getCurrentUser();
$marbleId = requireParam("marbleId");

$query = $db->prepare("
	INSERT INTO `ex82r_user_current_marble_selection`
	SET `user_id` = :user_id, `marble_id` = :marble_id
	ON DUPLICATE KEY UPDATE `marble_id` = :marble_id2
");
$query->bindValue(":user_id", $user->id);
$query->bindValue(":marble_id", $marbleId);
$query->bindValue(":marble_id2", $marbleId);
$query->execute();
