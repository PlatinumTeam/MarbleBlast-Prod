<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$username = param("user") ?? Login::getCurrentUsername();
$userId = JoomlaSupport::getUserId($username);
$user = User::get($userId);

$result = [
	"username" => $username,
    "id" => $userId
];

//Easy things
$result["access"] = $user->leaderboards["access"];
$result["display"] = sanitizeDisplayName($user->joomla["name"]);
$result["color"] = $user->joomla["colorValue"];
$result["titles"] = [
	"flair" => getTitle($user->joomla["titleFlair"]),
    "prefix" => getTitle($user->joomla["titlePrefix"]),
	"suffix" => getTitle($user->joomla["titleSuffix"])
];

//Lots of encoding on this one
$result["status"] = htmlspecialchars_decode(htmlspecialchars_decode($user->joomla["statusMsg"]), ENT_QUOTES);
$result["donations"] = $user->joomla["donations"];

//Account age
$result["registerDate"] = $user->joomla["registerDate"] . "UTC";
$result["accountAge"] = convertTimeReadablePrecise($user->joomla["registerDate"]);

//Total time online
$onlineQuery = $pdb->prepare("SELECT SUM(`data`) FROM `tracking` WHERE `type` = 'logintime' AND `username` = :username LIMIT 1");
$onlineQuery->bindValue(":username", $username);
$onlineQuery->execute();
$seconds = $onlineQuery->fetchColumn(0);
$result["totalTime"] = $seconds;

//Ratings and rankings for MBP/PQ
$result["rating"] = $user->ratings;
$result["ranking"] = [];
foreach ($user->ratings as $column => $rating) {
	//Yes please rate me based on my user id
	if ($column === "user_id") continue;
	$result["ranking"][$column] = getRank($user, $column);
}

