<?php
define("SPEEDBOOSTAIR_FLAIR", 62);
define("THEASLEEP_FLAIR", 76);
define("SPACESHIP_FLAIR", 124);
define("TRAPLAUNCH_FLAIR", 105);

$allow_nonwebchat = true;
$ignore_keys = true;

// Open the database connection
require_once("opendb.php");

function checkAchievements($username = null) {
	// Post the score
	if ($username == null)
		$username = getPostValue("username");

	$achievements = achievements($username);
	$eggs = easterEggs($username, "Platinum");
	$rating = userField($username, "rating");
	$ratingStart = $rating;
	$ratingAch = userField($username, "rating_achievements");
	$ratingMBG = userField($username, "rating_mbg");
	$ratingMBP = userField($username, "rating_mbp");
	$ratingLBCustom = userField($username, "rating_custom");

	/*
		Achievement 1
		Find any Easter Egg
		Award: 10,000 Points
		Several easy eggs to choose from = low score
	*/

	if (!$achievements[0] && $eggs > 0) {
		giveAchievement($username, 0, 10000);

		$rating += 10000;
		$ratingAch += 10000;
	}

	/*
		Achievement 2
		Find all of the Easter Eggs
		Award: 1,000,000 Points
	*/

	if (!$achievements[1] && $eggs == 98) {
		giveAchievement($username, 1, 1000000);

		$rating += 1000000;
		$ratingAch += 1000000;
	}

	/*
		Achievement 3
		Beat any level with a specified par time.
		Award: 10,000 Points
	*/

	if (!$achievements[2]) {
		require_once("lbratings.php");

		$query = pdo_prepare("SELECT COUNT(*) FROM `scores` WHERE `username` = :user AND `level` IN (SELECT `stripped` FROM `officiallevels` WHERE `qualify` != 0) UNION (SELECT `stripped` FROM `levels` WHERE `qualify` != 0)");
		$query->bind(":user", $username, PDO::PARAM_STR);
		$result = $query->execute();

		if ($result->fetchIdx(0) > 0) {
			giveAchievement($username, 2, 10000);

			$rating += 10000;
			$ratingAch += 10000;
		}
	}

	/*
		Achievement 4
		Beat Learn the Time Modifier under 1.75 seconds.
		Award: 25,000 Points
	*/

	if (!$achievements[3]) {
		$score = topScore($username, "learnthetimemodifier");
		if ($score < 1750) {
			giveAchievement($username, 3, 25000);

			$rating += 25000;
			$ratingAch += 25000;
		}
	}

	/*
		Achievement 5
		Beat Arch Acropolis under 7 seconds.
		Award: 75,000 Points
	*/

	if (!$achievements[4]) {
		$score = topScore($username, "archacropolis");
		if ($score < 7000) {
			giveAchievement($username, 4, 75000);

			$rating += 75000;
			$ratingAch += 75000;
		}
	}

	/*
		Achievement 6:
		Beat King of the Mountain under 9 seconds.
		Award: 40,000 Points
	*/

	if (!$achievements[5]) {
		$score = topScore($username, "kingofthemountain");
		if ($score < 9000) {
			giveAchievement($username, 5, 40000);

			$rating += 40000;
			$ratingAch += 40000;
		}
	}

	/*
		Achievement 7
		Beat Pinball Wizard under 10 seconds.
		Award: 50,000 Points
	*/

	if (!$achievements[6]) {
		$score = topScore($username, "pinballwizard");
		if ($score < 10000) {
			giveAchievement($username, 6, 50000);

			$rating += 50000;
			$ratingAch += 50000;
		}
	}

	/*
		Achievement 8
		Beat Ramps Reloaded original gold time of 15 seconds.
		Award: 250,000 Points
	*/

	if (!$achievements[7]) {
		$score = topScore($username, "rampsreloaded");
		if ($score < 15000) {
			giveAchievement($username, 7, 250000);

			$rating += 250000;
			$ratingAch += 250000;
		}
	}

	/*
		Achievement 9
		Beat Dive! under 17 seconds.
		Award: 50,000 Points
	*/

	if (!$achievements[8]) {
		$score = topScore($username, "dive");
		if ($score < 17000) {
			giveAchievement($username, 8, 50000);

			$rating += 50000;
			$ratingAch += 50000;
		}
	}

	/*
		Achievement 10
		Beat Acrobat under 18 seconds.
		Award: 25,000 Points
	*/

	if (!$achievements[9]) {
		$score = topScore($username, "acrobat");
		if ($score < 18000) {
			giveAchievement($username, 9, 25000);

			$rating += 25000;
			$ratingAch += 25000;
		}
	}

	/*
		Achievement 11
		Beat Icarus under 20 seconds.
		Award: 35,000 Points
	*/

	if (!$achievements[10]) {
		$score = topScore($username, "icarus");
		if ($score < 20000) {
			giveAchievement($username, 10, 35000);

			$rating += 35000;
			$ratingAch += 35000;
		}
	}

	/*
		Achievement 12
		Beat Airwalk under 25 seconds.
		Award: 150,000 Points
	*/

	if (!$achievements[11]) {
		$score = topScore($username, "airwalk");
		if ($score < 25000) {
			giveAchievement($username, 11, 150000);

			$rating += 150000;
			$ratingAch += 150000;
		}
	}

	/*
		Achievement 13
		Beat Pathways under 30 seconds.
		Award: 100,000 Points
	*/

	if (!$achievements[12]) {
		$score = topScore($username, "pathways");
		if ($score < 30000) {
			giveAchievement($username, 12, 100000);

			$rating += 100000;
			$ratingAch += 100000;
		}
	}

	/*
		Achievement 14
		Beat Siege under 40 seconds.
		Award: 40,000 Points
	*/

	if (!$achievements[13]) {
		$score = topScore($username, "siege");
		if ($score < 40000) {
			giveAchievement($username, 13, 40000);

			$rating += 40000;
			$ratingAch += 40000;
		}
	}

	/*
		Achievement 15
		Beat tightrope's gold time.
		Award: 500,000 Points
	*/

	if (!$achievements[14]) {
		$score = topScore($username, "tightrope");
		if ($score < 40000) {
			giveAchievement($username, 14, 500000);

			$rating += 500000;
			$ratingAch += 500000;
		}
	}


	/*
		Achievement 16
		Beat Combo Course in less than a minute!
		Award: 100,000 Points
	*/

	if (!$achievements[15]) {
		$score = topScore($username, "combocourse");
		if ($score < 60000) {
			giveAchievement($username, 15, 100000);

			$rating += 100000;
			$ratingAch += 100000;
		}
	}


	/*
		Achievement 17
		Beat Thief in less than a minute!
		Award: 50,000 Points
	*/

	if (!$achievements[16]) {
		$score = topScore($username, "thief");
		if ($score < 60000) {
			giveAchievement($username, 16, 50000);

			$rating += 50000;
			$ratingAch += 50000;
		}
	}

	/*
		Achievement 18
		Beat Space Station's Ultimate Time.
		Award: 200,000 Points
	*/

	if (!$achievements[17]) {
		$score = topScore($username, "spacestation");
		if ($score < 390000) {
			giveAchievement($username, 17, 200000);

			$rating += 200000;
			$ratingAch += 200000;
		}
	}

	/*
		Achievement 19
		Beat Battlecube Finale's Ultimate Time.
		Award: 100,000 Points
	*/

	if (!$achievements[18]) {
		$score = topScore($username, "battlecubefinale");
		if ($score < 570000) {
			giveAchievement($username, 18, 100000);

			$rating += 100000;
			$ratingAch += 100000;
		}
	}

	/*
		Achievement 20
		Beat Battlecube Finale in less than 7 minutes.
		Award: 150,000 Points
	*/

	if (!$achievements[19]) {
		$score = topScore($username, "battlecubefinale");
		if ($score < 420000) {
			giveAchievement($username, 19, 150000);

			$rating += 150000;
			$ratingAch += 150000;
		}
	}

	/*
		Achievement 21
		Beat Catwalks's Ultimate Time and Slowropes's Ultimate Time.
		Award: 150,000 Points
	*/

	if (!$achievements[20]) {
		$score1 = topScore($username, "catwalks");
		$score2 = topScore($username, "slowropes");
		if ($score1 < 95000 && $score2 < 150000) {
			giveAchievement($username, 20, 150000);

			$rating += 150000;
			$ratingAch += 150000;
		}
	}

	/*
		Achievement 22
		Achieve under 3.50 seconds on 'Learn the Super Jump' and under 10.00 seconds on
		'There and Back Again' by using the World Record methods.
		Award: 150,000 Points
	*/

	if (!$achievements[21]) {
		$score1 = topScore($username, "learnthesuperjump");
		$score2 = topScore($username, "thereandbackagain");
		if ($score1 < 3500 && $score2 < 10000) {
			giveAchievement($username, 21, 150000);

			$rating += 150000;
			$ratingAch += 150000;
		}
	}

	/*
		Achievement 23
		Beat Moto-Marblecross in less than 4 seconds, Monster Speedway Qualifying
		in less than 20 seconds and Monster Speedway in less than 15 seconds.
		Award: 125,000 Points
	*/

	if (!$achievements[22]) {
		$score1 = topScore($username, "motomarblecross");
		$score2 = topScore($username, "monsterspeedwayqualifying");
		$score3 = topScore($username, "monsterspeedway");
		if ($score1 < 4000 && $score2 < 20000 && $score3 < 15000) {
			giveAchievement($username, 22, 125000);

			$rating += 125000;
			$ratingAch += 125000;
		}
	}

	/*
		Achievement 24
		Beat Shimmy under 3 seconds, Path of Least Resistance under 10 seconds,
		Daedalus under 15 seconds & Tango under 13 seconds.
		Award: 125,000 Points
	*/

	if (!$achievements[23]) {
		$score1 = topScore($username, "shimmy");
		$score2 = topScore($username, "pathofleastresistance");
		$score3 = topScore($username, "daedalus");
		$score4 = topScore($username, "tango");
		if ($score1 < 3000 && $score2 < 10000 && $score3 < 15000 && $score4 < 13000) {
			giveAchievement($username, 23, 125000);

			$rating += 125000;
			$ratingAch += 125000;
		}
	}
	/*
		Achievement 25
		Achieve ANY of the THREE of the following times: under 1 minute on Skyscraper,
		under 30 seconds Survival of the Fittest, under 30 seconds Great Divide Revisited,
		under 20 seconds Tower Maze, under 15 seconds Battlements or under 20 seconds Natural Selection.
		Award: 250,000 Points
	*/

	if (!$achievements[24]) {
		$score1 = topScore($username, "skyscraper");
		$score2 = topScore($username, "survivalofthefittest");
		$score3 = topScore($username, "greatdividerevisited");
		$score4 = topScore($username, "towermaze");
		$score5 = topScore($username, "battlements");
		$score6 = topScore($username, "naturalselection");

		$scores = 0;
		if ($score1 < 60000) $scores ++;
		if ($score2 < 30000) $scores ++;
		if ($score3 < 30000) $scores ++;
		if ($score4 < 20000) $scores ++;
		if ($score5 < 15000) $scores ++;
		if ($score6 < 20000) $scores ++;

		if ($scores >= 3) {
			giveAchievement($username, 24, 250000);

			$rating += 250000;
			$ratingAch += 250000;
		}
	}
	/*
		Achievement 26
		Get a top ten place on any leaderboard on any level.
		Award: 10,000 Points
	*/

	if (!$achievements[25]) {
		$query = pdo_prepare("SELECT `stripped` FROM `officiallevels` UNION SELECT `stripped` FROM `levels`");
		$result = $query->execute();

		while (($level = $result->fetchIdx(0)) !== false) {
			$query1 = pdo_prepare("SELECT COUNT(*) FROM (SELECT * FROM (SELECT `username`, `score` FROM `scores` WHERE `level` = :level AND `username` IN (SELECT `username` FROM `users` WHERE `showscores` = 1 AND `banned` = 0) ORDER BY `score`) AS `scores` GROUP BY `username` ORDER BY `score` ASC LIMIT 10) AS `top10` WHERE `username` = :username");
			$query1->bind(":level", $level, PDO::PARAM_STR);
			$query1->bind(":username", $username, PDO::PARAM_STR);
			$bests = $query1->execute()->fetchIdx(0);

			if ($bests > 0) {
				giveAchievement($username, 25, 10000);

				$rating += 10000;
				$ratingAch += 10000;
				break;
			}
		}
	}

	/*
		Achievement 27
		Reach 7 million points on your ranking for the MBG Leaderboards.
		Award: 0 Points
	*/

	if (!$achievements[26] && $ratingMBG > 7000000) {
		giveAchievement($username, 26, 0);

		$rating += 0;
		$ratingAch += 0;
	}

	/*
		Achievement 28
		Reach 12 million points on your ranking for the MBP Leaderboards or MBP GG Marble Leaderboards.
		Award: 0 Points
	*/

	if (!$achievements[27] && $ratingMBP > 12000000) {
		giveAchievement($username, 27, 0);

		$rating += 0;
		$ratingAch += 0;
	}
	/*
		Achievement 29
		Achieve 30 million points on your username from the total of all leaderboards.
		Award: 0 Points
	*/

	if (!$achievements[28] && $rating > 30000000) {
		giveAchievement($username, 28, 0);

		$rating += 0;
		$ratingAch += 0;
	}

	/*
		Achievement 30
		Achieve 60 million points on your username from the total of all leaderboards
		Award: 0 Points
	*/

	if (!$achievements[29] && $rating > 60000000) {
		giveAchievement($username, 29, 0);

		$rating += 0;
		$ratingAch += 0;
	}

	//Speed boostair flair, sub 2 minutes on Speed Attack
	if (topScore($username, "speedattack") < 120000) {
		awardTitle($username, SPEEDBOOSTAIR_FLAIR);
	}

	//"The Asleep" flair: 10 or more times of 99:59.999
	$query = pdo_prepare("SELECT COUNT(*) FROM `scores` WHERE `username` = :user AND `score` >= 5998999");
	$query->bind(":user", $username, PDO::PARAM_STR);
	$count = $query->execute()->fetchIdx(0);

	if ($count >= 10) {
		awardTitle($username, THEASLEEP_FLAIR);
	}

	//Spaceship flair, sub 6 minutes on Space Station
	if (topScore($username, "spacestation") <= 360000) {
		awardTitle($username, SPACESHIP_FLAIR);
	}

	//Traplaunch flair: sub-18.5 on Great Divide
	if (topScore($username, "greatdivide") <= 18500) {
		awardTitle($username, TRAPLAUNCH_FLAIR);
	}

	//Do they deserve a color?
	$query = pdo_prepare("SELECT COUNT(*) FROM
		(SELECT * FROM `officiallevels`
			LEFT JOIN
			(SELECT `level`, `score` FROM `scores` WHERE `username` = :user GROUP BY `level` ORDER BY `score` DESC) AS `best`
			ON `officiallevels`.`stripped` = `best`.`level`
		) AS `combined`
		WHERE `score` < `gold`");
	$query->bind(":user", $username);
	$count = $query->execute()->fetchIdx(0);
	if ($count == 281 && !getHasColor($username)) {
		//Yep
		awardColor($username);
	}

	// They got one!
	if ($rating != $ratingStart) {
		$query = pdo_prepare("UPDATE `users` SET `rating` = :rating, `rating_achievements` = :ratingAch WHERE `username` = :user");
		$query->bind(":rating", $rating, PDO::PARAM_INT);
		$query->bind(":ratingAch", $ratingAch, PDO::PARAM_INT);
		$query->bind(":user", $username, PDO::PARAM_STR);
		$query->execute();

		echo("NEWRATING $rating\n");
	}
}

function achievementProgress($username) {
	$achievements = achievements($username);
	$eggs = easterEggs($username);
	$rating = userField($username, "rating");
	$ratingStart = $rating;
	$ratingAch = userField($username, "rating_achievements");
	$ratingMBG = userField($username, "rating_mbg");
	$ratingMBP = userField($username, "rating_mbp");
	$ratingLBCustom = userField($username, "rating_custom");

	for ($i = 0; $i < count($achievements); $i ++)
		if ($achievements[$i] == true)
			$achievements[$i] = "100%";

	if (!$achievements[0]) {
		$achievements[0] = formatPerc(0);
	}

	/*
		Achievement 2
		Find all of the Easter Eggs
	*/

	if (!$achievements[1] && $eggs <= 120)
		$achievements[1] = formatPerc($eggs / 1.20);

	/*
		Achievement 4
		Beat Learn the Time Modifier under 1.75 seconds.
	*/

	if (!$achievements[3]) {
		$score = topScore($username, "learnthetimemodifier");
		if ($score > 1750) {
			$achievements[3] = formatTime2($score, 1750);
		}
	}

	/*
		Achievement 5
		Beat Arch Acropolis under 7 seconds.
	*/

	if (!$achievements[4]) {
		$score = topScore($username, "archacropolis");
		if ($score > 7000) {
			$achievements[4] = formatTime2($score, 7000);
		}
	}

	/*
		Achievement 6:
		Beat King of the Mountain under 9 seconds.
	*/

	if (!$achievements[5]) {
		$score = topScore($username, "kingofthemountain");
		if ($score > 9000) {
			$achievements[5] = formatTime2($score, 9000);
		}
	}

	/*
		Achievement 7
		Beat Pinball Wizard under 10 seconds.
	*/

	if (!$achievements[6]) {
		$score = topScore($username, "pinballwizard");
		if ($score > 10000) {
			$achievements[6] = formatTime2($score, 10000);
		}
	}

	/*
		Achievement 8
		Beat Ramps Reloaded original gold time of 15 seconds.
	*/

	if (!$achievements[7]) {
		$score = topScore($username, "rampsreloaded");
		if ($score > 15000) {
			$achievements[7] = formatTime2($score, 15000);
		}
	}

	/*
		Achievement 9
		Beat Dive! under 17 seconds.
	*/

	if (!$achievements[8]) {
		$score = topScore($username, "dive");
		if ($score > 17000) {
			$achievements[8] = formatTime2($score, 17000);
		}
	}

	/*
		Achievement 10
		Beat Acrobat under 18 seconds.
	*/

	if (!$achievements[9]) {
		$score = topScore($username, "acrobat");
		if ($score > 18000) {
			$achievements[9] = formatTime2($score, 18000);
		}
	}

	/*
		Achievement 11
		Beat Icarus under 20 seconds.
	*/

	if (!$achievements[10]) {
		$score = topScore($username, "icarus");
		if ($score > 20000) {
			$achievements[10] = formatTime2($score, 20000);
		}
	}

	/*
		Achievement 12
		Beat Airwalk under 25 seconds.
	*/

	if (!$achievements[11]) {
		$score = topScore($username, "airwalk");
		if ($score > 25000) {
			$achievements[11] = formatTime2($score, 25000);
		}
	}

	/*
		Achievement 13
		Beat Pathways under 30 seconds.
	*/

	if (!$achievements[12]) {
		$score = topScore($username, "pathways");
		if ($score > 30000) {
			$achievements[12] = formatTime2($score, 30000);
		}
	}

	/*
		Achievement 14
		Beat Siege under 40 seconds.
	*/

	if (!$achievements[13]) {
		$score = topScore($username, "siege");
		if ($score > 40000) {
			$achievements[13] = formatTime2($score, 40000);
		}
	}

	/*
		Achievement 15
		Beat tightrope's gold time.
	*/

	if (!$achievements[14]) {
		$score = topScore($username, "tightrope");
		if ($score > 40000) {
			$achievements[14] = formatTime2($score, 40000);
		}
	}


	/*
		Achievement 16
		Beat Combo Course in less than a minute!
	*/

	if (!$achievements[15]) {
		$score = topScore($username, "combocourse");
		if ($score > 60000) {
			$achievements[15] = formatTime2($score, 60000);
		}
	}


	/*
		Achievement 17
		Beat Thief in less than a minute!
	*/

	if (!$achievements[16]) {
		$score = topScore($username, "thief");
		if ($score > 60000) {
			$achievements[16] = formatTime2($score, 60000);
		}
	}

	/*
		Achievement 18
		Beat Space Station's Ultimate Time.
	*/

	if (!$achievements[17]) {
		$score = topScore($username, "spacestation");
		if ($score > 390000) {
			$achievements[17] = formatTime2($score, 390000);
		}
	}

	/*
		Achievement 19
		Beat Battlecube Finale's Ultimate Time.
	*/

	if (!$achievements[18]) {
		$score = topScore($username, "battlecubefinale");
		if ($score > 570000) {
			$achievements[18] = formatTime2($score, 570000);
		}
	}

	/*
		Achievement 20
		Beat Battlecube Finale in less than 7 minutes.
	*/

	if (!$achievements[19]) {
		$score = topScore($username, "battlecubefinale");
		if ($score > 420000) {
			$achievements[19] = formatTime2($score, 420000);
		}
	}

	/*
		Achievement 21
		Beat Catwalks's Ultimate Time and Slowropes's Ultimate Time.
	*/

	if (!$achievements[20]) {
		$score1 = topScore($username, "catwalks");
		$score2 = topScore($username, "slowropes");
		$achievements[20] = formatPerc(($score1 < 95000) + ($score2 < 150000) * 50);
	}

	/*
		Achievement 22
		Achieve under 3.50 seconds on 'Learn the Super Jump' and under 10.00 seconds on
		'There and Back Again' by using the World Record methods.
	*/

	if (!$achievements[21]) {
		$score1 = topScore($username, "learnthesuperjump");
		$score2 = topScore($username, "thereandbackagain");
		$achievements[21] = formatPerc(($score1 < 3500) + ($score2 < 1000) * 50);
	}

	/*
		Achievement 23
		Beat Moto-Marblecross in less than 4 seconds, Monster Speedway Qualifying
		in less than 20 seconds and Monster Speedway in less than 15 seconds.
	*/

	if (!$achievements[22]) {
		$score1 = topScore($username, "motomarblecross");
		$score2 = topScore($username, "monsterspeedwayqualifying");
		$score3 = topScore($username, "monsterspeedway");
		$achievements[22] = formatPerc(($score1 < 4000) + ($score2 < 20000) + ($score3 < 15000) * 33.3333);
	}

	/*
		Achievement 24
		Beat Shimmy under 3 seconds, Path of Least Resistance under 10 seconds,
		Daedalus under 15 seconds & Tango under 13 seconds.
	*/

	if (!$achievements[23]) {
		$score1 = topScore($username, "shimmy");
		$score2 = topScore($username, "pathofleastresistance");
		$score3 = topScore($username, "daedalus");
		$score4 = topScore($username, "tango");
		$achievements[23] = formatPerc(($score1 < 3000) + ($score2 < 10000) + ($score3 < 15000) + ($score4 < 13000) * 25);
	}
	/*
		Achievement 25
		Achieve ANY of the THREE of the following times: under 1 minute on Skyscraper,
		under 30 seconds Survival of the Fittest, under 30 seconds Great Divide Revisited,
		under 20 seconds Tower Maze, under 15 seconds Battlements or under 20 seconds Natural Selection.
	*/

	if (!$achievements[24]) {
		$score1 = topScore($username, "skyscraper");
		$score2 = topScore($username, "survivalofthefittest");
		$score3 = topScore($username, "greatdivide");
		$score4 = topScore($username, "towermaze");
		$score5 = topScore($username, "battlements");
		$score6 = topScore($username, "naturalselection");

		$scores = 0;
		if ($score1 < 60000) $scores ++;
		if ($score2 < 30000) $scores ++;
		if ($score3 < 30000) $scores ++;
		if ($score4 < 20000) $scores ++;
		if ($score5 < 15000) $scores ++;
		if ($score6 < 20000) $scores ++;

		$achievements[24] = formatPerc($scores * 33.34);
	}
	/*
		Achievement 26
		Get a first place on any leaderboard on any level.
	*/

	if (!$achievements[25]) {
		$achievements[25] = formatPerc(0);
	}

	/*
		Achievement 27
		Reach 7 million points on your ranking for the MBG Leaderboards.
	*/

	if (!$achievements[26]) {
		$achievements[26] = formatPerc($ratingMBG / 70000.00);
	}

	/*
		Achievement 28
		Reach 12 million points on your ranking for the MBP Leaderboards.
	*/

	if (!$achievements[27]) {
		$achievements[27] = formatPerc($ratingMBP / 120000.00);
	}
	/*
		Achievement 29
		Achieve 30 million points on your username from the total of all leaderboards.
	*/

	if (!$achievements[28]) {
		$achievements[28] = formatPerc($rating / 300000.00);
	}

	/*
		Achievement 30
		Achieve 60 million points on your username from the total of all leaderboards
	*/

	if (!$achievements[29]) {
		$achievements[29] = formatPerc($rating / 600000.00);
	}

	return $achievements;
}

function achievements($username) {
	global $lb_connection;

	$query = pdo_prepare("SELECT `achievement` FROM `achievements` WHERE `username` = :user");
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

function achievementPoints($username) {
	$ratings = [10000, 1000000, 10000, 25000, 75000, 40000, 50000, 250000, 50000, 25000, 35000, 150000, 100000, 40000, 500000, 100000, 50000, 200000, 100000, 150000, 150000, 150000, 125000, 125000, 250000, 10000, 0, 0, 0, 0, 0, 0];

	$query = pdo_prepare("SELECT `achievement` FROM `achievements` WHERE `username` = :user");
	$query->bind(":user", $username, PDO::PARAM_STR);
	$result = $query->execute();

	$return = 0;

	while (($row = $result->fetchIdx()) !== false) {
		$return += $ratings[$row[0]];
	}

	return $return;
}

function topScore($username, $level) {
	global $lb_connection;

	$query = pdo_prepare("SELECT `score` FROM `scores` WHERE `username` = :user AND `level` = :level ORDER BY `score` ASC LIMIT 1");
	$query->bind(":user", $username, PDO::PARAM_STR);
	$query->bind(":level", $level, PDO::PARAM_STR);
	$result = $query->execute();

	$array = $result->fetchIdx();

	if (!$array)
		return 5998999;

	return $array[0];
}

function easterEggs($username, $game = null) {
	global $lb_connection;

	$query = pdo_prepare("SELECT COUNT(DISTINCT(`level`)) FROM `easteregg` WHERE `username` = :user" . ($game == null ? "" : " AND `gametype` = :game"));
	$query->bind(":user", $username, PDO::PARAM_STR);
	if ($game != null)
		$query->bind(":game", $game);
	$result = $query->execute();

	return $result->fetchIdx(0);
}

function dumpAchievements($username) {
	$achievements = achievements($username);
	for ($i = 0; $i < count($achievements); $i ++) {
		if ($achievements[$i] == true)
			echo("ACHIEVEMENT $i\n");
	}
}

function giveAchievement($username, $number, $rating) {
	$query = pdo_prepare("INSERT INTO `achievements` (`username`, `achievement`, `rating`) VALUES (:user, :number, :rating)");
	$query->bind(":user", $username, PDO::PARAM_STR);
	$query->bind(":number", $number, PDO::PARAM_INT);
	$query->bind(":rating", $rating, PDO::PARAM_INT);
	$query->execute();

	echo("ACHIEVEMENTGET $number\n");
}

function formatTime2($time, $par) {
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

function formatPerc($perc) {
	return round($perc) . "%";
}

?>
