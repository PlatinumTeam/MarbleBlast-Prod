<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

Login::requireLogin();

$mission = Mission::getByParams(true, false);
$rating = requireParam("rating");

$username = requireParam("username");
$user = User::get(JoomlaSupport::getUserId($username));

//See if they have already rated this mission
$query = $db->prepare("SELECT `rating` FROM ex82r_user_mission_ratings WHERE mission_id = :mission_id AND user_id = :user_id");
$query->bindValue(":mission_id", $mission->id);
$query->bindValue(":user_id", $user->id);
requireExecute($query);

if ($query->rowCount() == 0) {
	//They have no current rating, insert a new one
	$query = $db->prepare("INSERT INTO ex82r_user_mission_ratings SET mission_id = :mission_id, user_id = :user_id, rating = :rating");
	$query->bindValue(":mission_id", $mission->id);
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":rating", $rating);
	requireExecute($query);
} else {
	//They have a rating, update it
	$query = $db->prepare("UPDATE ex82r_user_mission_ratings SET rating = :rating WHERE mission_id = :mission_id AND user_id = :user_id");
	$query->bindValue(":rating", $rating);
	$query->bindValue(":mission_id", $mission->id);
	$query->bindValue(":user_id", $user->id);
	requireExecute($query);
}

//So the game knows we succeeded
techo("SUCCESS");
