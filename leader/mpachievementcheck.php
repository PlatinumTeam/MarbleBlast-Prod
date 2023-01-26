<?php

define("MAX_ACHIEVEMENTS", 79);

define("SNOW_GLOBE_FLAIR", 143);
define("SUFFIX_SNOWSTORM", 110);
define("TITLE_SANTA_ELF", 111);
define("PRISMATIC_FLAIR", 120);
define("TITLE_CHILLY", 112);
define("TITLE_FROZEN", 113);
define("SNOWBALL_FLAIR", 114);
define("SUFFIX_SNOWMAN", 115);
define("SUFFIX_CLAUS", 116);
define("GINGER_FLAIR", 117);
define("TITLE_CHAMPION_WINTER", 118);
define("TITLE_CLIMBER", 144);

define("GHOST_BUSTER_FLAIR", 96);
define("SPOOKY_FLAIR", 97);
define("SCARY_FLAIR", 98);
define("GHOULISH_FLAIR", 99);
define("CANDY_FLAIR", 100);
define("OCT1_FLAIR", 101);
define("OCT2_FLAIR", 102);
define("THE_HAUNTED_FLAIR", 103);

$allow_nonwebchat = false;
$ignore_keys = true;

// Open the database connection
if (!function_exists("pdo_prepare"))
	require_once("opendb.php");