//Last level
$query = $db->prepare("
	SELECT `name` FROM ex82r_user_scores
	JOIN `ex82r_missions` ON ex82r_user_scores.`mission_id` = `ex82r_missions`.`id`
	WHERE `user_id` = :user_id
	ORDER BY ex82r_user_scores.`id` DESC LIMIT 1
");
$query->bindValue(":user_id", $user->id);
$query->execute();
if ($query->rowCount() == 1) {
	$result["lastLevel"] = $query->fetchColumn(0);
} else {
	$result["lastLevel"] = "None";
}

//MP non-teams match placements
$query = $db->prepare("
	SELECT `placement`, COUNT(*) AS `count` FROM `ex82r_matches`
	  JOIN `ex82r_match_scores` ON `ex82r_matches`.`id` = `ex82r_match_scores`.`match_id`
	WHERE `user_id` = :user_id AND `player_count` > 1 AND `team_id` IS NULL
	GROUP BY `placement`
");
$query->bindValue(":user_id", $user->id);
$query->execute();
$result["mp_games"] = fetchQueryAssociative($query);

//MP teams match placements
$query = $db->prepare("
	SELECT `placement`, COUNT(*) AS `count` FROM `ex82r_matches`
	  JOIN `ex82r_match_scores` ON `ex82r_matches`.`id` = `ex82r_match_scores`.`match_id`
	WHERE `user_id` = :user_id AND `player_count` > 1 AND `team_id` IS NOT NULL
	GROUP BY `placement`
");
$query->bindValue(":user_id", $user->id);
$query->execute();
$result["mp_team_games"] = fetchQueryAssociative($query);

//MP gem counts
$query = $db->prepare("
	SELECT
	  SUM(`gems_1_point`) AS `red`,
	  SUM(`gems_2_point`) AS `yellow`,
	  SUM(`gems_5_point`) AS `blue`,
	  SUM(`gems_10_point`) AS `platinum`
	FROM `ex82r_match_scores`
	  JOIN ex82r_user_scores ON `ex82r_match_scores`.`score_id` = ex82r_user_scores.`id`
	WHERE `ex82r_match_scores`.`user_id` = :user_id
");
$query->bindValue(":user_id", $user->id);
$query->execute();
$result["mp_gems"] = $query->fetch(PDO::FETCH_ASSOC);

$query = $db->prepare("
	SELECT score, name, player_count FROM ex82r_matches
	  JOIN ex82r_match_scores ON ex82r_matches.id = ex82r_match_scores.match_id
	  JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
	  JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
	WHERE ex82r_match_scores.user_id = :user_id
	  AND score_type = 'Score'
	  AND player_count > 1
	ORDER BY sort ASC
	LIMIT 1
");
$query->bindValue(":user_id", $user->id);
$query->execute();
$best = $query->fetch(PDO::FETCH_ASSOC);
$result["mp_best"] = number_format($best["score"]) . " points on " . $best["name"] . " against " . ($best["player_count"] - 1) . " player" . ($best["player_count"] > 2 ? "s" : "") . ".";

$query = $db->prepare("
	SELECT ROUND(AVG(gem_count)) FROM ex82r_match_scores
	  JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
	WHERE ex82r_match_scores.user_id = :user_id
	AND score_type = 'Score'
");
$query->bindValue(":user_id", $user->id);
$query->execute();
$result["mp_average"] = $query->fetchColumn(0);

//Best Score (MP)
//Average Score (MP)

//Friends list
$query = $pdb->prepare("
	SELECT `bv2xj_users`.`name`, `bv2xj_users`.`username` FROM `friends`
	JOIN `users` ON `users`.`id` = `friends`.`friendid`
	JOIN `prod_joomla`.`bv2xj_users` ON `bv2xj_users`.`username` = `users`.`username`
	WHERE `friends`.`username` = :username
");
$query->bindValue(":username", $username);
$query->execute();
$result["friends"] = $query->fetchAll(PDO::FETCH_ASSOC);

//And send it all off
techo(json_encode($result));

/**
 * @param User   $user
 * @param string $column
 * @return string
 */
function getRank(User $user, $column) {
	global $db;
	$query = $db->prepare("
		SELECT COUNT(*) FROM `ex82r_user_ratings`
		JOIN prod_joomla.bv2xj_users ON prod_joomla.bv2xj_users.id = ex82r_user_ratings.user_id
		WHERE `$column` > :rating
		AND block = 0
	");
	$query->bindValue(":rating", $user->ratings[$column]);
	$query->execute();

	//Since 0th place == #1
	return intval($query->fetchColumn(0)) + 1;
}

/**
 * @param int $id
 * @return string
 */
function getTitle($id) {
	$jdb = JoomlaSupport::db();
	$query = $jdb->prepare("SELECT `title` FROM `bv2xj_user_titles` WHERE `id` = :id");
	$query->bindValue(":id", $id);
	$query->execute();

	if ($query->rowCount()) {
		return $query->fetchColumn(0);
	} else {
		return "";
	}
}

/**
 * Converts a date time timestamp into a human readable format. Uses years months and days.
 * eg. '1 year, 6 months, 24 days'
 * Src adapted from: http://stackoverflow.com/questions/2915864/php-how-to-find-the-time-elapsed-since-a-date-time
 * @param int $time An object containing the timestamp to convert
 * @return string
 */
function convertTimeReadablePrecise($time) {
	$startdate = new DateTime($time);
	$endDate   = new DateTime('now');
	$interval  = $endDate->diff($startdate);
	$days      = $interval->format('%d');
	$months    = $interval->format('%m');
	$years     = $interval->format('%y');

	$str = "";
	if ($years > 0) {
		$str = "$years year";
	}
	if ($years > 1) {
		$str .= "s";
	}

	if ($months > 0) {
		if ($str != "") {
			$str .= ", ";
		}
		$str .= "$months month";
	}
	if ($months > 1) {
		$str .= "s";
	}

	if ($days > 0) {
		if ($str != "") {
			$str .= ", ";
		}
		$str .= "$days day";
	}
	if ($days > 1) {
		$str .= "s";
	}

	return $str;
}
