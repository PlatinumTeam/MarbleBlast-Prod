<?php
$allow_nonwebchat = true;
$ignore_keys = true;

// Open the database connection
require_once("opendb.php");

define("NO_TIME", 5998999);

define("EASTER_EGG",     1 << 0);
define("NO_JUMP",        1 << 1);
define("DOUBLE_DIAMOND", 1 << 2);
define("NO_TT",          1 << 3);

define("DD_FLAIR", 109);

function checkUltraAchievements($username = null) {
	if ($username == null)
		$username = getPostValue("username");

	$achievements = ultraAchievements($username);
	$eggs = easterEggsU($username, "Ultra");
	$ratingMBU = userField($username, "rating_mbu");
	$rating = userField($username, "rating");
	$ratingStart = $rating;
	$ratingAch = userField($username, "rating_achievements");

	/*
	 * Achievement 1: The Only Easy Achievement
	 * Beat any par time
	 * Award: 0 points
	 */

	if (!$achievements[0]) {
		require_once("lbratings.php");

		$query = pdo_prepare("SELECT `level` FROM `scores` WHERE `username` = :user AND `score` < (SELECT `qualify` FROM `officiallevels` WHERE `game` = 'Ultra' AND `officiallevels`.`stripped` = `scores`.`level`) LIMIT 1");
		$query->bind(":user", $username, PDO::PARAM_STR);
		$result = $query->execute();

		if ($result->rowCount()) {
			giveUltraAchievement($username, 0, 0);

			$rating += 0;
			$ratingAch += 0;
		}
	}

	/*
	 * Achievement 2: Egg Hunter
	 * Find all the Easter eggs
	 * Award: 100,000 points
	 */

	if (!$achievements[1] && $eggs == 20) {
		giveUltraAchievement($username, 1, 100000);

		$rating += 100000;
		$ratingAch += 100000;
	}

	/*
	 * Achievement 3: Golden Finale
	 * Get under 30 seconds on Hypercube without jumping!
	 * Award: 150000 points
	 */

	if (!$achievements[2] && topScoreU($username, "hypercubeultra", NO_JUMP) < 30000) {
		giveUltraAchievement($username, 2, 150000);

		$rating += 150000;
		$ratingAch += 150000;
	}

	/*
	 * Achievement 4: Deja
	 * Beat all the Marble Blast Ultra levels seen in Marble Blast Gold
	 * Award: 10,000 points
	 */

	if (!$achievements[3] &&
			topScoreU($username, "pitfallultra") != NO_TIME &&
			topScoreU($username, "gravityultra") != NO_TIME &&
			topScoreU($username, "platformpartyultra") != NO_TIME &&
			topScoreU($username, "earlyfrostultra") != NO_TIME &&
			topScoreU($username, "windingroadultra") != NO_TIME &&
			topScoreU($username, "rampmatrixultra") != NO_TIME &&
			topScoreU($username, "jumpjumpjumpultra") != NO_TIME &&
			topScoreU($username, "upwardultra") != NO_TIME &&
			topScoreU($username, "gauntletultra") != NO_TIME &&
			topScoreU($username, "aroundtheworldultra") != NO_TIME &&
			topScoreU($username, "dualityultra") != NO_TIME &&
			topScoreU($username, "mudslideultra") != NO_TIME &&
			topScoreU($username, "aimhighultra") != NO_TIME &&
			topScoreU($username, "compasspointsultra") != NO_TIME &&
			topScoreU($username, "obstacleultra") != NO_TIME &&
			topScoreU($username, "sporkintheroadultra") != NO_TIME &&
			topScoreU($username, "greatdivideultra") != NO_TIME &&
			topScoreU($username, "plumbingultra") != NO_TIME &&
			topScoreU($username, "whirlultra") != NO_TIME &&
			topScoreU($username, "hopskipjumpultra") != NO_TIME &&
			topScoreU($username, "slickslideultra") != NO_TIME &&
			topScoreU($username, "ordealultra") != NO_TIME &&
			topScoreU($username, "survivalultra") != NO_TIME &&
			topScoreU($username, "reloadedultra") != NO_TIME &&
			topScoreU($username, "scaffoldultra") != NO_TIME &&
			topScoreU($username, "acrobatultra") != NO_TIME &&
			topScoreU($username, "battlementsultra") != NO_TIME &&
			topScoreU($username, "threefoldmazeultra") != NO_TIME &&
			topScoreU($username, "constructionultra") != NO_TIME &&
			topScoreU($username, "skiultra") != NO_TIME &&
			topScoreU($username, "threefoldraceultra") != NO_TIME &&
			topScoreU($username, "kingofthemountainultra") != NO_TIME &&
			topScoreU($username, "selectionultra") != NO_TIME &&
			topScoreU($username, "schadenfreudeultra") != NO_TIME) {

		giveUltraAchievement($username, 3, 10000);

		$rating += 10000;
		$ratingAch += 10000;
	}

	/*
	 * Achievement 5: Vu
	 * Beat all the Marble Blast Ultra levels seen in Marble Blast Platinum (including Andrew's custom levels remake)
	 * Award: 4,000 points
	 */

	if (!$achievements[4] &&
		topScoreU($username, "mountaintopultra") != NO_TIME &&
		topScoreU($username, "ascendultra") != NO_TIME &&
		topScoreU($username, "divergenceultra") != NO_TIME &&
		topScoreU($username, "urbanultra") != NO_TIME &&
		topScoreU($username, "lesstravelultra") != NO_TIME &&
		topScoreU($username, "treehouseultra") != NO_TIME &&
		topScoreU($username, "skateultra") != NO_TIME &&
		topScoreU($username, "cubeultra") != NO_TIME &&
		topScoreU($username, "enduranceultra") != NO_TIME) {

		giveUltraAchievement($username, 4, 4000);

		$rating += 4000;
		$ratingAch += 4000;
	}

	/*
	 * Achievement 6: On Par
	 * Beat the par time on all levels
	 * Award: 16,000 points
	 */

	if (!$achievements[5]) {
		$query = pdo_prepare("SELECT COUNT(*) FROM `officiallevels` WHERE `game` = 'Ultra' AND `qualify` > (SELECT `score` FROM `scores` WHERE `username` = :username AND `level` = `officiallevels`.`stripped` ORDER BY `score` ASC LIMIT 1)");
		$query->bind(":username", $username);
		if ($query->execute()->fetchIdx(0) == 61) {
			giveUltraAchievement($username, 5, 16000);

			$rating += 16000;
			$ratingAch += 16000;
		}
	}

	/*
	 * Achievement 7: Ultra Ultimate
	 * Beat the Ultimate time on all Marble Blast Ultra levels + Hypercube
	 * Award: 550,000 points
	 */

	if (!$achievements[6]) {
		$query = pdo_prepare("SELECT COUNT(*) FROM `officiallevels` WHERE `game` = 'Ultra' AND `ultimate` > (SELECT `score` FROM `scores` WHERE `username` = :username AND `level` = `officiallevels`.`stripped` ORDER BY `score` ASC LIMIT 1)");
		$query->bind(":username", $username);
		if ($query->execute()->fetchIdx(0) == 61) {
			giveUltraAchievement($username, 6, 550000);

			$rating += 550000;
			$ratingAch += 550000;
		}
	}

	/*
	 * Achievement 8: Double Diamond
	 * Get to the very bottom of Black Diamond, and make it back up to the edge under Par in real time.
	 * Award: 50,000 points
	 */

	if (!$achievements[7] && topScoreU($username, "blackdiamondultra", DOUBLE_DIAMOND | NO_TT) < 75000) {
		giveUltraAchievement($username, 7, 50000);

		$rating += 50000;
		$ratingAch += 50000;
	}

	/*
	 * Achievement 9: Scrambled Eggs
	 * Beat Whirl under 25 seconds while getting its Easter Egg in the same run.
	 * Award: 10,000 points
	 */

	if (!$achievements[8] && topScoreU($username, "whirlultra", EASTER_EGG) < 25000) {
		giveUltraAchievement($username, 8, 10000);

		$rating += 10000;
		$ratingAch += 10000;
	}

	/*
	 * Achievement 10: Ratings Monster
	 * Attain at least 5,000,000 points in MBU rating.
	 * Award: 0 points
	 */

	if (!$achievements[9] && $ratingMBU > 4000000) {
		giveUltraAchievement($username, 9, 0);

		$rating += 0;
		$ratingAch += 0;
	}

	/*
	 * Achievement 11: Speediest Marble on the Block!
	 * Beat Black Diamond under 12 seconds
	 * Award: 10,000 points
	 */

	if (!$achievements[10] && topScoreU($username, "blackdiamondultra") < 12000) {
		giveUltraAchievement($username, 10, 10000);

		$rating += 10000;
		$ratingAch += 10000;
	}

	/*
	 * Achievement 12: It's a Jungle Out There
	 * Beat Urban Jungle under 10 seconds
	 * Award: 15,000 points
	 */

	if (!$achievements[11] && topScoreU($username, "urbanultra") < 10000) {
		giveUltraAchievement($username, 11, 15000);

		$rating += 15000;
		$ratingAch += 15000;
	}

	/*
	 * Achievement 13: Trapped
	 * Beat Endurance under 15 seconds
	 * Award: 12,000 points
	 */

	if (!$achievements[12] && topScoreU($username, "enduranceultra") < 15000) {
		giveUltraAchievement($username, 12, 12000);

		$rating += 12000;
		$ratingAch += 12000;
	}

	/*
	 * Achievement 14: Bumped
	 * Beat Early Frost under 7.25 seconds
	 * Award: 6,000 points
	 */

	if (!$achievements[13] && topScoreU($username, "earlyfrostultra") < 7250) {
		giveUltraAchievement($username, 13, 6000);

		$rating += 6000;
		$ratingAch += 6000;
	}

	/*
	 * Achievement 15: Pipe Mastery
	 * Beat Half-Pipe Elite under 1.6 seconds, and Half-Pipe under 1.9 seconds.
	 * Award: 12,000 points
	 */

	if (!$achievements[14] && topScoreU($username, "halfpipe2ultra") < 1600 && topScoreU($username, "halfpipeultra") < 1900) {
		giveUltraAchievement($username, 14, 12000);

		$rating += 12000;
		$ratingAch += 12000;
	}

	// They got one!
	if ($rating != $ratingStart) {
		$query = pdo_prepare("UPDATE `users` SET `rating` = :rating, `rating_achievements` = :ratingAch WHERE `username` = :user");
		$query->bind(":rating", $rating, PDO::PARAM_INT);
		$query->bind(":ratingAch", $ratingAch, PDO::PARAM_INT);
		$query->bind(":user", $username, PDO::PARAM_INT);
		$query->execute();

		echo("NEWRATING $rating\n");
	}

	if (topScoreU($username, "blackdiamondultra", DOUBLE_DIAMOND | NO_TT) < 70000) {
		awardTitle($username, DD_FLAIR);
	}
}

