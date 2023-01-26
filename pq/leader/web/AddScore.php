<?php
define("PQ_RUN", true);
require_once("../Framework.php");

if (!Login::isPrivilege("pq.mod.editRatings")) {
	header("HTTP/1.1 401 Unauthorized");
	die();
}

$username  = requireParam("username");
$score     = requireParam("score");
$scoreType = requireParam("score_type");
$mission   = Mission::getByParams();
if ($mission === null) {
	error("Need mission");
}

$totalBonus = param("total_bonus") ?? 0;
$gemCount   = param("gem_count") ?? 0;
$modifiers  = param("modifiers") ?? 0;
$marbleId   = 0;

$gems     = [];
$gems[1]  = param("gems_1_count")  ?? 0;
$gems[2]  = param("gems_2_count")  ?? 0;
$gems[5]  = param("gems_5_count")  ?? 0;
$gems[10] = param("gems_10_count") ?? 0;

$user = User::get(JoomlaSupport::getUserId($username));
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

//Get their position on the scoreboards, BEFORE we send the score in (as that will
// corrupt this index). Also +1 since this is # of people better, not position.
$position = $mission->getScorePlacement($scoreInfo) + 1;
$record = $mission->getScoreBeatsRecord($scoreInfo);
if ($record) {
	//World record, flag it
	$modifiers |= Modifiers::WasWorldRecord;
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
	`sort`          = :sort,
	`origin`        = 'Ratings Viewer'
");

$query->bindValue(":user_id",    $user->id);
$query->bindValue(":mission_id", $mission->id);
$query->bindValue(":score",      $score);
$query->bindValue(":scoreType",  $scoreType);
$query->bindValue(":totalBonus", $totalBonus);
$query->bindValue(":rating",     $rating);
$query->bindValue(":gemCount",   $gemCount);
$query->bindValue(":gems1",      $gems[1]);
$query->bindValue(":gems2",      $gems[2]);
$query->bindValue(":gems5",      $gems[5]);
$query->bindValue(":gems10",     $gems[10]);
$query->bindValue(":sort",       $sort);
$query->bindValue(":modifiers", $modifiers);

requireExecute($query);
$scoreId = $db->lastInsertId();

//Now we need to give them rating
$ratingIncrease = $rating - $lastBest["rating"];
if ($ratingIncrease > 0) {
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

include("../api/Achievement/UpdateAchievements.php");
$achievements = updateAchievements($user);

if ($scoreType === "time") {
	$prettyScore .= formatTime(round($score), true);
} else {
	$prettyScore .= round($score);
}

$prettyBonus = formatTime($totalBonus, true);

$message = "ADD SCORE: \"{$mission->name}\" ({$mission->gameInfo["display"]} {$mission->difficultyInfo["display"]}): {$user->joomla["name"]} {$prettyScore}, position: #{$position}";
if ($record) {
	$message .= " (WORLD RECORD)";
}
$message .= "\nBonus: $prettyBonus, Gems: $gemCount, Reds: {$gems[1]}, Yellows: {$gems[2]}, Blues: {$gems[5]}, Platinums: {$gems[10]}";
$message .= "\nModifiers: ";
if (($modifiers & Modifiers::GotEasterEgg) === Modifiers::GotEasterEgg) $message .= " GotEasterEgg";
if (($modifiers & Modifiers::NoJumping) === Modifiers::NoJumping) $message .= " NoJumping";
if (($modifiers & Modifiers::DoubleDiamond) === Modifiers::DoubleDiamond) $message .= " DoubleDiamond";
if (($modifiers & Modifiers::NoTimeTravels) === Modifiers::NoTimeTravels) $message .= " NoTimeTravels";
if (($modifiers & Modifiers::QuotaHundred) === Modifiers::QuotaHundred) $message .= " QuotaHundred";
if (($modifiers & Modifiers::GemMadnessAll) === Modifiers::GemMadnessAll) $message .= " GemMadnessAll";
if (($modifiers & Modifiers::BeatParTime) === Modifiers::BeatParTime) $message .= " BeatParTime";
if (($modifiers & Modifiers::BeatPlatinumTime) === Modifiers::BeatPlatinumTime) $message .= " BeatPlatinumTime";
if (($modifiers & Modifiers::BeatUltimateTime) === Modifiers::BeatUltimateTime) $message .= " BeatUltimateTime";
if (($modifiers & Modifiers::BeatAwesomeTime) === Modifiers::BeatAwesomeTime) $message .= " BeatAwesomeTime";
if (($modifiers & Modifiers::BeatParScore) === Modifiers::BeatParScore) $message .= " BeatParScore";
if (($modifiers & Modifiers::BeatPlatinumScore) === Modifiers::BeatPlatinumScore) $message .= " BeatPlatinumScore";
if (($modifiers & Modifiers::BeatUltimateScore) === Modifiers::BeatUltimateScore) $message .= " BeatUltimateScore";
if (($modifiers & Modifiers::BeatAwesomeScore) === Modifiers::BeatAwesomeScore) $message .= " BeatAwesomeScore";
if (($modifiers & Modifiers::WasWorldRecord) === Modifiers::WasWorldRecord) $message .= " WasWorldRecord";

//Send it to discord
DiscordLink::getInstance()->sendMessage("XXXXXXXXXXXXXXX", $message);

echo(json_encode([
	"id" => $scoreId,
	"rating" => $rating,
	"newRating" => $user->getRating("rating_general"),
	"position" => $position,
	"increase" => ($ratingIncrease < 0 ? 0 : $ratingIncrease),
	"achievements" => $achievements
]));