function calculateMPAchievements($username) {
	list($user) = getPostValues("username");

	if ($username == "") {
		$username = $user;
	}
	$username = getUsername($username);

	$achievements = mpAchievements($username);
	$rating       = userField($username, "rating_mp");
	$games        = userField($username, "rating_mpgames");
	$teamgames    = userField($username, "rating_mpteamgames");
	$winstreak    = userField($username, "mpwinstreak");
	$provisGames  = getServerPref("provisGames");

	/*
		Achievement 1
		Win a Multiplayer match!
	*/

	if (!$achievements[0] && $games > 0) {
		$query =
			pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `custom` = 0 LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount()) {
			mpGiveAchievement($username, 0, 0);
		}
	}

	/*
		Achievement 2
		Win a Multiplayer match in Teams Mode.
	*/

	if (!$achievements[1] && $teamgames > 0) {
		$query =
			pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `team` != -1 AND `players` > 1 AND `custom` = 0 LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount()) {
			mpGiveAchievement($username, 1, 0);
		}
	}

	/*
		Achievement 3
		Become an established player!
	*/

	if (!$achievements[2] && $games >= $provisGames) {
		mpGiveAchievement($username, 2, 0);
	}

	/*
		Achievement 4
		Win a Multiplayer match with 2 points or less as the difference
	*/

	if (!$achievements[3]) {
		$query = pdo_prepare("SELECT * FROM `serverscores` AS `pq` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `custom` = 0 AND
			(SELECT MAX(`score`) FROM `serverscores` WHERE `serverscores`.`key` = `pq`.`key` AND `place` > 1) + 2 >= `score` LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();

		if ($result && $result->rowCount()) {
			mpGiveAchievement($username, 3, 0);
		}
	}

	/*
		Achievement 5
		Win a Multiplayer match by 50 or more points
	*/

	if (!$achievements[4]) {
		$query = pdo_prepare("SELECT * FROM `serverscores` AS `pq` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `custom` = 0 AND
			(SELECT MAX(`score`) FROM `serverscores` WHERE `serverscores`.`key` = `pq`.`key` AND `place` > 1) + 50 <= `score` LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();

		if ($result && $result->rowCount()) {
			mpGiveAchievement($username, 4, 5);
		}
	}

	/*
		Achievement 6
		Win a Multiplayer match on every official level at least once.
	*/

	if (!$achievements[5]) {
		$query =
			pdo_prepare("SELECT * FROM `mplevels` WHERE !(`file` IN (SELECT `level` FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `custom` = 0)) AND `gamemode` = 'hunt' AND `game` != 'PlatinumQuest' LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount() == 0) {
			mpGiveAchievement($username, 5, 0);
		}
	}

	/*
		Achievement 7
		Win 25 FFA matches in Matan Mode
	*/

	if (!$achievements[6]) {
		$query =
			pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `team` = -1 AND `modes` LIKE '%matan%' AND `custom` = 0 LIMIT 26");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount() >= 25) {
			mpGiveAchievement($username, 6, 5);
		}
	}

	/*
		Achievement 8
		Win a FFA match against 7 other people
	*/

	if (!$achievements[7]) {
		$query =
			pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `players` > 7 AND `team` = -1 AND `custom` = 0 LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount()) {
			mpGiveAchievement($username, 7, 10);
		}
	}

	/*
		Achievement 9
		Win 10 matches in a row
	*/

	if (!$achievements[8]) {
		if ($winstreak >= 10) {
			mpGiveAchievement($username, 8, 5);
		}
	}

	/*
		Achievement 10
		Win 25 matches in a row
	*/

	if (!$achievements[9]) {
		if ($winstreak >= 25) {
			mpGiveAchievement($username, 9, 15);
		}
	}

	/*
		Achievement 11
		Get 100 cumulative wins in Team Mode
	*/

	if (!$achievements[10]) {
		$query =
			pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `team` != -1 AND `custom` = 0 LIMIT 100");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount() > 100) {
			mpGiveAchievement($username, 10, 10);
		}
	}

	/*
		Achievement 12
		Get 500 cumulative wins in FFA
	*/

	if (!$achievements[11]) {
		$query =
			pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `team` = -1 AND `custom` = 0 LIMIT 500");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount() >= 500) {
			mpGiveAchievement($username, 11, 15);
		}
	}

	/*
		Achievement 13
		Win a match with all hanicaps on for you alone
	*/

	if (!$achievements[12]) {
		$query = pdo_prepare("SELECT * FROM `serverscores` AS `pq` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `handicap` = 4095 AND `custom` = 0 AND
			(SELECT MAX(`handicap`) FROM `serverscores` WHERE `serverscores`.`key` = `pq`.`key` AND `place` > 1) = 0 LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();

		if ($result && $result->rowCount()) {
			mpGiveAchievement($username, 12, 5);
		}
	}

	/*
		Achievement 14
		Win a Multiplayer match against a top 10% ranked player in FFA
	*/

	$established =
		pdo_prepare("SELECT COUNT(*) FROM `users` WHERE `banned` = 0 AND `guest` = 0 AND `rating_mpgames` >= $provisGames")
			->execute()->fetchIdx(0);
	if (!$achievements[13] && $established >= getServerPref("toptenpref")) {
		$tenPercent = ceil($established / 10);
		$query      = pdo_prepare("SELECT * FROM `serverscores` AS `pq` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `custom` = 0 AND
			(SELECT COUNT(*) FROM `serverscores` WHERE `serverscores`.`key` = `pq`.`key` AND `place` > 1 AND `username` IN
				(SELECT `username` FROM
					(SELECT * FROM `users` WHERE `banned` = 0 AND `guest` = 0 AND `rating_mpgames` >= $provisGames ORDER BY `rating_mp` DESC LIMIT $tenPercent) as `foo`
				)
			) > 0 LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();

		if ($result && $result->rowCount()) {
			mpGiveAchievement($username, 13, 10);
		}
	}

	/*
		Achievement 15
		Get to 1850 in rating points
	*/

	if (!$achievements[14] && $rating >= 1850) {
		mpGiveAchievement($username, 14, 0);
	}

	$redgems     = userField($username, "gems1");
	$yellowgems  = userField($username, "gems2");
	$bluegems    = userField($username, "gems5");
	$totalgems   = $redgems + $yellowgems + $bluegems;
	$totalpoints = ($redgems) + ($yellowgems * 2) + ($bluegems * 5);

	/*
		Achievement 16
		Collect 5,000 lifetime red gems
	*/

	if (!$achievements[15] && $redgems >= 5000) {
		mpGiveAchievement($username, 15, 5);
	}

	/*
		Achievement 17
		Collect 2,000 lifetime yellow gems
	*/

	if (!$achievements[16] && $yellowgems >= 2000) {
		mpGiveAchievement($username, 16, 5);
	}

	/*
		Achievement 18
		Collect 400 lifetime blue gems
	*/

	if (!$achievements[17] && $bluegems >= 400) {
		mpGiveAchievement($username, 17, 10);
	}

	/*
		Achievement 19
		Collect 15,000 lifetime gems
	*/

	if (!$achievements[18] && $totalgems >= 15000) {
		mpGiveAchievement($username, 18, 15);
	}

	/*
		Achievement 20
		Collect 30,000 lifetime points
	*/

	if (!$achievements[19] && $totalpoints >= 30000) {
		mpGiveAchievement($username, 19, 15);
	}

	/*
		Achievement 21
		Win a Multiplayer match in Nukesweeper Revisited
	*/

	if (!$achievements[20] && mpLevelWins($username, "NukesweeperRevisited_Hunt")) {
		mpGiveAchievement($username, 20, 0);
	}

	/*
		Achievement 22
		Win a hard fought FFA on Spires against three other people.
	*/

	if (!$achievements[21] && mpLevelWins($username, "Spires", 1, 3)) {
		mpGiveAchievement($username, 21, 0);
	}

	/*
		Achievement 23
		Win 5 Multiplayer matches in each of Concentric and Core
	*/

	if (!$achievements[22]) {
		if ((mpLevelWins($username, "Core", 5) >= 5) &&
		    (mpLevelWins($username, "Concentric", 5) >= 5) &&
		    (mpLevelWins($username, "Battlecube_Hunt", 5) >= 5) &&
		    (mpLevelWins($username, "BattlecubeRevisited_Hunt", 5) >= 5) &&
		    (mpLevelWins($username, "VortexEffect", 5) >= 5) &&
		    (mpLevelWins($username, "Zenith", 5) >= 5)
		) {
			mpGiveAchievement($username, 22, 15);
		}
	}

	/*
		Achievement 24
		Beat the Gold Score of each Marble Blast Gold level in Multiplayer, FFA only

		Achievement 25
		Get the Platinum Score in every level from Marble Blast Ultra, FFA only
	*/

	if (!$achievements[23] || !$achievements[24]) {
		$query  = pdo_prepare("SELECT * FROM `mplevels`");
		$result = $query->execute();
		if ($result) {
			$qualify23 = true;
			$qualify24 = true;
			while (($row = $result->fetch()) !== false) {
				$level = $row["file"];
				$score = mpTopScore($username, $level);

				if ($row["gamemode"] != "Hunt") {
					continue;
				}

				if ($row["game"] == "Gold" && $score < $row["platinumscore"]) {
					$qualify23 = false;
				}
				if ($row["game"] == "Ultra" && $score < $row["platinumscore"]) {
					$qualify24 = false;
				}
				if (!$qualify23 && !$qualify24) {
					break;
				}
			}

			if ($qualify23 && !$achievements[23]) {
				mpGiveAchievement($username, 23, 5);
			}

			if ($qualify24 && !$achievements[24]) {
				mpGiveAchievement($username, 24, 5);
			}
		}
	}

	/*
		Achievement 26
		Win 30 matches with Matan, Balanced and Glass Modes all enabled.
	*/

	if (!$achievements[25]) {
		$query =
			pdo_prepare("SELECT COUNT(*) FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `custom` = 0 AND `players` > 1 AND `modes` LIKE '%matan glass balanced%'");
		$query->bind(":username", $username);
		if ($query->execute()->fetchIdx(0) >= 30) {
			mpGiveAchievement($username, 25, 15);
		}
	}

	/*
		Achievement 27
		Win a 4v4 Multiplayer match
	*/

	if (!$achievements[26]) {
		$query = pdo_prepare("SELECT COUNT(*) FROM `serverscores` AS `pq` WHERE `username` = :username AND `place` = 1 AND `custom` = 0 AND `players` = 8 AND `team` != -1 AND
			`teams` = 2 AND `teammembers` = 4 AND
			(SELECT `teammembers` FROM `serverscores` WHERE `serverscores`.`key` = `pq`.`key` AND `serverscores`.`team` != `pq`.`team` LIMIT 1) = 4");
		$query->bind(":username", $username);
		if ($query->execute()->fetchIdx(0) >= 30) {
			mpGiveAchievement($username, 26, 0);
		}
	}

	/*
		Achievement 28
		Win on both Sprawl and Horizon in a Team Mode Multiplayer match against two other teams.
	*/

	if (!$achievements[27]) {
		if (mpLevelWins($username, "Sprawl", 1, 2, 3) > 0 && mpLevelWins($username, "Horizon", 1, 2, 3) > 0) {
			mpGiveAchievement($username, 27, 0);
		}
	}

	/*
		Achievement 29
		Get the lowest score on your team by at least half the points than the next person after you.
	*/

	if (!$achievements[28]) {
		// Find the person with the least points who isn't you
		$query = pdo_prepare("SELECT * FROM `serverscores` AS `pq` WHERE `username` = :username AND `team` != -1 AND `players` > 1 AND `custom` = 0 AND
			(SELECT MIN(`score`) FROM `serverscores` WHERE `serverscores`.`key` = `pq`.`key` AND `username` != :username AND `team` = `pq`.`team`) >= `score` * 2 LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount()) {
			// YOU SUCK, HAVE AN ACHIEVEMENT
			mpGiveAchievement($username, 28, 0);
			$achievements[28] = true;
		}
	}

	/*
		Achievement 30
		Get more points than the rest of your team-mates combined.
	*/

	if (!$achievements[29]) {
		// Add all the people's scores below you
		$query = pdo_prepare("SELECT * FROM `serverscores` AS `pq` WHERE `username` = :username AND `team` != -1 AND `players` > 1 AND `custom` = 0 AND
			(SELECT SUM(`score`) FROM `serverscores` WHERE `serverscores`.`key` = `pq`.`key` AND `username` != :username AND `team` = `pq`.`team`) < `score` LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount()) {
			// YOUR TEAM SUCKS, HAVE POINTS
			mpGiveAchievement($username, 29, 5);
			$achievements[29] = true;
		}
	}

	/*
		Achievement 31
		Have more than 12 players playing on Kind of the Marble
	*/

	if (!$achievements[30]) {
		if (mpLevelWins($username, "KingOfTheMarble_Hunt", 1, 12)) {
			mpGiveAchievement($username, 30, 0);
		}
	}

	/*
		Achievement 32
		Lose to more than one guest in a Multiplayer match.
	*/

	if (!$achievements[31]) {
		$query =
			pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` != 1 AND `team` = -1 AND `betterguests` > 1 AND `custom` = 0 LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount()) {
			mpGiveAchievement($username, 31, 0);
		}
	}

	/*
		Achievement 33
		Get more points in a match than all other players combined
	*/

	if (!$achievements[32]) {
		// Add all the people's scores
		$query = pdo_prepare("SELECT * FROM `serverscores` AS `pq` WHERE `username` = :username AND `players` > 3 AND `custom` = 0 AND
			(SELECT SUM(`score`) FROM `serverscores` WHERE `serverscores`.`key` = `pq`.`key` AND `username` != :username) < `score` LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount()) {
			mpGiveAchievement($username, 32, 5);
			$achievements[32] = true;
		}
	}

	/*
		Achievement 34
		Beat another person or team by over 225 points.
	*/

	if (!$achievements[33]) {
		$query = pdo_prepare("SELECT * FROM `serverscores` AS `pq` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `custom` = 0 AND
			(SELECT MAX(`score`) FROM `serverscores` WHERE `serverscores`.`key` = `pq`.`key` AND `place` > 1) + 225 <= `score` LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();

		if ($result && $result->rowCount()) {
			mpGiveAchievement($username, 33, 15);
		}
	}

	/*
		Achievement 35
		Beat a tournament!

		Yeah, we'll have to manually set this one...
	*/


	/*
		Achievement 36
		Defeat IsraeliRD in a match
	*/

	if (!$achievements[35]) {
		$query = pdo_prepare("SELECT * FROM `serverscores` AS `pq` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `custom` = 0 AND
			(SELECT COUNT(*) FROM `serverscores` WHERE `serverscores`.`key` = `pq`.`key` AND `username` = 'IsraeliRD' AND `place` > 1) > 0 LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();

		if ($result && $result->rowCount()) {
			// No way. No. This cannot logically happen.
			// Get out.
			mpGiveAchievement($username, 35, 25);
			$achievements[35] = true;
		}
	}

	/*
		Achievement 37
		How did you get NEGATIVE points in a match?! Are you hacking!
	*/

	if (!$achievements[36]) {
		$query =
			pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `team` = -1 AND `score` < 0 AND `custom` = 0 LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount()) {
			mpGiveAchievement($username, 36, - 5);
		}
	}

	if (getServerPref("wintermode")) {
		/*
			Achievement 38
			Participate in the December 2014 event.
		*/

		if (!$achievements[37]) {
			mpGiveAchievement($username, 37, 0);
		}

		/*
			Achievement 39
			Hit an ice shard floating high above the ground.

			Manually done. See socketserver.extended.php
		*/

		/*
			Achievement 40
			Win a match against at least three other players on the frozen pipes of Skate Battle Royale.
		*/

		if (!$achievements[39]) {
			if (mpLevelWins($username, "skatebattleroyale_xmas", 1, 4)) {
				mpGiveAchievement($username, 39, 0);
			}
		}

		/*
			Achievement 41
			Win a Teams match on the icy plains of Spires.
		*/

		if (!$achievements[40]) {
			$query = pdo_prepare("SELECT `score`, `key` FROM `serverscores` AS `s1` WHERE `username` = :username AND `level` = :level AND `place` = 1 AND `teams` >= 2 AND
			 (SELECT MIN(`teammembers`) FROM `serverscores` WHERE `key` = `s1`.`key`) > 1 ORDER BY `score` DESC LIMIT 1");
			$query->bind(":username", $username);
			$query->bind(":level", "spires_xmas");
			$result = $query->execute();

			if ($result->rowCount()) {
				mpGiveAchievement($username, 40, 0);
			}
		}

		/*
			Achievement 42
			Collect all the snowglobes
		*/

		if (!$achievements[41]) {
			$query = pdo_prepare("SELECT COUNT(*) FROM `snowglobes` WHERE `username` = :username");
			$query->bind(":username", $username);
			$result = $query->execute();

			$count = $result->fetchIdx(0);

			if ($count >= 35) {
				mpGiveAchievement($username, 41, 0);
				awardTitle($username, SNOW_GLOBE_FLAIR);
			}
		}

		/*
			Achievement 43
			Beat any 5 Frozen Scores on the frozen Multiplayer levels, in Versus mode.
		*/

		if (!$achievements[42]) {
			$query =
				pdo_prepare("SELECT COUNT(*) FROM `mplevels` WHERE `gamemode` LIKE '%snowball%' AND (SELECT MAX(`score`) FROM `serverscores` WHERE `players` > 1 AND `place` = 1 AND `username` = :username AND `level` = `mplevels`.`file`) >= `ultimatescore`");
			$query->bind(":username", $username);
			$result = $query->execute();

			if ($result->fetchIdx(0) >= 5) {
				mpGiveAchievement($username, 42, 0);
			}
		}

		/*
			Achievement 62
			Win a Winterfest FFA Match by at least 100 points.
		*/

		if (!$achievements[61]) {
			$query = pdo_prepare("SELECT * FROM `serverscores` AS `pq` WHERE `username` = :username AND `modes` LIKE '%snowball%' AND `place` = 1 AND `players` > 1 AND `custom` = 0 AND
				(SELECT MAX(`score`) FROM `serverscores` WHERE `serverscores`.`key` = `pq`.`key` AND `place` > 1) + 100 <= `score` LIMIT 1");
			$query->bind(":username", $username);
			$result = $query->execute();

			if ($result && $result->rowCount()) {
				mpGiveAchievement($username, 61, 15);
			}
		}

		/*
			Achievement 63
			Launch 3,000 Snowballs.

			Achievement 64
			Hit other players a total of 500 times with Snowballs
		*/

		if (!$achievements[62] || !$achievements[63]) {
			$query = pdo_prepare("SELECT * FROM `snowballs` WHERE `username` = :username");
			$query->bind(":username", $username);
			$result = $query->execute();

			if ($result->rowCount()) {
				$row = $result->fetch();
				$count = $row["count"];
				$hits  = $row["hits"];

				if (!$achievements[62] && $count >= 3000) {
					mpGiveAchievement($username, 62, 0);
				}
				if (!$achievements[63] && $hits >= 500) {
					awardTitle($username, SUFFIX_SNOWSTORM);
					mpGiveAchievement($username, 63, 0);
				}
			}
		}

		/*
			Achievement 65
			Find a hidden Santa!
			Trigger Range: 8101 -> 8129
		*/

		if (!$achievements[64]) {
			if (mpHasEventTriggerRange($username, 8101, 8130) >= 1) {
				mpGiveAchievement($username, 64, 0);
				$achievements[64] = true;
			}
		}

		/*
			Achievement 66
			Find 7 hidden Santas!
			Trigger Range: 8101 -> 8129
		*/

		if (!$achievements[65]) {
			if (mpHasEventTriggerRange($username, 8101, 8130) >= 7) {
				mpGiveAchievement($username, 65, 0);
				$achievements[65] = true;
			}
		}

		/*
			Achievement 67
			Find 14 hidden Santas!
			Trigger Range: 8101 -> 8129
		*/

		if (!$achievements[66]) {
			if (mpHasEventTriggerRange($username, 8101, 8130) >= 14) {
				awardTitle($username, TITLE_SANTA_ELF);
				mpGiveAchievement($username, 66, 0);
				$achievements[66] = true;
			}
		}

		/*
			Achievement 68
			Find all the hidden Santas! (30)
			Trigger Range: 8101 -> 8129
		*/

		if (!$achievements[67]) {
			if (mpHasEventTriggerRange($username, 8101, 8130) >= 30) {
				awardTitle($username, PRISMATIC_FLAIR);
				mpGiveAchievement($username, 67, 0);
				$achievements[67] = true;
			}
		}

		/*
			Achievement 69
			Beat at least 20 Chilly Scores on the Frozen Multiplayer levels
		*/

		if (!$achievements[68]) {
			$query = pdo_prepare("SELECT COUNT(*) FROM `mplevels` WHERE `gamemode` LIKE '%snowball%' AND (SELECT MAX(`score`) FROM `serverscores` WHERE `username` = :username AND `level` = `mplevels`.`file`) >= `platinumscore`");
			$query->bind(":username", $username);
			$result = $query->execute();

			if ($result->fetchIdx(0) >= 20) {
				awardTitle($username, TITLE_CHILLY);
				mpGiveAchievement($username, 68, 0);
			}
		}

		/*
			Achievement 70
			Beat at least 20 Frozen Scores on the Frozen Multiplayer levels
		*/

		if (!$achievements[69]) {
			$query = pdo_prepare("SELECT COUNT(*) FROM `mplevels` WHERE `gamemode` LIKE '%snowball%' AND (SELECT MAX(`score`) FROM `serverscores` WHERE `username` = :username AND `level` = `mplevels`.`file`) >= `ultimatescore`");
			$query->bind(":username", $username);
			$result = $query->execute();

			if ($result->fetchIdx(0) >= 20) {
				awardTitle($username, TITLE_FROZEN);
				mpGiveAchievement($username, 69, 0);
			}
		}

		/*
			Achievement 71
			Win a Snowball-Only Teams match on Snow Brawl where there are at least 2 teams
		*/

		if (!$achievements[70]) {
			$query = pdo_prepare("SELECT COUNT(*) FROM `serverscores` WHERE `username` = :username AND `level` = :level AND `modes` LIKE '%snowball%' AND `modes` LIKE '%snowballsonly%' AND `place` = 1 AND `teams` >= 2 ORDER BY `score` DESC");
			$query->bind(":username", $username);
			$query->bind(":level", "SnowBrawl");
			$result = $query->execute();

			if ($result->fetchIdx(0) > 0) {
				awardTitle($username, SNOWBALL_FLAIR);
				mpGiveAchievement($username, 70, 0);
			}
		}

		/*
			Achievement 72
			Find all the hidden presents in Wintry Village
			Trigger Range: 1020 -> 1042
		*/

		if (!$achievements[71]) {
			if (mpHasEventTriggerRange($username, 1020, 1042) >= 23) {
				mpGiveAchievement($username, 71, 0);
				$achievements[71] = true;
			}
		}

		/*
			Achievement 73
			Find all the snowmen in Wintry Village
			Trigger Range: 1000 -> 1019
		*/

		if (!$achievements[72]) {
			if (mpHasEventTriggerRange($username, 1000, 1019) >= 20) {
				awardTitle($username, SUFFIX_SNOWMAN);
				mpGiveAchievement($username, 72, 0);
				$achievements[72] = true;
			}
		}

		/*
			Achievement 74
			Visit all of the chimneys in Wintry Village
			Trigger Range: 1043 -> 1074
		*/

		if (!$achievements[73]) {
			if (mpHasEventTriggerRange($username, 1043, 1074) >= 32) {
				awardTitle($username, SUFFIX_CLAUS);
				mpGiveAchievement($username, 73, 0);
				$achievements[73] = true;
			}
		}

		/*
			Achievement 75
			Find all 10 hidden Gingerbread Men. Look for Santa's stolen hat!
			Trigger Range: 1075 -> 1084
		*/

		if (!$achievements[74]) {
			if (mpHasEventTriggerRange($username, 1075, 1084) >= 10) {
				awardTitle($username, GINGER_FLAIR);
				mpGiveAchievement($username, 74, 0);
				$achievements[74] = true;
			}
		}

		/*
			Achievement 76
			Won a Snowball-Only round of Wintry Village
		*/

		if (!$achievements[75]) {
			$query = pdo_prepare("SELECT COUNT(*) FROM `serverscores` WHERE `username` = :username AND `level` = :level AND `modes` LIKE '%snowball%' AND `modes` LIKE '%snowballsonly%' AND `place` = 1 AND `players` > 1 ORDER BY `score` DESC");
			$query->bind(":username", $username);
			$query->bind(":level", "WintryVillage");
			$result = $query->execute();

			if ($result->fetchIdx(0) > 0) {
				mpGiveAchievement($username, 75, 0);
			}
		}

		/*
			Achievement 80
			Identify three points of interest within Winter's Rage.
			IDs: 1102, 1103, 1104.
		 */
		if (!$achievements[79] && mpHasEventTriggerRange($username, 1102, 1104) >= 3) {
			mpGiveAchievement($username, 79, 0);
			$achievements[79] = true;
		}

		/*
			Achievement 80
			IDs: 1102, 1103, 1104.
		 */
		if (!$achievements[80] && mpHasEventTrigger($username, 1105)) {
			awardTitle($username, TITLE_CLIMBER);
			mpGiveAchievement($username, 80, 0);
			$achievements[80] = true;
		}

		/*
			Achievement 81
			Check if player has trigger 1106, 1107, 1108, 1109, 1110, and 1111.
		 */
		if (!$achievements[81] && mpHasEventTriggerRange($username, 1106, 1111) >= 6) {
			mpGiveAchievement($username, 81, 0);
			$achievements[81] = true;
		}

		/*
			Achievement 77
			Complete all of the Winterfest Exploration Achievements
			IDs: 39, 42, 67 (only 20 santas, not all of them - that's hard), 72, 73, 74, 75
		*/

		if (!$achievements[76]) {
			if ($achievements[38] &&
			    $achievements[41] &&
			    $achievements[66] &&
			    $achievements[71] &&
			    $achievements[72] &&
			    $achievements[73] &&
			    $achievements[74] &&
			    $achievements[79] &&
			    $achievements[80] &&
			    $achievements[81]
			) {
				mpGiveAchievement($username, 76, 0);
				$achievements[76] = true;
			}
		}

		/*
			Achievement 78
			Complete all of the Winterfest Gameplay Achievements
			IDs: 40, 41, 43, 62, 63, 64, 69, 70, 71, 76
		*/

		if (!$achievements[77]) {
			if ($achievements[39] &&
			    $achievements[40] &&
			    $achievements[42] &&
			    $achievements[61] &&
			    $achievements[62] &&
			    $achievements[63] &&
			    $achievements[68] &&
			    $achievements[69] &&
			    $achievements[70] &&
			    $achievements[75]
			) {
				mpGiveAchievement($username, 77, 0);
				$achievements[77] = true;
			}
		}

		/*
			Achievement 79
			Complete all of the Winterfest Achievements
			IDs: 38, 77, 78
		*/

		if (!$achievements[78]) {
			if ($achievements[37] &&
			    $achievements[76] &&
			    $achievements[77]
			) {
				awardTitle($username, TITLE_CHAMPION_WINTER);
				mpGiveAchievement($username, 78, 0);
				$achievements[78] = true;
			}
		}

	}

	if (getServerPref("spookyevent")) {
		/*
		    Achievement 44
			Log in between Oct. 10 and Nov. 10
		*/

		if (!$achievements[43]) {
			mpGiveAchievement($username, 43, 0);
			$achievements[43] = true;
		}

		/*
			Achievement 45
			Win a round while Ghost Hunt and Ratings are enabled - 2 or more players

			Achievement 46
			Win 10 rounds while Ghost Hunt and Ratings are enabled - 2 or more players
		*/

		if (!$achievements[44] || !$achievements[45]) {
			$query =
				pdo_prepare("SELECT COUNT(*) FROM `serverscores` WHERE `modes` LIKE '%ghosts%' AND `players` > 1 AND `place` = 1 AND `custom` = 0 AND `username` = :username");
			$query->bind(":username", $username);
			$result = $query->execute();

			// # of levels beaten
			$count = $result->fetchIdx(0);

			if (!$achievements[44] && $count > 0) {
				mpGiveAchievement($username, 44, 0);
				$achievements[44] = true;
			}
			if (!$achievements[45] && $count >= 10) {
				awardTitle($username, GHOST_BUSTER_FLAIR);
				mpGiveAchievement($username, 45, 0);
				$achievements[45] = true;
			}
		}

		/*
			Achievement 47
			Play and finish one game on each of the halloween multiplayer levels with ratings enabled and 2 or more players.
		*/

		if (!$achievements[46]) {
			$query =
				pdo_prepare("SELECT COUNT(*) FROM `mplevels` WHERE `file` NOT IN (SELECT `level` FROM `serverscores` WHERE `username` = :username AND `modes` LIKE '%spooky%' AND `custom` = 0 AND `players` > 1 GROUP BY `level`) AND `gamemode` LIKE '%spooky%'");
			$query->bind(":username", $username);
			$result = $query->execute();

			if ($result->fetchIdx(0) == 0) {
				mpGiveAchievement($username, 46, 0);
				$achievements[46] = true;
			}
		}

		/*
			Achievement 48
			Play and beat the spooky score on at least 5 halloween multiplayer levels with ratings enabled. DO NOT NEED 2 OR MORE PLAYERS!
		*/

		if (!$achievements[47]) {
			$query =
				pdo_prepare("SELECT COUNT(*) FROM `mplevels` WHERE `gamemode` LIKE '%spooky%' AND (SELECT MAX(`score`) FROM `serverscores` WHERE `username` = :username AND `level` = `mplevels`.`file`) >= `platinumscore`");
			$query->bind(":username", $username);
			$result = $query->execute();

			if ($result->fetchIdx(0) >= 5) {
				awardTitle($username, SPOOKY_FLAIR);
				mpGiveAchievement($username, 47, 0);
				$achievements[47] = true;
			}
		}

		/*
			Achievement 49
			Play and beat the scary score on at least 5 halloween multiplayer levels with ratings enabled. DO NOT NEED 2 OR MORE PLAYERS!
		*/

		if (!$achievements[48]) {
			$query =
				pdo_prepare("SELECT COUNT(*) FROM `mplevels` WHERE `gamemode` LIKE '%spooky%' AND (SELECT MAX(`score`) FROM `serverscores` WHERE `username` = :username AND `level` = `mplevels`.`file`) >= `ultimatescore`");
			$query->bind(":username", $username);
			$result = $query->execute();

			if ($result->fetchIdx(0) >= 5) {
				awardTitle($username, SCARY_FLAIR);
				mpGiveAchievement($username, 48, 0);
				$achievements[48] = true;
			}
		}

		/*
			Achievement 50
			Check if player has Trigger ID 9001
		*/

		if (!$achievements[49]) {
			if (mpHasEventTrigger($username, 9001)) {
				mpGiveAchievement($username, 49, 0);
				$achievements[49] = true;
			}
		}

		/*
			Achievement 51
			Check if player has Trigger ID 8080 - 8089 INCLUSIVE
		*/

		if (!$achievements[50]) {
			if (mpHasEventTriggerRange($username, 8080, 8089) >= 10) {
				awardTitle($username, GHOULISH_FLAIR);
				mpGiveAchievement($username, 50, 0);
				$achievements[50] = true;
			}
		}

		/*
			Achievement 52
			Snowglobes except candies. What. We need to code this - use candy bucket for now. One on every level?? DO THIS ACHIEVEMENT LAST
		*/

		if (!$achievements[51]) {
			$query = pdo_prepare("SELECT COUNT(*) FROM `eventcandy` WHERE `username` = :username");
			$query->bind(":username", $username);
			$result = $query->execute();

			$count = $result->fetchIdx(0);

			if ($count >= 15) {
				mpGiveAchievement($username, 51, 0);
				awardTitle($username, CANDY_FLAIR);
			}
		}

		/*
			Achievement 53
			Total gem counts for spooky rated matches should be > 2000
		*/

		if (!$achievements[52]) {
			$query =
				pdo_prepare("SELECT SUM(`gems1`) + SUM(`gems2`) + SUM(`gems5`) FROM `serverscores` WHERE `modes` LIKE '%spooky%' AND `players` > 1 AND `custom` = 0 AND `username` = :username");
			$query->bind(":username", $username);
			$result = $query->execute();

			if ($result->fetchIdx(0) >= 2000) {
				mpGiveAchievement($username, 52, 0);
				$achievements[52] = true;
			}
		}

		/*
			Achievement 54
			Complete the following achievements: 44-53
		*/

		if (!$achievements[53]) {
			if ($achievements[43] &&
				$achievements[44] &&
				$achievements[45] &&
				$achievements[46] &&
				$achievements[47] &&
				$achievements[48] &&
				$achievements[49] &&
				$achievements[50] &&
				$achievements[51] &&
				$achievements[52]
			) {
				awardTitle($username, OCT1_FLAIR);
				mpGiveAchievement($username, 53, 0);
				$achievements[53] = true;
			}
		}

		/*
			Achievement 55
			Check if player has Trigger ID 1500 - 1515 INCLUSIVE
		*/

		if (!$achievements[54]) {
			if (mpHasEventTriggerRange($username, 1500, 1515) >= 16) {
				mpGiveAchievement($username, 54, 0);
				$achievements[54] = true;
			}
		}

		/*
			Achievement 56
			Check if player has Trigger ID 1700 - 1730 INCLUSIVE
		*/

		if (!$achievements[55]) {
			if (mpHasEventTriggerRange($username, 1700, 1730) >= 31) {
				mpGiveAchievement($username, 55, 0);
				$achievements[55] = true;
			}
		}

		/*
			Achievement 57
			Check if player has Trigger ID 1650 - 1660 INCLUSIVE
		*/

		if (!$achievements[56]) {
			if (mpHasEventTriggerRange($username, 1650, 1660) >= 11) {
				mpGiveAchievement($username, 56, 0);
				$achievements[56] = true;
			}
		}

		/*
			Achievement 58
			Check if player has Trigger ID 1600 - 1611 INCLUSIVE
		*/

		if (!$achievements[57]) {
			if (mpHasEventTriggerRange($username, 1600, 1611) >= 12) {
				mpGiveAchievement($username, 57, 0);
				$achievements[57] = true;
			}
		}

		/*
			Achievement 59
			Check if player has Trigger ID 1750
		*/

		if (!$achievements[58]) {
			if (mpHasEventTrigger($username, 1750)) {
				mpGiveAchievement($username, 58, 0);
				$achievements[58] = true;
			}
		}

		/*
			Achievement 60
			Complete the following achievements: 55-59
		*/

		if (!$achievements[59]) {
			if ($achievements[54] &&
				$achievements[55] &&
				$achievements[56] &&
				$achievements[57] &&
				$achievements[58]
			) {
				awardTitle($username, OCT2_FLAIR);
				mpGiveAchievement($username, 59, 0);
				$achievements[59] = true;
			}
		}

		/*
			Achievement 61
			Complete the following achievements: 54 60
		*/

		if (!$achievements[60]) {
			if ($achievements[53] &&
				$achievements[59]
			) {
				awardTitle($username, THE_HAUNTED_FLAIR);
				mpGiveAchievement($username, 60, 0);
				$achievements[60] = true;
				postNotify("achievement", $username, 0, "The Haunted");
			}
		}
	}
}

function mpAchievementProgress($username) {
	$achievements = mpAchievements($username);
	$rating = userField($username, "rating_mp");
	$games = userField($username, "rating_mpgames");
	$teamgames = userField($username, "rating_mpteamgames");
	$winstreak = userField($username, "mpwinstreak");
	$provisGames = getServerPref("provisGames");

	for ($i = 0; $i < count($achievements); $i ++)
		if ($achievements[$i] == true)
			$achievements[$i] = "100%";

	/*
		Achievement 1
		Win a Multiplayer match!
	*/

	if (!$achievements[0] && $games > 0) {
		$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `players` > 1 LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount())
			$achievements[0] = "100%";
	}

	/*
		Achievement 2
		Win a Multiplayer match in Teams Mode.
	*/

	if (!$achievements[1] && $teamgames > 0) {
		$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `team` != -1 AND `players` > 1 LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount())
			$achievements[1] = "100%";
	}

	/*
		Achievement 3
		Become an established player!
	*/

	if (!$achievements[2] && $games >= $provisGames) {
		$achievements[2] = formatPerc(($games * 100) / $provisGames);
	}

	/*
		Achievement 4
		Win a Multiplayer match with 2 points or less as the difference

		Achievement 5
		Win a Multiplayer match by 50 or more points

		Achievement 13
		Win a match with all hanicaps on for you alone

		Achievement 14
		Win a Multiplayer match against a top 10 ranked player in FFA

		Achievement 27
		Win a 4v4 Multiplayer match

		Achievement 29
		Get the lowest score on your team by at least half the points than the next person after you.

		Achievement 30
		Get more points than the rest of your team-mates combined.

		Achievement 33
		Get more points in a match than all other players combined

		Achievement 34
		Beat another person or team by over 225 points.

		Achievement 36
		Defeat IsraeliRD in a match
	*/
	// Oh boy!

	if (!$achievements[3] || !$achievements[4] || !$achievements[12] || !$achievements[13] || !$achievements[26] || !$achievements[28] || !$achievements[29] || !$achievements[32] || !$achievements[33] || !$achievements[35]) {
		$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `players` > 1");
		$query->bind(":username", $username);
		$result = $query->execute();

		if ($result->rowCount())
			while (($row = $result->fetch()) !== false) {
				$score = $row["score"];
				$key = $row["key"];
				$players = $row["players"];
				$level = $row["level"];
				$handicap = $row["handicap"];
				$team = $row["team"];
				$teammembers = $row["teammembers"];
				$teams = $row["teams"];

				if (!$achievements[3] && $score >= 2) {
					$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` != :username AND `key` = :key AND `level` = :level AND ABS(`score` - $score) <= 2 LIMIT 1");
					$query->bind(":username", $username);
					$query->bind(":key", $key);
					$query->bind(":level", $level);
					if ($query->execute()->rowCount()) {
						$achievements[3] = "100%";
					}
				}
				if (!$achievements[4] && $score >= 50) {
					$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` != :username AND `key` = :key AND `level` = :level AND `score` - $score < 50 LIMIT 1");
					$query->bind(":username", $username);
					$query->bind(":key", $key);
					$query->bind(":level", $level);
					if (!$query->execute()->rowCount()) {
						$achievements[4] = "100%";
					}
				}
				if (!$achievements[12] && $handicap == 1023) {
					$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` != :username AND `key` = :key AND `level` = :level AND `handicap` != 0 LIMIT 1");
					$query->bind(":username", $username);
					$query->bind(":key", $key);
					$query->bind(":level", $level);
					if (!$query->execute()->rowCount()) {
						$achievements[12] = "100%";
					}
				}
				if (!$achievements[13]) {
					$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` != :username AND `key` = :key AND `level` = :level AND `username` IN (SELECT `username` FROM (SELECT * FROM `users` WHERE `banned` = 0 AND `guest` = 0 AND `rating_mpgames` >= $provisGames ORDER BY `rating_mp` DESC LIMIT 10) as `foo`) LIMIT 1");
					$query->bind(":username", $username);
					$query->bind(":key", $key);
					$query->bind(":level", $level);
					if ($query->execute()->rowCount()) {
						$achievements[13] = "100%";
					}
				}
				if (!$achievements[26] && $teammembers == 4 && $teams == 2) {
					$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` != :username AND `key` = :key AND `level` = :level AND `team` != :team LIMIT 1");
					$query->bind(":username", $username);
					$query->bind(":key", $key);
					$query->bind(":level", $level);
					$query->bind(":team", $team);
					$result_ = $query->execute();
					if ($result_->rowCount()) {
						while (($array = $result_->fetch()) !== false) {
							$members = $array["teammembers"];
							$achievements[26] = formatPerc($members / 0.04);
						}
					}
				}
				if (!$achievements[28] && $team != -1) {
					// Find the person with the least points who isn't you
					$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` != :username AND `key` = :key AND `level` = :level AND `team` = :team ORDER BY `score` ASC LIMIT 1");
					$query->bind(":username", $username);
					$query->bind(":key", $key);
					$query->bind(":level", $level);
					$query->bind(":team", $team);
					$result_ = $query->execute();
					if ($result_ && ($array = $result_->fetch()) !== false) {
						$points = $array["score"];
						if ($score * 2 < $points) {
							// YOU SUCK, HAVE AN ACHIEVEMENT
							$achievements[28] = "100%";
						}
					}
				}
				if (!$achievements[29] && $team != -1) {
					// Add all the people's scores below you
					$query = pdo_prepare("SELECT SUM(`score`) FROM `serverscores` WHERE `username` != :username AND `key` = :key AND `level` = :level AND `team` = :team");
					$query->bind(":username", $username);
					$query->bind(":key", $key);
					$query->bind(":level", $level);
					$query->bind(":team", $team);
					$result_ = $query->execute();
					if ($result_ && ($array = $result_->fetchIdx()) !== false) {
						$points = $array[0];
						if ($score > $points) {
							// YOUR TEAM SUCKS, HAVE POINTS
							$achievements[29] = "100%";
						}
					}
				}
				if (!$achievements[32] && $team != -1 && $players > 3) {
					// Add all the people's scores
					$query = pdo_prepare("SELECT SUM(`score`) FROM `serverscores` WHERE `username` != :username AND `key` = :key AND `level` = :level");
					$query->bind(":username", $username);
					$query->bind(":key", $key);
					$query->bind(":level", $level);
					$result_ = $query->execute();
					if ($result_ && ($array = $result_->fetchIdx()) !== false) {
						$points = $array[0];
						if ($score > $points) {
							$achievements[32] = "100%";
						}
					}
				}
				if (!$achievements[33] && $score >= 225) {
					$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` != :username AND `key` = :key AND `level` = :level AND `score` - $score < 225 LIMIT 1");
					$query->bind(":username", $username);
					$query->bind(":key", $key);
					$query->bind(":level", $level);
					if (!$query->execute()->rowCount()) {
						$achievements[33] = "100%";
					}
				}
				if (!$achievements[35]) {
					$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = 'IsraeliRD' AND `key` = :key AND `level` = :level AND `place` != 1 LIMIT 1");
					$query->bind(":key", $key);
					$query->bind(":level", $level);
					if ($query->execute()->rowCount()) {
						// No way. No. This cannot logically happen.
						// Get out.
						$achievements[35] = "100%";
					}
				}

				if ($achievements[3] && $achievements[4] && $achievements[12] && $achievements[13] && $achievements[26] && $achievements[28] && $achievements[29] && $achievements[32] && $achievements[33] && $achievements[35])
					break;
			}
	}

	/*
		Achievement 6
		Win a Multiplayer match on every official level at least once.
	*/

	if (!$achievements[5]) {
		$query = pdo_prepare("SELECT * FROM `mplevels` WHERE !(`file` IN (SELECT `level` FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `players` > 1))");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result)
			$achievements[5] = formatPerc((6 - $result->rowCount()) / 0.06);
	}

	/*
		Achievement 7
		Win 25 FFA matches in Matan Mode
	*/

	if (!$achievements[6]) {
		$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `team` = -1 AND `modes` LIKE '%matan' LIMIT 26");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result) {
			$achievements[6] = formatPerc($result->rowCount() / 0.25);
		}
	}

	/*
		Achievement 8
		Win a FFA match against 7 other people
	*/

	if (!$achievements[7]) {
		$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `players` > 7 AND `team` = -1 LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount()) {
			$achievements[7] = "100%";
		}
	}

	/*
		Achievement 9
		Win 10 matches in a row
	*/

	if (!$achievements[8]) {
		$achievements[8] = formatPerc($winstreak / 0.10);
	}

	/*
		Achievement 10
		Win 25 matches in a row
	*/

	if (!$achievements[9]) {
		$achievements[9] = formatPerc($winstreak / 0.25);
	}

	/*
		Achievement 11
		Get 100 cumulative wins in Team Mode
	*/

	if (!$achievements[10]) {
		$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `team` != -1 LIMIT 100");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result) {
			$achievements[10] = formatPerc($result->rowCount() / 1.00);
		}
	}

	/*
		Achievement 12
		Get 500 cumulative wins in FFA
	*/

	if (!$achievements[11]) {
		$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `team` = -1 LIMIT 500");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result) {
			$achievements[11] = formatPerc($result->rowCount() / 5.00);
		}
	}

	/*
		Achievement 15
		Get to 1700 in rating points
	*/

	if (!$achievements[14]) {
		$achievements[14] = formatPerc(($rating - 1500) / 2.00);
	}

	$redgems     = userField($username, "gems1");
	$yellowgems  = userField($username, "gems2");
	$bluegems    = userField($username, "gems5");
	$totalgems   = $redgems + $yellowgems + $bluegems;
	$totalpoints = ($redgems) + ($yellowgems * 2) + ($bluegems * 5);

	/*
		Achievement 16
		Collect 5,000 lifetime red gems
	*/

	if (!$achievements[15]) {
		$achievements[15] = formatPerc($redgems / 50.00);
	}

	/*
		Achievement 17
		Collect 2,000 lifetime yellow gems
	*/

	if (!$achievements[16]) {
		$achievements[16] = formatPerc($yellowgems / 20.00);
	}

	/*
		Achievement 18
		Collect 400 lifetime blue gems
	*/

	if (!$achievements[17]) {
		$achievements[17] = formatPerc($bluegems / 4.00);
	}

	/*
		Achievement 19
		Collect 15,000 lifetime gems
	*/

	if (!$achievements[18]) {
		$achievements[18] = formatPerc($totalgems / 150.00);
	}

	/*
		Achievement 20
		Collect 30,000 lifetime points
	*/

	if (!$achievements[19]) {
		$achievements[19] = formatPerc($totalpoints / 300.00);
	}

	/*
		Achievement 21
		Win a Multiplayer match in Nukesweeper Revisited
	*/

	if (!$achievements[20] && mpLevelWins($username, "NukesweeperRevisited_Hunt")) {
		$achievements[20] = "100%";
	}

	/*
		Achievement 22
		Win a hard fought FFA on Spires against three other people.
	*/

	if (!$achievements[21] && mpLevelWins($username, "Spires", 1, 3)) {
		$achievements[21] = "100%";
	}

	/*
		Achievement 23
		Win 5 Multiplayer matches in each of Concentric and Core
	*/

	if (!$achievements[22]) {
		$achievements[22] = formatPerc((mpLevelWins($username, "Core", 5) +
												  mpLevelWins($username, "Concentric", 5) +
												  mpLevelWins($username, "Battlecube_Hunt", 5) +
												  mpLevelWins($username, "BattlecubeRevisited", 5) +
												  mpLevelWins($username, "VortexEffect", 5) +
												  mpLevelWins($username, "Zenith", 5)) / 0.30);
	}

	/*
		Achievement 24
		Beat the Gold Score of each Marble Blast Gold level in Multiplayer, FFA only

		Achievement 25
		Get the Platinum Score in every level from Marble Blast Ultra, FFA only
	*/

	if (!$achievements[23] || !$achievements[24]) {
		$query = pdo_prepare("SELECT * FROM `mplevels`");
		$result = $query->execute();
		if ($result) {
			$qualify23 = true;
			$qualify24 = true;
			$total23 = 0;
			$total24 = 0;
			while (($row = $result->fetch()) !== false) {
				$level = $row["file"];
				$score = mpTopScore($username, $level);

				if ($row["game"] == "Gold") {
					if ($score < $row["platinumscore"])
						$qualify23 = false;
					else
						$total23 ++;
				}
				if ($row["game"] == "Ultra") {
					if ($score < $row["platinumscore"])
						$qualify24 = false;
					else
						$total24 ++;
				}
			}

			echo("$total23 $total24");

			if (!$achievements[23])
				$achievements[23] = formatPerc($total23 / 0.03);
			if (!$achievements[24])
				$achievements[24] = formatPerc($total24 / 0.21);
		}
	}

	/*
		Achievement 26
		Win 30 matches with Matan, Balanced and Glass Modes all enabled.
	*/

	if (!$achievements[25]) {
		$query = pdo_prepare("SELECT COUNT(*) FROM `serverscores` WHERE `username` = :username AND `place` = 1 AND `players` > 1 AND `modes` = 'matan glass balanced'");
		$query->bind(":username", $username);
		$achievements[25] = formatPerc($query->execute()->fetchIdx(0) / 0.30);
	}

	/*
		Achievement 28
		Win on both Sprawl and Horizon in a Team Mode Multiplayer match against two other teams.
	*/

	if (!$achievements[27]) {
		if (mpLevelWins($username, "Sprawl", 20, 2, 3) > 0 && mpLevelWins($username, "Horizon", 20, 2, 3) > 0) {
			$achievements[27] = "100%";
		}
	}

	/*
		Achievement 31
		Have more than 12 players playing on Kind of the Marble
	*/

	if (!$achievements[30]) {
		if (mpLevelWins($username, "KingOfTheMarble_Hunt", 1, 12)) {
			$achievements[30] = "100%";
		}
	}

	/*
		Achievement 32
		Lose to more than one guest in a Multiplayer match.
	*/

	if (!$achievements[31]) {
		$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `place` != 1 AND `team` = -1 AND `betterguests` > 1 LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount()) {
			$achievements[31] = "100%";
		}
	}

	/*
		Achievement 37
		How did you get NEGATIVE points in a match?! Are you hacking!
	*/

	if (!$achievements[36]) {
		$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `username` = :username AND `team` = -1 AND `score` < 0 LIMIT 1");
		$query->bind(":username", $username);
		$result = $query->execute();
		if ($result && $result->rowCount()) {
			$achievements[36] = "100%";
		}
	}

	return $achievements;
}

