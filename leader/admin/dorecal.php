<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");
require_once("../lbratings.php");
require_once("../MP_Master/calcgorithm.php");

$access = getAccess();

require("../achievementcheck.php");
require("../ultraachievementcheck.php");

if (getPostValue("reall")) {
	setServerPref("lastrecalc", date("U"));
}

if ($access > 0) {
	$time = time();

	set_time_limit(0);

	$user = $_POST["user"];
	$users = array();

	$query = pdo_prepare("SELECT * FROM `users` WHERE `username` = :user");
	$query->bind(":user", $user, PDO::PARAM_STR);
	$result = $query->execute();
	$userArray = $result->fetch();

	echo("RECALLING $user\n\n");
	$query = pdo_prepare("SELECT * FROM `scores` WHERE `username` = :user ORDER BY `rating` DESC");
	$query->bind(":user", $user, PDO::PARAM_STR);
	$result = $query->execute();

	checkAchievements($user);
	checkUltraAchievements($user);

	$query = pdo_prepare("UPDATE `users` SET `rating` = `rating` - `rating_achievements` WHERE `username` = :user");
	$query->bind(":user", $user);
	$query->execute();

	$query = pdo_prepare("UPDATE `users` SET `rating_achievements` = 0 WHERE `username` = :user");
	$query->bind(":user", $user);
	$query->execute();

	$achievPoints = achievementPoints($user);
	$ultraAchievPoints = ultraAchievementPoints($user);

	$query = pdo_prepare("UPDATE `users` SET `rating_achievements` = :ratingach WHERE `username` = :user");
	$query->bind(":ratingach", $achievPoints + $ultraAchievPoints);
	$query->bind(":user", $user);
	$query->execute();

	$userArray["rating_achievements"] = $achievPoints + $ultraAchievPoints;

	$seen = array();
	$changeAll = 0;
	$changeTop = 0;

	$largest = 0;
	$largestA = 0;
	$largestName = "";
	$largestNameA = "";

	$count = 0;
	$last = array();

	while (($row = $result->fetch()) !== false) {
		$score    = $row["score"];
		$level    = $row["level"];
		$type     = $row["type"];
		$gametype = $row["gametype"];
		$rating   = $row["rating"];
		$id       = $row["id"];

		if (array_key_exists("level", $last) &&
			$last["level"] == $row["level"] &&
		    $last["score"] == $row["score"] &&
		    $last["rating"] == $row["rating"] &&
		    $last["gametype"] == $row["gametype"] &&
		    $last["type"] == $row["type"]) {
			$query = pdo_prepare("DELETE FROM `scores` WHERE `id` = :id");
			$query->bind(":id", $id, PDO::PARAM_INT);
			echo("!Removed duplicate score for level $level of score $score and rating " . number_format($rating) . "!\n");
			$query->execute();
			continue;
		}

		$last = $row;

		$realRating = getRating($score, $level, $gametype);

		//Something's strange
		if ($realRating == -5) {
			$data = getLevelArray($level);
			echo("!Update gametype on score for level $level of score $score and rating " . number_format($rating) . ". Was $gametype, should be {$data["MissionGame"]}!\n");
			$gametype = $data["MissionGame"];
			$realRating = getRating($score, $level, $gametype);

			$query = pdo_prepare("UPDATE `scores` SET `gametype` = :gametype WHERE `id` = :id");
			$query->bind(":gametype", $gametype, PDO::PARAM_STR);
			$query->bind(":id", $id, PDO::PARAM_INT);
			$query->execute();
		}

		$difference = ($realRating - $rating);
		$changeAll += $difference;

		if ($realRating != $rating) {
			$count ++;
			//Update on the table
			$query = pdo_prepare("UPDATE `scores` SET `rating` = :realRating WHERE `id` = :id");
			$query->bind(":realRating", $realRating, PDO::PARAM_INT);
			$query->bind(":id", $id, PDO::PARAM_INT);
			$query->execute();

			//Update this too
			$row["rating"] = $realRating;
			$last = $row;
		}

		//No duplicates
		if (in_array($level, $seen))
			continue;
		array_push($seen, $level);

		//Now we have all the top scores

		$changeTop += $difference;

		if ($rating != $realRating) {
			$data = getLevelArray($level);

			if ($data == NULL)
				continue;
			$type = $data["MissionType"];
			$ratingField = "rating_";

			if ($data["MissionGame"] == "Gold")
				$ratingField .= "mbg";
			else if ($data["MissionGame"] == "Platinum")
				$ratingField .= "mbp";
			else if ($data["MissionGame"] == "Ultra")
				$ratingField .= "mbu";
			else if ($data["MissionGame"] == "MultiPlayer")
				$ratingField .= "mp";
			else if ($data["MissionGame"] == "LBCustom")
				$ratingField .= "custom";

			$query = pdo_prepare("UPDATE `users` SET `rating` = `rating`+$difference, `$ratingField` = `$ratingField`+$difference WHERE `username` = :user");
			$query->bind(":user", $user, PDO::PARAM_STR);
			$query->execute();

			if ($rating > $realRating && abs($difference) > $largestA) {
				$largestA = abs($difference);
				$largestNameA = $level;
			}
			if ($rating < $realRating && abs($difference) > $largest) {
				$largest = abs($difference);
				$largestName = $level;
			}

			if ($rating > $realRating)
				echo("!");
			echo("Rating for $level of time $score is " . number_format($rating) . ", should be " . number_format($realRating) . ", discrepancy of " . number_format($difference));
			if ($rating > $realRating)
				echo("!");
			echo("\n");
		}
	}

	$query = pdo_prepare("SELECT * FROM `topscores` WHERE `username` = :user ORDER BY `rating` DESC");
	$query->bind(":user", $user, PDO::PARAM_STR);
	$result = $query->execute();

	while (($row = $result->fetch()) !== false) {
		$score    = $row["score"];
		$level    = $row["level"];
		$type     = $row["type"];
		$gametype = $row["gametype"];
		$rating   = $row["rating"];
		$id       = $row["id"];

		$realRating = getRating($score, $level, $gametype);

		//Something's strange
		if ($realRating == -5) {
			$data = getLevelArray($level);
			echo("!Update gametype on score for level $level of score $score and rating " . number_format($rating) . ". Was $gametype, should be {$data["MissionGame"]}!\n");
			$gametype = $data["MissionGame"];
			$realRating = getRating($score, $level, $gametype);

			$query = pdo_prepare("UPDATE `topscores` SET `gametype` = :gametype WHERE `id` = :id");
			$query->bind(":gametype", $gametype, PDO::PARAM_STR);
			$query->bind(":id", $id, PDO::PARAM_INT);
			$query->execute();
		}

		$difference = ($realRating - $rating);
		$changeAll += $difference;

		if ($realRating != $rating) {
			$count ++;
			//Update on the table
			$query = pdo_prepare("UPDATE `topscores` SET `rating` = :realRating WHERE `id` = :id");
			$query->bind(":realRating", $realRating, PDO::PARAM_INT);
			$query->bind(":id", $id, PDO::PARAM_INT);
			$query->execute();

			//Update this too
			$row["rating"] = $realRating;
			$last = $row;
			
			if ($rating > $realRating)
				echo("!");
			echo("Rating for $level of time $score is " . number_format($rating) . ", should be " . number_format($realRating) . ", discrepancy of " . number_format($difference));
			if ($rating > $realRating)
				echo("!");
			echo("\n");
		}
	}

	if ($count == 0) {
		echo("All scores already up to date.\n\n");
	} else {
		echo("All scores rating was " . number_format($changeAll) . " points lower than normal\n");
		echo("Top scores rating was " . number_format($changeTop) . " points lower than normal\n");
		echo("\n");
		echo("Largest difference (positive): $largestName with a rating difference of " . number_format($largest) . "\n");
		echo("!Largest difference (negative): $largestNameA with a rating difference of -" . number_format($largestA) . "!\n");
		echo("\n");
		echo("Updated " . number_format($count) . " scores\n");
	}

	$query = pdo_prepare("SELECT * FROM `scores` WHERE `username` = :user ORDER BY `rating` DESC");
	$query->bind(":user", $user, PDO::PARAM_STR);
	$result = $query->execute();

	$seen = array();
	$finalRating = 0;
	$finalCategories = array();

	while (($row = $result->fetch()) !== false) {
		$score    = $row["score"];
		$level    = $row["level"];
		$type     = $row["type"];
		$gametype = $row["gametype"];
		$rating   = $row["rating"];
		$id       = $row["id"];

		//No duplicates
		if (in_array($level, $seen))
			continue;
		array_push($seen, $level);

		//Now we have all the top scores
		$finalRating += $rating;

		$data = getLevelArray($level);
		$type = $data["MissionType"];
		$ratingField = "rating_";

		if ($data["MissionGame"] == "Gold")
			$ratingField .= "mbg";
		else if ($data["MissionGame"] == "Platinum")
			$ratingField .= "mbp";
		else if ($data["MissionGame"] == "Ultra")
			$ratingField .= "mbu";
		else if ($data["MissionGame"] == "MultiPlayer")
			$ratingField .= "mp";
		else if ($data["MissionGame"] == "LBCustom")
			$ratingField .= "custom";

		if ($ratingField != "rating_") {
			if (array_key_exists($ratingField, $finalCategories))
				$finalCategories[$ratingField] += $rating;
			else
				$finalCategories[$ratingField]  = $rating;
		}
	}

	//And add achievements too
	$query = pdo_prepare("SELECT SUM(`rating`) FROM `achievements` WHERE `username` = :username");
	$query->bind(":username", $user);
	$finalCategories["rating_achievements"] = $query->execute()->fetchIdx(0);

	$query = pdo_prepare("SELECT SUM(`rating`) FROM `ultraachievements` WHERE `username` = :username");
	$query->bind(":username", $user);
	$finalCategories["rating_achievements"] += $query->execute()->fetchIdx(0);

	$finalRating += $finalCategories["rating_achievements"];

	foreach (array("rating_mbg", "rating_mbp", "rating_mbu", "rating_custom", "rating_achievements") as $field) {
		if (!array_key_exists($field, $finalCategories))
			$finalCategories[$field] = 0;
	}

	$difference = $finalRating - $userArray["rating"];
	echo("User's rating is now " . number_format($finalRating) . ", was " . number_format($userArray["rating"]) . ". Difference of " . number_format($difference) . "\n");

	$query = pdo_prepare("UPDATE `users` SET `rating` = :finalRating WHERE `username` = :user");
	$query->bind(":finalRating", $finalRating, PDO::PARAM_INT);
	$query->bind(":user", $user, PDO::PARAM_STR);
	$query->execute();
	foreach ($finalCategories as $ratingField => $rating) {
		echo("$ratingField => $rating\n");
		if ($userArray[$ratingField] != $rating) {
			$difference = $rating - $userArray[$ratingField];
			echo("User's rating for category $ratingField now " . number_format($rating) . ", was " . number_format($userArray[$ratingField]) . ". Difference of " . number_format($difference) . "\n");

			$query = pdo_prepare("UPDATE `users` SET `$ratingField` = :rating WHERE `username` = :user");
			$query->bind(":rating", $rating, PDO::PARAM_INT);
			$query->bind(":user", $user, PDO::PARAM_STR);
			$query->execute();
		}
	}

	echo("Scores update complete.\n");
	echo("TOOK A WHOPPING " . time() - $time . " SECONDS\n");
}

?>
