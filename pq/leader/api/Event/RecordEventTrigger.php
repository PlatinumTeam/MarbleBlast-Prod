<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$username = requireParam("username");
$user = User::get(JoomlaSupport::getUserId($username));

$trigger = requireParam("trigger");

// If the player has seen the trigger, simply return 0.
// If the player has not seen the trigger, insert the value and return 1.

$query = $db->prepare("SELECT * FROM ex82r_user_event_triggers WHERE user_id = :user_id AND `trigger` = :trigger LIMIT 1");
$query->bindValue(":user_id", $user->id);
$query->bindValue(":trigger", $trigger);
requireExecute($query);

if ($query->rowCount() == 0) {
	//Don't have this one yet
	$query = $db->prepare("INSERT INTO ex82r_user_event_triggers SET user_id = :user_id, `trigger` = :trigger");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":trigger", $trigger);
	requireExecute($query);

	//Got it!
	techo("1");
} else {
	//We do have this, just return 0
	techo("0");
}

require_once("../Achievement/UpdateAchievements.php");
updateAchievements($user);
