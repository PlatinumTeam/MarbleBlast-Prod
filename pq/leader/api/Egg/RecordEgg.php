<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

Login::requireLogin();

$user    = Login::getCurrentUser();
$time    = requireParam("time");
$mission = Mission::getByParams();

//Check if this is egg wr
$query = $db->prepare("
	SELECT MIN(`time`), COUNT(*) FROM (
	    SELECT MIN(`time`) AS time FROM ex82r_user_eggs
	    WHERE `mission_id` = :mission_id
	    GROUP BY user_id
	) AS bests
");
$query->bindValue(":mission_id", $mission->id);
$query->execute();
$row = $query->fetch(PDO::FETCH_NUM);

$showWR = false;
if ($row !== null) {
	[$min, $count] = $row;
	if ($time < $min) {
		//Yes it is
		techo("RECORDING");

		if ($db->getSetting("show_egg_wr_messages") && $count >= (int)$db->getSetting("wr_player_count") && !$mission->isDisabled() && $mission->gameInfo["game_type"] === "Single Player") {
			$showWR = true;
		}
	}
}

//Check if they already have an egg in the database
$query = $db->prepare("
	SELECT COUNT(*) FROM ex82r_user_eggs
	WHERE `user_id` = :user_id
    AND `mission_id` = :mission_id
");
$query->bindValue(":user_id", $user->id);
$query->bindValue(":mission_id", $mission->id);
$query->execute();

if ($query->fetchColumn(0) === 0) {
	//Give them egg rating
	$eggRating = $mission->ratingInfo["egg_rating"];
	if ($eggRating > 0) {
		$query = $db->prepare("
			UPDATE `ex82r_user_ratings`
			SET `rating_egg` = `rating_egg` + :rating,
			    `rating_general` = `rating_general` + :rating2
			WHERE `user_id` = :user_id
		");
		$query->bindValue(":rating", $eggRating);
		$query->bindValue(":rating2", $eggRating);
		$query->bindValue(":user_id", $user->id);

		requireExecute($query);
	}
}

//Insert new egg time
$query = $db->prepare("
	INSERT INTO ex82r_user_eggs
	SET `user_id` = :user_id,
	    `mission_id` = :mission_id,
	    `time` = :time
");
$query->bindValue(":user_id", $user->id);
$query->bindValue(":mission_id", $mission->id);
$query->bindValue(":time", $time);

requireExecute($query);

techo("SUCCESS");

if ($showWR) {
	$declarative = $mission->gameInfo["easter_egg_name"];
	// Blah blah blah blah ya ya ya ya
	if ($declarative[0] === 'E') {
		$declarative = "an $declarative";
	} else {
		$declarative = "a $declarative";
	}
	$message = "{$user->joomla["name"]} has just achieved {$declarative} record on \"{$mission->name}\" ({$mission->gameInfo["display"]} {$mission->difficultyInfo["display"]}) of " . formatTime(round($time), true);

	$info = Platinum\encodeName($mission->gameInfo["easter_egg_name"] . ": " . $mission->name) . " " . round($time);
	Platinum\postNotify("record", $user->joomla["username"], -1, $info);

	//Send it to discord
	$l = new DiscordLink("Bot XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX");
	$msgData = $l->sendMessage("346039779126935552", $message);
}

include("../Achievement/UpdateAchievements.php");
$achievements = updateAchievements($user);
foreach ($achievements as $achievement) {
	techo("ACHIEVEMENT " . $achievement["id"]);
}