function mpGiveAchievement($username, $achievement, $rating) {
	global $lb_connection;

	$achievement = (int)$achievement;
	$rating      = (int)$rating;

	$query = pdo_prepare("INSERT INTO `mpachievements` (`username`, `achievement`, `rating`) VALUES (:username, :achievement, :rating)");
	$query->bind(":username", $username);
	$query->bind(":achievement", $achievement);
	$query->bind(":rating", $rating);
	$query->execute();

	if ($rating) {
	   $query = pdo_prepare("UPDATE `users` SET `rating_mp` = `rating_mp` + :rating WHERE `username` = :username");
	   $query->bind(":rating", $rating);
	   $query->bind(":username", $username);
	   $query->execute();
	}
}

function mpAchievements($username) {
	global $lb_connection;

	$query = pdo_prepare("SELECT `achievement` FROM `mpachievements` WHERE `username` = :username");
	$query->bind(":username", $username);
	$result = $query->execute();

	$return = array();

	for ($i = 0; $i < MAX_ACHIEVEMENTS; $i ++)
		$return[$i] = false;

	while (($row = $result->fetchIdx()) !== false) {
		$return[$row[0]] = true;
	}

	return $return;
}

function mpTopScore($username, $level) {
	$query = pdo_prepare("SELECT `score` FROM `serverscores` WHERE `username` = :username AND `level` = :level ORDER BY `score` DESC LIMIT 1");
	$query->bind(":username", $username);
	$query->bind(":level", $level);
	$result = $query->execute();

	$array = $result->fetchIdx();

	if (!$array)
		return 0;

	return $array[0];
}

