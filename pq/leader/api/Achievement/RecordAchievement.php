<?php

define("PQ_RUN", true);
require_once("../../Framework.php");

//For grant()
require_once("UpdateAchievements.php");

Login::requireLogin();

$username = requireParam("username");
$user = User::get(JoomlaSupport::getUserId($username));

$achievement = requireParam("achievement");

//Make sure this achievement is manually awarded
$query = $db->prepare("SELECT `manual` FROM ex82r_achievement_names WHERE id = :id");
$query->bindValue(":id", $achievement);
requireExecute($query);

if ($query->rowCount() == 0) {
	error("NOACH");
}
$manual = $query->fetchColumn(0);
if (!$manual) {
	error("AUTOMATIC");
}

//Manual ach and we supposedly got it. So let's grant it
grant($user, $achievement);
techo("GRANTED");