function ultraAchievementProgress($username) {
	$achievements = ultraAchievements($username);
	$eggs = easterEggsU($username, "Ultra");
	$ratingMBU = userField($username, "rating_mbu");

	for ($i = 0; $i < count($achievements); $i ++)
		if ($achievements[$i] == true)
			$achievements[$i] = "100%";

	/*
	 * Achievement 1: The Only Easy Achievement
	 * Beat any par time
	 * Award: 0 points
	 */

	if (!$achievements[0]) {
		$achievements[0] = formatPerc100(0);
	}

	/*
	 * Achievement 2: Egg Hunter
	 * Find all the Easter eggs
	 * Award: 0 points
	 */

	if (!$achievements[1]) {
		$achievements[1] = formatPerc100($eggs / 20);
	}

	/*
	 * Achievement 3: Golden Finale
	 * Get under 30 seconds on Hypercube without jumping!
	 * Award: 0 points
	 */

	if (!$achievements[2]) {
		$achievements[2] = formatTime3(topScoreU($username, "hypercubeultra", NO_JUMP), 30000);
	}

	/*
	 * Achievement 4: Deja
	 * Beat all the Marble Blast Ultra levels seen in Marble Blast Gold
	 * Award: 0 points
	 */

	if (!$achievements[3]) {
		$achievements[3] = formatPerc100(
			(topScoreU($username, "pitfallultra") != NO_TIME) +
			(topScoreU($username, "gravityultra") != NO_TIME) +
			(topScoreU($username, "platformpartyultra") != NO_TIME) +
			(topScoreU($username, "earlyfrostultra") != NO_TIME) +
			(topScoreU($username, "windingroadultra") != NO_TIME) +
			(topScoreU($username, "rampmatrixultra") != NO_TIME) +
			(topScoreU($username, "jumpjumpjumpultra") != NO_TIME) +
			(topScoreU($username, "upwardultra") != NO_TIME) +
			(topScoreU($username, "gauntletultra") != NO_TIME) +
			(topScoreU($username, "aroundtheworldultra") != NO_TIME) +
			(topScoreU($username, "dualityultra") != NO_TIME) +
			(topScoreU($username, "mudslideultra") != NO_TIME) +
			(topScoreU($username, "aimhighultra") != NO_TIME) +
			(topScoreU($username, "compasspointsultra") != NO_TIME) +
			(topScoreU($username, "obstacleultra") != NO_TIME) +
			(topScoreU($username, "sporkintheroadultra") != NO_TIME) +
			(topScoreU($username, "greatdivideultra") != NO_TIME) +
			(topScoreU($username, "plumbingultra") != NO_TIME) +
			(topScoreU($username, "whirlultra") != NO_TIME) +
			(topScoreU($username, "hopskipjumpultra") != NO_TIME) +
			(topScoreU($username, "slickslideultra") != NO_TIME) +
			(topScoreU($username, "ordealultra") != NO_TIME) +
			(topScoreU($username, "survivalultra") != NO_TIME) +
			(topScoreU($username, "reloadedultra") != NO_TIME) +
			(topScoreU($username, "scaffoldultra") != NO_TIME) +
			(topScoreU($username, "acrobatultra") != NO_TIME) +
			(topScoreU($username, "battlementsultra") != NO_TIME) +
			(topScoreU($username, "threefoldmazeultra") != NO_TIME) +
			(topScoreU($username, "constructionultra") != NO_TIME) +
			(topScoreU($username, "skiultra") != NO_TIME) +
			(topScoreU($username, "threefoldraceultra") != NO_TIME) +
			(topScoreU($username, "kingofthemountainultra") != NO_TIME) +
			(topScoreU($username, "selectionultra") != NO_TIME) +
			(topScoreU($username, "schadenfreudeultra") != NO_TIME) / 34);
	}

	/*
	 * Achievement 5: Vu
	 * Beat all the Marble Blast Ultra levels seen in Marble Blast Platinum (including Andrew's custom levels remake)
	 * Award: 0 points
	 */

	if (!$achievements[4]) {
		$achievements[4] = formatPerc100(
		(topScoreU($username, "mountaintopultra") != NO_TIME) +
		(topScoreU($username, "ascendultra") != NO_TIME) +
		(topScoreU($username, "divergenceultra") != NO_TIME) +
		(topScoreU($username, "urbanultra") != NO_TIME) +
		(topScoreU($username, "lesstravelultra") != NO_TIME) +
		(topScoreU($username, "treehouseultra") != NO_TIME) +
		(topScoreU($username, "skateultra") != NO_TIME) +
		(topScoreU($username, "cubeultra") != NO_TIME) +
		(topScoreU($username, "enduranceultra") != NO_TIME) / 9);
	}

	/*
	 * Achievement 6: On Par
	 * Beat the par time on all levels
	 * Award: 0 points
	 */

	if (!$achievements[5]) {
		$query = pdo_prepare("SELECT COUNT(*) FROM `officiallevels` WHERE `game` = 'Ultra' AND `qualify` > (SELECT `score` FROM `scores` WHERE `username` = :username AND `level` = `officiallevels`.`stripped` ORDER BY `score` ASC LIMIT 1)");
		$query->bind(":username", $username);
		$achievements[5] = formatPerc100($query->execute()->fetchIdx(0) / 61);
	}

	/*
	 * Achievement 7: Ultra Ultimate
	 * Beat the Ultimate time on all Marble Blast Ultra levels + Hypercube
	 * Award: 0 points
	 */

	if (!$achievements[6]) {
		$query = pdo_prepare("SELECT COUNT(*) FROM `officiallevels` WHERE `game` = 'Ultra' AND `ultimate` > (SELECT `score` FROM `scores` WHERE `username` = :username AND `level` = `officiallevels`.`stripped` ORDER BY `score` ASC LIMIT 1)");
		$query->bind(":username", $username);
		$achievements[6] = formatPerc100($query->execute()->fetchIdx(0) / 61);
	}

	/*
	 * Achievement 8: Double Diamond
	 * Get to the very bottom of Black Diamond, and make it back up to the edge under Par in real time.
	 * Award: 0 points
	 */

	if (!$achievements[7]) {
		$achievements[7] = formatTime3(topScoreU($username, "blackdiamondultra", DOUBLE_DIAMOND | NO_TT), 75000);
	}

	/*
	 * Achievement 9: Scrambled Eggs
	 * Beat Whirl under 25 seconds while getting its Easter Egg in the same run.
	 * Award: 0 points
	 */

	if (!$achievements[8]) {
		$achievements[8] = formatTime3(topScoreU($username, "whirlultra", EASTER_EGG) < 25000);
	}

	/*
	 * Achievement 10: Ratings Monster
	 * Attain at least 5,000,000 points in MBU rating.
	 * Award: 0 points
	 */

	if (!$achievements[9]) {
		$achievements[9] = formatPerc100($ratingMBU / 4000000);
	}

	/*
	 * Achievement 11: Speediest Marble on the Block!
	 * Beat Black Diamond under 12 seconds
	 * Award: 0 points
	 */

	if (!$achievements[10]) {
		$achievements[10] = formatTime3(topScoreU($username, "blackdiamondultra"), 12000);
	}

	/*
	 * Achievement 12: It's a Jungle Out There
	 * Beat Urban Jungle under 10 seconds
	 * Award: 0 points
	 */

	if (!$achievements[11]) {
		$achievements[11] = formatTime3(topScoreU($username, "urbanultra"), 10000);
	}

	/*
	 * Achievement 13: Trapped
	 * Beat Endurance under 15 seconds
	 * Award: 0 points
	 */

	if (!$achievements[12]) {
		$achievements[12] = formatTime3(topScoreU($username, "enduranceultra"), 15000);
	}

	/*
	 * Achievement 14: Bumped
	 * Beat Early Frost under 7.25 seconds
	 * Award: 0 points
	 */

	if (!$achievements[13]) {
		$achievements[13] = formatTime3(topScoreU($username, "earlyfrostultra"), 7250);
	}

	/*
	 * Achievement 15: Pipe Mastery
	 * Beat Half-Pipe Elite under 1.6 seconds, and Half-Pipe under 1.9 seconds
	 * Award: 0 points
	 */

	if (!$achievements[14]) {
		$achievements[14] = formatTime3(topScoreU($username, "halfpipe2ultra") + topScoreU($username, "halfpipeultra"), 3500);
	}

	return $achievements;
}