function mpLevelWins($username, $level, $limit = 1, $minplayers = 2, $teams = 0) {
	$query = pdo_prepare("SELECT `score` FROM `serverscores` WHERE `username` = :username AND `level` = :level AND `players` >= :minplayers AND `place` = 1 AND `teams` = :teams ORDER BY `score` DESC LIMIT $limit");
	$query->bind(":username", $username);
	$query->bind(":level", $level);
	$query->bind(":minplayers", $minplayers);
	$query->bind(":teams", $teams);
	$result = $query->execute();

	if (!$result)
		return 0;

	return $result->rowCount();
}

function mpDumpAchievements($username) {
	$achievements = mpAchievements($username);
	for ($i = 0; $i < count($achievements); $i ++) {
		if ($achievements[$i] == true)
			echo("MPACHIEVEMENT $i\n");
	}
}

function mpHasEventTrigger($username, $id) {
	$query = pdo_prepare("SELECT COUNT(*) FROM `eventtriggers` WHERE `triggerID` = :id AND `username` = :username");
	$query->bind(":username", $username);
	$query->bind(":id", $id);
	$result = $query->execute();

	return ($result->fetchIdx(0) > 0);
}

function mpHasEventTriggerRange($username, $idStart, $idEnd) {
	$query = pdo_prepare("SELECT COUNT(*) FROM `eventtriggers` WHERE `triggerID` >= :start AND `triggerID` <= :end AND `username` = :username");
	$query->bind(":username", $username);
	$query->bind(":start", $idStart);
	$query->bind(":end", $idEnd);
	$result = $query->execute();

	return $result->fetchIdx(0);
}

?>