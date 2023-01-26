<?php
define("PQ_RUN", true);
require_once("../Framework.php");

$jdb = JoomlaSupport::db();

function testCommit(Database $db) {
	$db->commit();
//	$db->rollBack();
}

function setRatingColumn($userId, $column, $rating) {
	global $db, $jdb;

	$query = $jdb->prepare("SELECT username FROM bv2xj_users WHERE id = :id");
	$query->bindValue(":id", $userId);
	$query->execute();
	$username = $query->fetchColumn(0);

	//Get current rating for them for messages
	$query = $db->prepare("SELECT `$column` FROM `ex82r_user_ratings` WHERE `user_id` = :user_id");
	$query->bindValue(":user_id", $userId);
	$query->execute();
	$current = $query->fetchColumn(0);

	if ($current != $rating) {
		echo("Update $column for $userId ($username), $current --> $rating\n");
	}

	$query = $db->prepare("UPDATE `ex82r_user_ratings` SET `$column` = :rating WHERE `user_id` = :user_id");
	$query->bindValue(":rating", $rating);
	$query->bindValue(":user_id", $userId);
	$query->execute();
}

//-----------------------------------------------------------------------------


//Get all the missions
$query = $db->prepare("
	SELECT `ex82r_missions`.`id`, `basename` FROM `ex82r_missions`
	JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
	WHERE game_type = 'Single Player'
");
$query->execute();

$missionLookup = [];
while (($row = $query->fetch()) !== false) {
	$id = $row[0];
	$basename = $row[1];
	$mission = Mission::getById($id);
	$missionLookup[$id] = $mission;
}
echo("Got all missions\n");

//-----------------------------------------------------------------------------

//Now recalc the scores themselves
$query = $db->prepare("
	SELECT ex82r_user_scores.* FROM ex82r_user_scores
	JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
	JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
	WHERE game_type = 'Single Player'
	ORDER BY `id` ASC
");
$query->execute();
$i = 0;
$count = $query->rowCount();

$db->beginTransaction();
while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	$scoreInfo = [
		"score"     => $row["score"],
		"type"      => $row["score_type"],
		"modifiers" => $row["modifiers"] ?? 0,
		"gems"      => $row["gems"]      ?? 0,
		"gemCount"  => $row["gem_count"] ?? 0
	];
	$newRating = SPRatings::getScoreRating($scoreInfo, $missionLookup[$row["mission_id"]]);

	if (is_infinite($newRating) || is_nan($newRating)) {
		die("[$i/$count, id {$row["id"]}] Rating is now infinity! What.\n");
	}

	$newSort = getScoreSorting($scoreInfo);

	if ($newRating != $row["rating"] || $newSort != $row["sort"] || $scoreInfo["modifiers"] != $row["modifiers"]) {
		$delta = $newRating - $row["rating"];
		echo("[$i/$count, id {$row["id"]}] Rating update: {$missionLookup[$row["mission_id"]]->name} from {$row["rating"]} to $newRating delta {$delta}\n");
		$update = $db->prepare("UPDATE ex82r_user_scores SET rating = :rating, sort = :sort, modifiers = :modifiers WHERE id = :id");
		$update->bindValue(":rating", $newRating);
		$update->bindValue(":sort", $newSort);
		$update->bindValue(":modifiers", $scoreInfo["modifiers"]);
		$update->bindValue(":id", $row["id"]);
		$update->execute();
	}
	$i ++;
}
testCommit($db);
echo("Got all score ratings\n");

//-----------------------------------------------------------------------------

$query = $db->prepare("SELECT * FROM `ex82r_mission_games` WHERE game_type = 'Single Player'");
$query->execute();
$games = $query->fetchAll(PDO::FETCH_ASSOC);

$ratingSum = [];

$db->beginTransaction();
foreach ($games as $gameRow) {
	$gameId = $gameRow["id"];
	$gameColumn = $gameRow["rating_column"];

	if ($gameColumn === null) {
		continue;
	}

	$query = $db->prepare("
		SELECT `user_id`, SUM(`rating`) AS `rating` FROM
		(
		    SELECT DISTINCT `bests`.`mission_id`, `bests`.`user_id`, `rating`, `score`
		    FROM
		    -- Select all time scores
		    (
		        SELECT `user_id`, `mission_id`, MIN(`sort`) AS `minSort`
		        FROM ex82r_user_scores
		        GROUP BY `user_id`, `mission_id`
		    ) AS `bests`
		    -- Join the scores table so we can get other info
		    JOIN ex82r_user_scores
		      ON ex82r_user_scores.`mission_id` = `bests`.`mission_id`
		     AND ex82r_user_scores.`user_id` = `bests`.`user_id`
		     AND ex82r_user_scores.`sort` = `bests`.`minSort`
		    JOIN `ex82r_missions`
		      ON ex82r_user_scores.`mission_id` = `ex82r_missions`.`id`
		    WHERE game_id = :gameId
		  ) AS `bestScores`
		GROUP BY `user_id`
		ORDER BY `rating` DESC
	");
	$query->bindValue(":gameId", $gameId);
	$query->execute();

	while (($row = $query->fetch()) !== false) {
		list($userId, $rating) = $row;
		$ratingSum[$userId] += $rating;
		setRatingColumn($userId, $gameColumn, $rating);
	}

	echo("Got {$gameRow["name"]} ratings\n");
}
testCommit($db);
echo("Got level ratings\n");

//-----------------------------------------------------------------------------

//Get egg ratings
$query = $db->prepare("
	SELECT user_id, SUM(egg_rating) FROM (
	    SELECT `user_id`, ex82r_user_eggs.`mission_id` FROM ex82r_user_eggs
	    GROUP BY user_id, mission_id
	) AS users_eggs
	JOIN ex82r_missions ON users_eggs.mission_id = ex82r_missions.id
	JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
	GROUP BY user_id
");
$query->execute();

$db->beginTransaction();
while (($row = $query->fetch()) !== false) {
	list($userId, $rating) = $row;
	$ratingSum[$userId] += $rating;
	setRatingColumn($userId, "rating_egg", $rating);
}
testCommit($db);

echo("Got egg ratings\n");

/*
//Update everyone's achievements
require("../api/Achievement/UpdateAchievements.php");
$query = $db->prepare("SELECT `user_id` FROM `ex82r_user_ratings`");
$query->execute();
while (($userId = $query->fetchColumn(0)) !== false) {
	$user = User::get($userId);
	updateAchievements($user);
}

echo("Got achievement ratings\n");
*/

//Some extra ratings that aren't used in any game
$query = $db->prepare("SELECT * FROM `ex82r_user_ratings` ORDER BY rating_general");
$query->execute();

while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	$userId = $row["user_id"];

	$ratingSum[$userId] +=
		  $row["rating_quota_bonus"]
		+ $row["rating_achievement"];
}

//Get current rating for them for messages
$query = $db->prepare("SELECT `user_id`, `rating_general` FROM `ex82r_user_ratings`");
$query->execute();
$oldGenerals = fetchQueryAssociative($query);

$db->beginTransaction();
foreach ($ratingSum as $userId => $rating) {
	setRatingColumn($userId, "rating_general", $rating);
}
testCommit($db);

echo("Got general ratings\n");

echo("Updates:\n");

$lines = [];
foreach ($ratingSum as $userId => $rating) {
	$query = $jdb->prepare("SELECT name FROM bv2xj_users WHERE id = :id AND `block` = 0");
	$query->bindValue(":id", $userId);
	$query->execute();
	$name = $query->fetchColumn(0);

	$current = $oldGenerals[$userId];

	$delta = $rating - $current;
	if ($delta > 0) {
		$delta = "+$delta";
	}

	if ($current != $rating) {
		$lines[] = "Updated rating for $name, $current --> $rating ($delta)";
	}
}

usort($lines, function($s1, $s2) {
	return strcasecmp($s1, $s2);
});
echo(implode("\n", $lines));