function ultraAchievements($username) {
	global $lb_connection;

	$query = pdo_prepare("SELECT `achievement` FROM `ultraachievements` WHERE `username` = :user");
	$query->bind(":user", $username, PDO::PARAM_STR);
	$result = $query->execute();

	$return = array();

	for ($i = 0; $i < 30; $i ++)
		$return[$i] = false;

	while (($row = $result->fetchIdx()) !== false) {
		$return[$row[0]] = true;
	}

	return $return;
}

function ultraAchievementPoints($username) {
	$ratings = [0, 100000, 150000, 10000, 4000, 16000, 550000, 50000, 10000, 0, 10000, 15000, 12000, 6000, 12000];

	$query = pdo_prepare("SELECT `achievement` FROM `ultraachievements` WHERE `username` = :user");
	$query->bind(":user", $username, PDO::PARAM_STR);
	$result = $query->execute();

	$return = 0;

	while (($row = $result->fetchIdx()) !== false) {
		$return += $ratings[$row[0]];
	}

	return $return;
}

/**
 * Gets your top score on a level
 * @param string $username
 * @param string $level
 * @param int $modifiers
 * @param boolean $par
 * @return int
 */
function topScoreU($username, $level, $modifiers = 0, $par = false) {
	global $lb_connection;

	$query = pdo_prepare("SELECT `score` FROM `scores` WHERE `username` = :user AND `level` = :level AND (`modifiers` & :modifiers = :modifiers OR :modifiers = 0) " . ($par ? "AND `score` < (SELECT `qualify` FROM `officiallevels` WHERE `stripped` = `level`)" : "") . " ORDER BY `score` ASC LIMIT 1");
	$query->bind(":user", $username, PDO::PARAM_STR);
	$query->bind(":level", $level, PDO::PARAM_STR);
	$query->bind(":modifiers", $modifiers, PDO::PARAM_INT);
	$result = $query->execute();

	$array = $result->fetchIdx();

	if (!$array)
		return NO_TIME;

	return $array[0];
}

