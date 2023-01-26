<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

Login::requireLogin();

$score     = requireParam("score");
$scoreType = requireParam("scoreType");
$mission   = Mission::getByParams();
if ($mission === null) {
	error("Need mission");
}

$totalBonus = param("totalBonus");
$gemCount   = param("gemCount");
$modifiers  = param("modifiers") ?? 0;
$marbleId   = param("marbleId");
$extraModes = param("extraModes");

$gems     = [];
$gems[1]  = param("gems1") ?? 0;
$gems[2]  = param("gems2") ?? 0;
$gems[5]  = param("gems5") ?? 0;
$gems[10] = param("gems10") ?? 0;

//if ($totalBonus === null || $gemCount === null || $modifiers === null || $marbleId === null || $extraModes === null || $gems[1] === null || $gems[2] === null || $gems[5] === null || $gems[10] === null) {
//
//}

//Pls!
if ((int)(param("missionId")) === 834 && $scoreType === "time") {
	error("Stop");
}

$user = Login::getCurrentUser();
$scoreInfo = [
	"score" => $score,
	"type" => $scoreType,
    "modifiers" => $modifiers,
    "gems" => $gems,
    "gemCount" => $gemCount
];

$rating = SPRatings::getScoreRating($scoreInfo, $mission);
//Use any modifiers that the score rater gives us
$modifiers = $scoreInfo["modifiers"];

$sort = getScoreSorting($scoreInfo);

//What their last best score is so we can give them the difference in rating
$bests = $user->getBestScores($mission, 1);
if (count($bests) == 1) {
	$lastBest = $bests[0];
} else {
	//Dummy last best result
	$lastBest = [
		"rating" => 0
	];
}

if ($extraModes != null) {
	$extraModes = implode(" ", $extraModes);
}

//Get their position on the scoreboards, BEFORE we send the score in (as that will
// corrupt this index). Also +1 since this is # of people better, not position.
$position = $mission->getScorePlacement($scoreInfo) + 1;
$record = $mission->getScoreBeatsRecord($scoreInfo);
if ($record) {
	//World record, flag it
	$modifiers |= Modifiers::WasWorldRecord;
	//Ask for their replay
	techo("RECORDING");
}

$query = $db->prepare("INSERT INTO ex82r_user_scores SET
	`user_id`       = :user_id,
	`mission_id`    = :mission_id,
	`score`         = :score,
	`score_type`    = :scoreType,
	`total_bonus`   = :totalBonus,
	`rating`        = :rating,
	`gem_count`     = :gemCount,
	`gems_1_point`  = :gems1,
	`gems_2_point`  = :gems2,
	`gems_5_point`  = :gems5,
	`gems_10_point` = :gems10,
	`modifiers`     = :modifiers,
	`extra_modes`   = :extra_modes,
	`sort`          = :sort
");

$query->bindValue(":user_id",     $user->id);
$query->bindValue(":mission_id",  $mission->id);
$query->bindValue(":score",       $score);
$query->bindValue(":scoreType",   $scoreType);
$query->bindValue(":totalBonus",  $totalBonus);
$query->bindValue(":rating",      $rating);
$query->bindValue(":gemCount",    $gemCount);
$query->bindValue(":gems1",       $gems[1]);
$query->bindValue(":gems2",       $gems[2]);
$query->bindValue(":gems5",       $gems[5]);
$query->bindValue(":gems10",      $gems[10]);
$query->bindValue(":sort",        $sort);
$query->bindValue(":modifiers",   $modifiers);
$query->bindValue(":extra_modes", $extraModes);

requireExecute($query);
//If it's a laps mission we should also store their lap time
$lapTime = param("lapTime");
if (stristr($mission->gamemode, "laps") !== false && $lapTime !== null) {
	$query = $db->prepare("
		INSERT INTO ex82r_user_lap_times SET
		`user_id` = :user_id,
		`mission_id` = :mission_id,
		`time` = :time
	");
	$query->bindValue(":user_id",    $user->id);
	$query->bindValue(":mission_id", $mission->id);
	$query->bindValue(":time",       $lapTime);
	$query->execute();

	//And we don't care about the rest
}

//Now we need to give them rating
$ratingIncrease = $rating - $lastBest["rating"];
//Don't increase if the previous was an invalid score
if ($ratingIncrease > 0 && $lastBest["rating"] >= 0) {
	$ratingColumn = $scoreInfo["rating_column"];
	if ($ratingColumn !== null) {
		//Increase both general and the column for this game
		$query = $db->prepare("UPDATE `ex82r_user_ratings`
			SET `rating_general` = `rating_general` + :increase,
			    $ratingColumn = $ratingColumn + :increase2
			WHERE `user_id` = :user_id
		");
		$query->bindValue(":increase", $ratingIncrease);
		$query->bindValue(":increase2", $ratingIncrease);
		$query->bindValue(":user_id", $user->id);
		$query->execute();
	}
}

//100% bonus for quota, if this is the only 100% time they have
if (($scoreInfo["modifiers"] & Modifiers::QuotaHundred) &&
    $user->getScoreCount($mission, Modifiers::QuotaHundred) == 1) {
	//They got the 100% in quota, give them a bonus
	$query = $db->prepare("UPDATE `ex82r_user_ratings`
		SET `rating_quota_bonus` = `rating_quota_bonus` + :bonus,
		    `rating_general` = `rating_general` + :bonus2
		WHERE `user_id` = :user_id");
	$query->bindValue(":bonus", $scoreInfo["quota_bonus"]);
	$query->bindValue(":bonus2", $scoreInfo["quota_bonus"]);
	$query->bindValue(":user_id", $user->id);
	$query->execute();
}

//Get new ratings
$user->update();

techo("SUCCESS");
techo("RATING " . $rating);
techo("NEWRATING " . $user->getRating("rating_general"));
techo("POSITION " . $position);
techo("DELTA " . $ratingIncrease);

$showWR = ($db->getSetting("show_wr_messages") && $record && !$mission->ratingInfo["normally_hidden"]);
if ($showWR) {
	//Show WR messages if there are more than 10 scores on the level
	$query = $db->prepare("SELECT COUNT(*) FROM (SELECT * FROM prod_pq.ex82r_user_scores WHERE mission_id = :mission_id GROUP BY `user_id`) AS scorers");
	$query->bindValue(":mission_id", $mission->id);
	$query->execute();
	$count = $query->fetchColumn(0);

	$neededCount = (int)$db->getSetting("wr_player_count");
	if ($count >= $neededCount) {
		//LBChatColor("record") @ %display SPC "has just achieved a world record on \"" @ %name @ "\" of" SPC formatTime(%time);
		//LBChatColor("record") @ %display SPC "has just achieved a world record on \"" @ %name @ "\" of" SPC %score;

		$message = "{$user->joomla["name"]} has just achieved a world record on \"{$mission->name}\" ({$mission->gameInfo["display"]} {$mission->difficultyInfo["display"]}) of ";

		$info = Platinum\encodeName($mission->name) . " " . round($score);
		if ($scoreType === "time") {
			Platinum\postNotify("record", $user->joomla["username"], -1, $info);

			$message .= formatTime(round($score), true);
		} else {
			//Score
			Platinum\postNotify("recordscore", $user->joomla["username"], -1, $info);

			$message .= round($score);
		}

		//Send it to discord
		DiscordLink::getInstance()->sendMessage("346039779126935552", $message);
	}
}

include("../Achievement/UpdateAchievements.php");
$achievements = updateAchievements($user);
foreach ($achievements as $achievement) {
	techo("ACHIEVEMENT " . $achievement["id"]);
}
