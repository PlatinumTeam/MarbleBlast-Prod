<?php

function generateKey($length = 64) {
	$chars = "abcdefghijklmnopqrstuvwqyz0123456789";

	//Get random seed from microtime
	list($usec, $sec) = explode(" ", microtime());
	//Do some cool maths
	$seed = (float) $sec + ((float) $usec * 100000);
	//And set the seed
	mt_srand($seed);

	//Generate
	$str = "";
	$charc = strlen($chars);

	for ($i = 0; $length > $i; $i ++) {
		$str .= $chars{mt_rand(0, $charc - 1)};
	}

	return $str;
}

function calculateFinalScores($array = NULL) {
	//Default array is $_POST
	if ($array == NULL)
		$array = $_POST;

	/* Value - Description (example)
		player - Array of player names
		score  - Array of player scores
		Place  - Array of player placings
		Host   - Array of player host statuses
	 */

	/* MySQL structure

		`address`       text
		`name`          text
		`level`         text
		`mode`          text
		`players`       int(11)
		`maxPlayers`    int(11)
		`password`      tinyint(1)
		`lastHeartbeat` timestamp
		`ping`          int(11)
		`key`           text
		`version`       text
		`dev`           tinyint(1)
		`dedicated`     tinyint(1)
		`mod`           text
		`os`            text
		`id`            int(11)
	 */

	//Get client IP address
	$address = $_SERVER["REMOTE_ADDR"];
	if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
		$address = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));

	//Get values from array
	$key     =      ($array["key"]);
	$level   =      ($array["level"]);
	$players = (int)($array["players"]);
	$port    = (int)($array["port"]);
	$modes   =      ($array["modes"]);

	//Make sure these values exist
	$port = ($port == "" ? 28000 : $port);

	global $dedicated;

	if (false) {
		if (serverExistsIPNew($address, $port)) {
			$key = serverExistsIPNew($address, $port);
		} else {
			//Make sure they're actually hosting
			if (!serverExistsIP($address, $port))
				die("ERR:4:A server with your IP address and port is not currently running!\n");

			if (!serverExistsKey($key))
				die("ERR:5:A server with the specified key is not currently running!\n");
		}
	}

	$address = $address . ":" . $port;

	$calculate = true;

	if (getServerPref("restrictmplevels")) {
		//Make sure they're playing a real level

		$query = pdo_prepare("SELECT * FROM `mplevels` WHERE `file` = :level");
		$query->bind(":level", $level);
		$result = $query->execute();
		if (!$result || !$result->rowCount())
			$calculate = false;

		//Only do this if it's a hunt level
		if (strpos(strtolower($modes), "hunt") === FALSE && $modes !== "")
			$calculate = false;
	}

	if ($modes === "") {
		//This should never be the case
		if ($calculate) {
			//It's official, has to be hunt
			$modes = "hunt";
		} else {
			//Not official, check if there are any gems
			for ($i = 0; $i < count($array["player"]); $i ++) {
				$score  = (int)($array["score"][$i]);
				$gems1  = (int)($array["gems"][1][$i]);
				$gems2  = (int)($array["gems"][2][$i]);
				$gems5  = (int)($array["gems"][5][$i]);

				if ($gems1 > 0) {
					//Also hunt
					$modes = "hunt";
					break;
				}
				if (($gems1 === 0 && $gems2 === 0 && $gems5 === 0) && $score > 0) {
					//No gems, but a score, must be coop
					$modes = "coop";
					break;
				}
			}
		}
	}

	//Make sure they don't send two scores (like my crappy Mac dedicated)
	//Also serves as a means of rate-limiting scores
	$query = pdo_prepare("SELECT COUNT(*) FROM `serverscores` WHERE `server` = :address AND `timestamp` > (CURRENT_TIMESTAMP - 20)");
	$query->bind(":address", $address);
	$games = $query->execute()->fetchIdx(0);
	if ($calculate && $games > 0) {
		//If we have a previous game, see if we can take its scores and send them

		//Make sure we know this isn't the same set of scores
		echo("SCORE REDUMP\n");

		//Pretend we've just sent the last round of scores
		echo("SCORE DUMP\n");

		//Get the last game's key
		$query = pdo_prepare("SELECT `key` FROM `serverscores` WHERE `server` = :address ORDER BY `timestamp` DESC LIMIT 1");
		$query->bind(":address", $address);
		$key = $query->execute()->fetchIdx(0);

		//Get the scores used in the last game
		$query = pdo_prepare("SELECT * FROM `serverscores` WHERE `key` = :key");
		$query->bind(":key", $key);
		$result = $query->execute();

		//Read all the player data from the last game so we can print it
		while (($row = $result->fetch()) !== FALSE) {
			$player = $row["username"];
			$finalrating = $row["post"];
			$change = $row["change"];

			//Echo out the old scores just like last time
			echo("SCR:$player\n");
			echo("RAT:$finalrating\n");
			echo("CHG:$change\n");
		}

		//Kill the script so we don't submit again
		die("ERR:-1:Scores Submitted.\n");
	}

	if ($key == "") {
		$key = generateKey();
	}

	//Organize the players because we don't trust the host
	$ptemp = $array;
	$guests = 0;

	echo("SCORE DUMP\n");

	$provisGames = getServerPref("provisgames");
	$finals = array();

	//Do fun score calculation here!
	for ($i = 0; $i < count($array["player"]); $i ++) {

		$player = ($array["player"][$i]);
		$placement = ($array["place"][$i]);
		$handicap = (int)($array["handicap"][$i]);

		if (isGuest($player) || $array["guest"][$i])
			continue;

		$query = pdo_prepare("SELECT `rating_mp`, `rating_mpgames` FROM `users` WHERE `username` = :player");
		$query->bind(":player", $player);
		list($oldrating, $games) = $query->execute()->fetchIdx();

		$team = $array["team"][$i];

		$change = 0;
		$opponents = 0;
		$betterguests = 0;
		$provis = $games < $provisGames;

		if ($calculate) {
			for ($j = 0; $j < count($array["player"]); $j ++) {

				if ($j == $i)
					continue;

				$oppplayer = ($array["player"][$j]);
				$oppplacement = ($array["place"][$j]);
				$opphandicap = (int)($array["handicap"][$j]);

				$query = pdo_prepare("SELECT `rating_mp`, `rating_mpgames` FROM `users` WHERE `username` = :player");
				$query->bind(":player", $oppplayer);
				list($opprating, $oppgames) = $query->execute()->fetchIdx();

				$oppteam = $array["team"][$j];

				//Don't have team mates affect your score
				if ($oppteam == $team && $team != -1 && $oppteam != -1)
					continue;

				//Ignore guests
				if (isGuest($oppplayer) || $array["guest"][$j]) {
					if ($oppplacement < $placement)
						$betterguests ++;
					continue;
				}

				$oppprovis = $oppgames < $provisGames;

				$winloss = 0.5;
				if ($oppplacement > $placement)
					$winloss = 1;
				if ($oppplacement < $placement)
					$winloss = 0;

				$newrating = 0;

				/*
				If you win, you get more points for being handicapped
				If you win, you lose points based on your opponent's handicap
				If you lose, you lose fewer points for being handicapped
				If you lose, you lose more points based on your opponent's handicap

				Essentially

				If you win:
					Adjustment = h1 - h2
				If you lose
					Adjustment = h2 - h1

				Essentially, if you lose, h2 is added to the lost points (from 32) and
					h1 is subtracted from the lost points (from 32).
				If you win, h1 is added to the gained points (from 32) and
					h2 is subtracted from the gained points (from 32)

				where:
					h1 = player's handicap for the game in # of handicaps
					h2 = opponents's handicap for the game in # of handicaps
				*/
				$handicapDiff = ($oppplacement > $placement ? handicapResolve($handicap, $level) - handicapResolve($opphandicap, $level) : handicapResolve($opphandicap, $level) - handicapResolve($handicap, $level));

				/*
				When a player is established and the opponent is also established:
					Points = 32 + h
				If player is provisional and the opponent is established
					Points = 16 + (16 * (n1 / 20)) + h
				If player is established and the opponent is provisional
					Points = 16 + (16 * (n2 / 20)) + h
				If player is provisional and the opponent is also provisional
					Points = 16 + (16 * ((n2 + n1) / 40)) + h

				And then the new rating is calculated as such:
					r1new = r1 + Points * (w - (1 / (1 + 10 ^ ((r2 - r1) / 400))))

				where:
					r1new = player's rating after the match
					r1 = player's rating prior to the match
					r2 = opponent's rating prior to the match
					w = player's outcome (0 for loss, 0.5 for draw, 1 for win)
					n1 = number of games played by player prior to the match
					n2 = number of games played by opponent prior to the match
					h = Adjustment (see above)
					h1 = player's handicap for the game in # of handicaps
					h2 = opponents's handicap for the game in # of handicaps
				*/

				if (!$provis && !$oppprovis)
					$newrating = 32 + $handicapDiff;
				if (!$provis && $oppprovis)
					$newrating = (16 + (16 * ($oppgames / $provisGames))) + $handicapDiff;
				if ($provis && !$oppprovis)
					$newrating = (16 + (16 * ($games / $provisGames))) + $handicapDiff;
				if ($provis && $oppprovis)
					$newrating = (16 + (16 * (($oppgames + $games) / 40))) + $handicapDiff;

				$newrating *= ($winloss - (1 / (1 + pow(10, ($opprating - $oldrating) / 400))));

				$change += $newrating;
				$opponents ++;
			}
		} else {
			//If it's a custom mission, don't change anything
			$newrating = $oldrating;
			$change = 0;
		}

		$change /= ($opponents == 0 ? 1 : $opponents);

		// Winter mode gives you extra points
		if (getServerPref("wintermode") && $opponents > 0) {
			$change += 3;
		}

		$finalrating = round($oldrating + $change);
		$change = round($change);

		// If they can actually get a sub-zero rating, that'd be impressive
		// (notices rating after playing Matan...) crap now I want to make this
		// an achievement so I can at least achieve something :(
		if ($finalrating < 0) {
			$change += abs($finalrating);
			$finalrating += abs($finalrating);
		}

		array_push($finals, array($i, $finalrating, $change, $oldrating, $betterguests));
	}

	$teamcount = 0;
	if ($array["team"][0] != -1) {
		$teams = array();
		for ($i = 0; $i < count($finals); $i ++) {
			$team = (int)$array["team"][$finals[$i][0]];
			if (!in_array($team, $teams)) {
				array_push($teams, $team);
			}
		}

		$teamcount = count($teams);
	}

	/*
	 Restore the final scores and player datas
	*/

	for ($i = 0; $i < count($finals); $i ++) {
		$index        = $finals[$i][0];
		$player       =      ($array["player"][$index]);
		$place        = (int)($array["place"][$index]);
		$handicap     = (int)($array["handicap"][$index]);
		$team         = (int)($array["team"][$index]);
		$score        = (int)($array["score"][$index]);
		$host         = (int)($array["host"][$index]);
		$gems1        = (int)($array["gems"][1][$index]);
		$gems2        = (int)($array["gems"][2][$index]);
		$gems5        = (int)($array["gems"][5][$index]);
		$marble       = (int)($array["marble"][$index]);

		$finalrating  = (int)$finals[$i][1];
		$change       = (int)$finals[$i][2];
		$oldrating    = (int)$finals[$i][3];
		$betterguests = (int)$finals[$i][4];
		$teamname     = "";
		$teamcolor    = "";

		if ($team != -1) {
			$teamname  =      ($array["teamname"][$index]);
			$teamcolor = (int)($array["teamcolor"][$index]);
		}

		$host = ($host == "" ? 0 : $host);

		if (isGuest($player))
			continue;

		$teammembers = 0;
		if ($team != -1) {
			$teams = array();
			for ($j = 0; $j < count($finals); $j ++) {
				$jteam = (int)$array["team"][$finals[$i][0]];
				if ($jteam == $team)
					$teammembers ++;
			}
		}

		//Dump it to the user
		echo("SCR:$player\n");
		echo("RAT:$finalrating\n");
		echo("CHG:$change\n");

		//SCORES WE NEED THEM
		if ($players > 1 && $calculate) {
			$query = pdo_prepare("UPDATE `users` SET `rating_mp` = $finalrating, `rating_mpgames` = `rating_mpgames` + 1, `gems1` = `gems1` + $gems1, `gems2` = `gems2` + $gems2, `gems5` = `gems5` + $gems5 WHERE `username` = :player");
			$query->bind(":player", $player);
			$query->execute();

			if ($team != -1) {
				$query = pdo_prepare("UPDATE `users` SET `rating_mpteamgames` = `rating_mpteamgames` + 1 WHERE `username` = :username");
				$query->bind(":username", $player);
				$query->execute();
			}

			if ($place == 1) {
				$query = pdo_prepare("UPDATE `users` SET `mpwinstreak` = `mpwinstreak` + 1 WHERE `username` = :username");
				$query->bind(":username", $player);
				$query->execute();
			} else {
				$query = pdo_prepare("UPDATE `users` SET `mpwinstreak` = 0 WHERE `username` = :username");
				$query->bind(":username", $player);
				$query->execute();
			}
		}

		//Metric shitton of varibles
		$query = pdo_prepare("INSERT INTO `serverscores` (`username`, `place`, `score`, `handicap`, `server`, `key`, `host`, `change`, `pre`, `post`, `players`, `guests`, `betterguests`, `team`, `teammembers`, `teams`, `level`, `modes`, `gems1`, `gems2`, `gems5`, `marble`, `custom`) VALUES
							(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$query->bindParam(1, $player);
		$query->bindParam(2, $place);
		$query->bindParam(3, $score);
		$query->bindParam(4, $handicap);
		$query->bindParam(5, $address);
		$query->bindParam(6, $key);
		$query->bindParam(7, $host);
		$query->bindParam(8, $change);
		$query->bindParam(9, $oldrating);
		$query->bindParam(10, $finalrating);
		$query->bindParam(11, $players);
		$query->bindParam(12, $guests);
		$query->bindParam(13, $betterguests);
		$query->bindParam(14, $team);
		$query->bindParam(15, $teammembers);
		$query->bindParam(16, $teamcount);
		$query->bindParam(17, $level);
		$query->bindParam(18, $modes);
		$query->bindParam(19, $gems1);
		$query->bindParam(20, $gems2);
		$query->bindParam(21, $gems5);
		$query->bindParam(22, $marble);
		$query->bindParam(23, !$calculate);
		$query->execute();

		if ($team != -1) {
			$query = pdo_prepare("INSERT INTO `serverteams` SET `username` = :username, `scorekey` = :key, `teamname` = :name, `teamcolor` = :color");
			$query->bind(":username", $player);
			$query->bind(":key", $key);
			$query->bind(":name", $teamname);
			$query->bind(":color", $teamcolor);
			$query->execute();
		}

		// And set their last played level
		$query = pdo_prepare("UPDATE `users` SET `lastlevel` = :level WHERE `username` = :username");
		$query->bind(":level", $level);
		$query->bind(":username", $player);
		$query->execute();

		//Snowball mode extra stuff
		if (strpos($modes, "snowball") !== FALSE) {
			$snowballs = (int)($array["snowballs"][$index]);
			$hits      = (int)($array["snowballhits"][$index]);
			$query = pdo_prepare("INSERT INTO `snowballs` SET `username` = ?, `count` = ? ON DUPLICATE KEY UPDATE `count` = `count` + ?");
			$query->bindParam(1, $player);
			$query->bindParam(2, $snowballs);
			$query->bindParam(3, $snowballs);
			$query->execute();

			$query = pdo_prepare("UPDATE `snowballs` SET `hits` = `hits` + :hits WHERE `username` = :username");
			$query->bind(":hits", $hits);
			$query->bind(":username", $player);
			$query->execute();
		}
	}

	$counter = 0;
	$newkey = generateKey();
	do {
		$newkey = generateKey();
		$counter ++;
	} while (serverExistsKey($key) && $counter < 100);

	$query = pdo_prepare("UPDATE `servers` SET `key` = :key WHERE `key` = :oldkey");
	$query->bind(":oldkey", $key);
	$query->bind(":key", $newkey);
	$query->execute();

	echo("ERR:-1:Scores Submitted.\n");
}

//-1- - gives ANTI POINTS DUN DUN DUNNNNNN
// 0 - gives no points when this handicap is on.
// 1+ - gives points when this handicap is on.

function handicapResolve($handicap = 0, $level) {
	$modifier[0]  = 1; // 2-Point Gems
	$modifier[1]  = 2; // 5-Point Gems
	$modifier[2]  = 1; // Collision
	$modifier[3]  = 4; // Diagonal
	$modifier[4]  = 4; // Jump
	$modifier[5]  = 3; // Blast
	$modifier[6]  = 2; // SuperJump
	$modifier[7]  = 1; // SuperSpeed
	$modifier[8]  = 3; // Gyrocopter
	$modifier[9]  = 1; // MegaMarble
	$modifier[10] = 3; // Radar
	$modifier[11] = 1; // Opponent Marbles

	// Beginner levels

	if ($level == "TripleDecker_Hunt") {
		$modifier[7]  = 0; // SuperSpeed
		$modifier[8]  = 0; // Gyrocopter
		$modifier[10] = 2; // Radar
	}
	if ($level == "RampMatrix_Hunt") {
		$modifier[1]  = 1; // 5-Point Gems - rarely happens
		$modifier[3]  = 3; // Diagonal
		$modifier[4]  = 3; // Jump
		$modifier[5]  = 3; // Blast
		$modifier[7]  = 0; // SuperSpeed
		$modifier[8]  = 0; // Gyrocopter
		$modifier[10] = 2; // Radar
	}
	if ($level == "KingOfTheMarble_Hunt") {
		// relies on yellow, collisions, blast and mega marble more than anything else
		$modifier[0]  = 2; // 2-Point Gems
		$modifier[1]  = 0; // 5-Point Gems
		$modifier[2]  = 2; // Collision
		$modifier[3]  = 2; // Diagonal
		$modifier[4]  = 2; // Jump
		$modifier[5]  = 4; // Blast
		$modifier[6]  = 1; // SuperJump
		$modifier[7]  = 2; // SuperSpeed
		$modifier[8]  = 0; // Gyrocopter
		$modifier[9]  = 2; // MegaMarble
		$modifier[10] = 0; // Radar
		$modifier[11] = 2; // Opponent Marbles
	}
	if ($level == "Sprawl") {
		$modifier[0]  = 2; // 2-Point Gems
		$modifier[7]  = 2; // SuperSpeed
	}
	if ($level == "Playground") {
		$modifier[0]  = 0; // 2-Point Gems
		$modifier[1]  = 0; // 5-Point Gems
		$modifier[2]  = 1; // Collision
		$modifier[3]  = 3; // Diagonal
		$modifier[4]  = 3; // Jump
		$modifier[5]  = 2; // Blast
		$modifier[6]  = 1; // SuperJump
		$modifier[7]  = 0; // SuperSpeed
		$modifier[8]  = 1; // Gyrocopter
		$modifier[9]  = 0; // MegaMarble
		$modifier[10] = 2; // Radar
	}
	if ($level == "BlastClub") {
		$modifier[1]  = 0; // 2-Point Gems
		$modifier[1]  = 0; // 5-Point Gems
		$modifier[6]  = 3; // SuperJump
		$modifier[7]  = 2; // SuperSpeed
		$modifier[8]  = 1; // Gyrocopter
	}
	if ($level == "Battlecube_Hunt") {
		$modifier[0]  = 0; // 2-Point Gems
		$modifier[1]  = 0; // 5-Point Gems
		$modifier[4]  = 3; // Jump
		$modifier[6]  = 1; // SuperJump
		$modifier[8]  = 0; // Gyrocopter
		$modifier[9]  = 2; // MegaMarble
	}
	if ($level == "Bowl") {
		$modifier[0]  = 4; // 2-Point Gems
		$modifier[1]  = 0; // 5-Point Gems
		$modifier[3]  = 6; // Diagonal
		$modifier[4]  = 6; // Jump
		$modifier[5]  = 2; // Blast
		$modifier[6]  = 1; // SuperJump
		$modifier[7]  = 2; // SuperSpeed
		$modifier[8]  = 3; // Gyrocopter
		$modifier[9]  = 1; // MegaMarble
		$modifier[10] = 4; // Radar
	}
	if ($level == "MarbleAgilityCourse_Hunt") {
		$modifier[4]  = 6; // Jump
		$modifier[6]  = 0; // SuperJump
		$modifier[7]  = 0; // SuperSpeed
		$modifier[8]  = 0; // Gyrocopter
		$modifier[9]  = 2; // MegaMarble
		$modifier[10] = 2; // Radar
		$modifier[11] = 2; // Opponent Marbles
	}
	if ($level == "Triumvirate") {
		$modifier[3]  = 3; // Diagonal
		$modifier[4]  = 3; // Jump
		$modifier[6]  = 3; // SuperJump
		$modifier[7]  = 0; // SuperSpeed
		$modifier[8]  = 3; // Gyrocopter
		$modifier[9]  = 0; // MegaMarble
		$modifier[10] = 4; // Radar
	}
	if ($level == "MarbleCity") {
		$modifier[7]  = 2; // SuperSpeed
	}

	// Intermediate levels

	if ($level == "VortexEffect") {
		$modifier[1]  = 1; // 5-Point Gems
		$modifier[7]  = 2; // SuperSpeed
	}
	if ($level == "BasicAgilityCourse_Hunt") {
		$modifier[1]  = 3; // 5-Point Gems
		$modifier[7]  = 0; // SuperSpeed
		$modifier[9]  = 0; // MegaMarble - exists but rarely used
	}
	if ($level == "BattlecubeRevisited_Hunt") {
		$modifier[4]  = 3; // Jump
		$modifier[5]  = 2; // Blast
		$modifier[6]  = 1; // SuperJump
		$modifier[7]  = 2; // SuperSpeed
		$modifier[8]  = 1; // Gyrocopter
		$modifier[9]  = 2; // MegaMarble
	}
	if ($level == "AllAngles") {
		$modifier[6]  = 1; // SuperJump
		$modifier[8]  = 1; // Gyrocopter
		$modifier[9]  = 2; // MegaMarble
		$modifier[10] = 2; // Radar
		$modifier[11] = 2; // Opponent Marbles
	}
	if ($level == "Core") {
		$modifier[6]  = 1; // SuperJump
		$modifier[8]  = 1; // Gyrocopter
		$modifier[10] = 4; // Radar
	}
	if ($level == "Epicenter") {
		$modifier[5]  = 2; // Blast
		$modifier[8]  = 2; // Gyrocopter
	}
	if ($level == "GemsInTheRoad") {
		$modifier[0]  = 1; // 2-Point Gems
		$modifier[1]  = 0; // 5-Point Gems
		$modifier[6]  = 1; // SuperJump
		$modifier[8]  = 2; // Gyrocopter
	}
	if ($level == "MarbleItUp") {
		$modifier[6]  = 1; // SuperJump
		$modifier[8]  = 1; // Gyrocopter
		$modifier[10] = 4; // Radar
	}
	if ($level == "SkateBattleRoyale") {
		$modifier[6]  = 1; // SuperJump
		$modifier[7]  = 2; // SuperSpeed
		$modifier[8]  = 2; // Gyrocopter
	}
	if ($level == "Ziggurat") {
		$modifier[6]  = 1; // SuperJump
		$modifier[8]  = 2; // Gyrocopter
	}
	if ($level == "Gym_Hunt") {
		$modifier[4]  = 6; // Jump
		$modifier[8]  = 1; // Gyrocopter
	}

	// Advanced levels

	if ($level == "EyeOfTheStorm_Hunt") {
		$modifier[1]  = 1; // 5-Point Gems
		$modifier[3]  = 7; // Diagonal - TEST FOR IMPORTANCE
	// You're fucked. Like, fucked fucked.
		$modifier[4]  = 50; // Jump
		$modifier[5]  = 7; // Blast
		$modifier[6]  = 0; // SuperJump
		$modifier[7]  = 0; // SuperSpeed
		$modifier[8]  = 0; // Gyrocopter
		$modifier[9]  = 0; // MegaMarble
	}
	if ($level == "Sacred") {
		$modifier[5]  = 4; // Blast
		$modifier[7]  = 2; // SuperSpeed
		$modifier[8]  = 0; // Gyrocopter
		$modifier[9]  = 2; // MegaMarble
	}
	if ($level == "NukesweeperRevisited_Hunt") {
	// Powerups are troll in this level. You're better off without them.
	// Collision/Diagonal don't matter here too.
	// Blast you need once every 8 seconds to get into/out of a square.
	// Jump is therefore most important because you can roll into 22/52 squares from the side.
		$modifier[0]  = 0; // 2-Point Gems
		$modifier[1]  = 0; // 5-Point Gems
		$modifier[2]  = 0; // Collision
		$modifier[3]  = 0; // Diagonal
		$modifier[4]  = 25; // Jump
		$modifier[5]  = 0; // Blast
		$modifier[6]  = 0; // SuperJump
		$modifier[7]  = 0; // SuperSpeed
		$modifier[8]  = 0; // Gyrocopter
		$modifier[9]  = 0; // MegaMarble
	}
	if ($level == "Zenith") {
		$modifier[4]  = 5; // Jump
	// Played a match without these powerups and it was a disaster
		$modifier[5]  = 4; // Blast
		$modifier[6]  = 3; // SuperJump
		$modifier[7]  = 2; // SuperSpeed
	}
	if ($level == "Architecture") {
		$modifier[4]  = 5; // Jump
		$modifier[7]  = 2; // SuperSpeed
		$modifier[8]  = 2; // Gyrocopter
	}
	if ($level == "Horizon") {
		$modifier[6]  = 1; // SuperJump
		$modifier[7]  = 3; // SuperSpeed
	}
	if ($level == "ParPit_Hunt") {
		$modifier[3]  = 3; // Diagonal
		$modifier[4]  = 10; // Jump - level relies on wall hits. I got 74 no jump though.
		$modifier[5]  = 4; // Blast
		$modifier[6]  = 2; // SuperJump
		$modifier[7]  = 0; // SuperSpeed
		$modifier[8]  = 0; // Gyrocopter
		$modifier[9]  = 0; // MegaMarble
	}
	if ($level == "RampsReloaded_Hunt") {
		$modifier[3]  = 3; // Diagonal
		$modifier[4]  = 3; // Jump
		$modifier[8]  = 0; // Gyrocopter
	}
	if ($level == "Concentric") {
		$modifier[2]  = 2; // Collision
		$modifier[3]  = 2; // Diagonal
		$modifier[4]  = 6; // Jump
		$modifier[6]  = 0; // SuperJump
		$modifier[7]  = 0; // SuperSpeed
		$modifier[8]  = 0; // Gyrocopter
		$modifier[9]  = 2; // MegaMarble
		$modifier[10] = 2; // Radar
		$modifier[11] = 4; // Opponent Marbles
	}
	if ($level == "Promontory") {
		$modifier[4]  = 10; // Jump
		$modifier[6]  = 3; // SuperJump
		$modifier[8]  = 2; // Gyrocopter
		$modifier[9]  = 3; // MegaMarble
		$modifier[10] = 6; // Radar
	}
	if ($level == "Spires") {
		$modifier[0]  = 2; // 2-Point Gems
		$modifier[1]  = 3; // 5-Point Gems
		$modifier[3]  = 5; // Diagonal
		$modifier[8]  = 5; // Gyrocopter
		$modifier[9]  = 0; // MegaMarble
	}

	return (!!($handicap & (1 <<  0)) * $modifier[0])  + //Disable 2-Point Gems
			 (!!($handicap & (1 <<  1)) * $modifier[1])  + //Disable 5-Point Gems
			 (!!($handicap & (1 <<  2)) * $modifier[2])  + //Disable Collision
			 (!!($handicap & (1 <<  3)) * $modifier[3])  + //Disable Diagonal
			 (!!($handicap & (1 <<  4)) * $modifier[4])  + //Disable Jump
			 (!!($handicap & (1 <<  5)) * $modifier[5])  + //Disable Blast
			 (!!($handicap & (1 <<  6)) * $modifier[6])  + //Disable SuperJump
			 (!!($handicap & (1 <<  7)) * $modifier[7])  + //Disable SuperSpeed
			 (!!($handicap & (1 <<  8)) * $modifier[8])  + //Disable Gyrocopter
			 (!!($handicap & (1 <<  9)) * $modifier[9])  + //Disable MegaMarble
			 (!!($handicap & (1 << 10)) * $modifier[10]) + //Disable Radar
			 (!!($handicap & (1 << 11)) * $modifier[11]);  //Disable Opponent Marbles
}
?>