function easterEggsU($username, $game = null) {
	global $lb_connection;

	$query = pdo_prepare("SELECT COUNT(DISTINCT(`level`)) FROM `easteregg` WHERE `username` = :user" . ($game == null ? "" : " AND `level` IN (SELECT `stripped` FROM `officiallevels` WHERE `game` = :game)"));
	$query->bind(":user", $username, PDO::PARAM_STR);
	if ($game != null)
		$query->bind(":game", $game);
	$result = $query->execute();

	return $result->fetchIdx(0);
}

function dumpUltraAchievements($username) {
	$achievements = ultraAchievements($username);
	for ($i = 0; $i < count($achievements); $i ++) {
		if ($achievements[$i] == true)
			echo("UACHIEVEMENT $i\n");
	}
}

function giveUltraAchievement($username, $number, $rating) {
	$query = pdo_prepare("INSERT INTO `ultraachievements` (`username`, `achievement`, `rating`) VALUES (:user, :number, :rating)");
	$query->bind(":user", $username, PDO::PARAM_STR);
	$query->bind(":number", $number, PDO::PARAM_INT);
	$query->bind(":rating", $rating, PDO::PARAM_INT);
	$query->execute();

	echo("ACHIEVEMENTGET $number\n");
}

function formatTime3($time, $par) {
	if ($time == 0 || $time == 5998999)
		return "+99:59.999";
	$time -= $par;
	$neg = $time < 0;
	$time = abs($time);
	$ms = fmod($time, 1000);
	$time = ($time - $ms) / 1000;
	$s  = fmod($time, 60);
	$m  = ($time - $s) / 60;
	return ($neg ? "-" : "+") . str_pad($m, 2, "0", STR_PAD_LEFT) . ":" . str_pad($s, 2, "0", STR_PAD_LEFT) . ":" . str_pad($ms, 3, "0", STR_PAD_RIGHT);
}

function formatPerc100($perc) {
	return round(min(1, $perc) * 100) . "%";
}

?>
