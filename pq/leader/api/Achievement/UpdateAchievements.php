<?php

//Leaving this here for historical reasons
//-----------------------------------------------------------------------------
// Jeff: this is going to be a nightmare to code, because there are SOOOOO
//       many achievements!
//-----------------------------------------------------------------------------

define("NO_TIME", 6000000);

define("DOUBLE_DIAMOND_FLAIR", 109);
define("SPEED_BOOSTAIR_FLAIR", 62);
define("TITLE_THE_ASLEEP", 76);
define("SPACESHIP_FLAIR", 124);
define("TRAPLAUNCH_FLAIR", 105);

define("PQBRONZE_FLAIR", 213);
define("PQSILVER_FLAIR", 214);
define("PQGOLD_FLAIR", 215);
define("PQPLATINUM_FLAIR", 216);
define("NESTEGG_FLAIR", 218);
define("GEMBLUE_FLAIR", 219);
define("GEMPLATINUM_FLAIR", 220);
define("GEMPURPLE_FLAIR", 221);
define("GEMRED_FLAIR", 222);
define("GEMTEAL_FLAIR", 223);
define("GEMYELLOW_FLAIR", 224);
define("RBYGEM_FLAIR", 231);
define("BUBBLE_FLAIR", 225);
define("ANVIL_FLAIR", 226);
define("FIREBALL_FLAIR", 227);
define("TITLE_THE_SPEEDSTER", 228);
define("TITLE_THE_OVERACHIEVER", 229);
define("TITLE_THE_COMPLETIONIST", 230);
define("TITLE_100_AWESOME", 212);

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
define("GHOST_HUNTER_PREFIX", 163);
define("GHOST_WHISPERER_FLAIR", 164);

function updateAchievements(User $user) {
	global $db;

	//Current achievements
	$current = $user->achievements;
	//List of best times (much faster than 1000 queries)
	$bests = getBestScores($user);

	//Egg achievements
	$query = $db->prepare("
		SELECT `game_id`, COUNT(*) FROM (
		    SELECT mission_id FROM ex82r_user_eggs
		    JOIN ex82r_missions ON ex82r_user_eggs.`mission_id` = ex82r_missions.`id`
		    JOIN `ex82r_mission_games` ON ex82r_missions.`game_id` = `ex82r_mission_games`.`id`
		    WHERE `user_id` = :user_id
		      AND `game_type` = 'Single Player' AND `is_custom` = 0
		    GROUP BY mission_id
		) AS egg_missions
		JOIN ex82r_missions ON ex82r_missions.id = egg_missions.mission_id
		JOIN `ex82r_mission_games` ON ex82r_missions.game_id = `ex82r_mission_games`.`id`
		GROUP BY `game_id`
	");
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	$gameEggs = fetchQueryAssociative($query);
	$eggCount = array_sum($gameEggs);

	//At least 1 easter egg
	if ($eggCount > 0) { grant($user, 1); }
	//98 MBP eggs
	if ($gameEggs[2] == 98) { grant($user, 2); }
	//20 MBU eggs
	if ($gameEggs[3] == 20) { grant($user, 32); }

	//Any level under par
	$query = $db->prepare("
		SELECT game_id, COUNT(game_id) FROM (
			SELECT ex82r_user_scores.mission_id FROM ex82r_user_scores
			JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
			JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
			WHERE score < ex82r_mission_rating_info.par_time
			  AND user_id = :user_id AND is_custom = 0
			GROUP BY mission_id
		) AS beat_pars
		JOIN ex82r_missions ON beat_pars.mission_id = ex82r_missions.id
		JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
		WHERE game_type = 'Single Player'
		GROUP BY game_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	$pars = fetchQueryAssociative($query);

	//Beat any MBP par
	if ($pars[2] > 0) { grant($user, 3); }
	//Beat any MBU par
	if ($pars[3] > 0) { grant($user, 31); }
	//Beat all MBU pars
	if ($pars[3] == 61) { grant($user, 36); }

	//All MBU ultimates, Matan is a bit of a dick for this one
	$query = $db->prepare("
		SELECT COUNT(*) FROM (
			SELECT ex82r_user_scores.mission_id FROM ex82r_user_scores
			JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
			JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
			WHERE score < ex82r_mission_rating_info.ultimate_time
			  AND user_id = :user_id
			  AND game_id = 3
			  AND is_custom = 0
			GROUP BY mission_id
		) AS beat_ults
	");
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	$ults = $query->fetchColumn(0);
	if ($ults == 61) { grant($user, 37); }

	//Level-based ones, oh boy
	if (!in_array( 4, $current) && best($bests, "LearnTheTimeModifier") < 1750)   { grant($user,  4); }
	if (!in_array( 5, $current) && best($bests, "ArchAcropolis")        < 7000)   { grant($user,  5); }
	if (!in_array( 6, $current) && best($bests, "KingOfTheMountain")    < 9000)   { grant($user,  6); }
	if (!in_array( 7, $current) && best($bests, "PinballWizard")        < 10000)  { grant($user,  7); }
	if (!in_array( 8, $current) && best($bests, "RampsReloaded")        < 15000)  { grant($user,  8); }
	if (!in_array( 9, $current) && best($bests, "Dive!")                < 17000)  { grant($user,  9); }
	if (!in_array(10, $current) && best($bests, "Acrobat")              < 18000)  { grant($user, 10); }
	if (!in_array(11, $current) && best($bests, "Icarus")               < 20000)  { grant($user, 11); }
	if (!in_array(12, $current) && best($bests, "Airwalk")              < 25000)  { grant($user, 12); }
	if (!in_array(13, $current) && best($bests, "Pathways")             < 30000)  { grant($user, 13); }
	if (!in_array(14, $current) && best($bests, "Siege")                < 40000)  { grant($user, 14); }
	if (!in_array(15, $current) && best($bests, "Tightrope")            < 40000)  { grant($user, 15); }
	if (!in_array(16, $current) && best($bests, "ComboCourse")          < 60000)  { grant($user, 16); }
	if (!in_array(17, $current) && best($bests, "Thief")                < 60000)  { grant($user, 17); }
	if (!in_array(18, $current) && best($bests, "SpaceStation")         < 390000) { grant($user, 18); }
	if (!in_array(19, $current) && best($bests, "BattlecubeFinale")     < 570000) { grant($user, 19); }
	if (!in_array(20, $current) && best($bests, "BattlecubeFinale")     < 420000) { grant($user, 20); }
	if (!in_array(41, $current) && best($bests, "blackdiamond_ultra")   < 12000)  { grant($user, 41); }
	if (!in_array(42, $current) && best($bests, "urban_ultra")          < 10000)  { grant($user, 42); }
	if (!in_array(43, $current) && best($bests, "endurance_ultra")      < 15000)  { grant($user, 43); }
	if (!in_array(44, $current) && best($bests, "earlyfrost_ultra")     < 7250)   { grant($user, 44); }

	//These ones are slightly more complicated
	if (!in_array(21, $current) &&
	    best($bests, "Catwalks") < 95000 &&
	    best($bests, "Slowropes") < 150000) { grant($user, 21); }
	if (!in_array(22, $current) &&
	    best($bests, "LearnTheSuperJump") < 3500 &&
	    best($bests, "ThereAndBackAgain") < 10000) { grant($user, 22); }
	if (!in_array(23, $current) &&
	    best($bests, "Moto-Marblecross") < 4000 &&
	    best($bests, "MonsterSpeedwayQualifying") < 20000 &&
	    best($bests, "MonsterSpeedway") < 15000) { grant($user, 23); }
	if (!in_array(24, $current) &&
	    best($bests, "Shimmy") < 3000 &&
	    best($bests, "PathOfLeastResistance") < 10000 &&
	    best($bests, "Daedalus") < 15000 &&
	    best($bests, "Tango") < 13000) { grant($user, 24); }
	if (!in_array(45, $current) &&
	    best($bests, "halfpipe2_ultra") < 1600 &&
	    best($bests, "halfpipe_ultra") < 1900) { grant($user, 45); }

	//Beat three or more of these times
	if (!in_array(25, $current)) {
		$count = (best($bests, "Skyscraper")           < 60000 ? 1 : 0) +
		         (best($bests, "SurvivalOfTheFittest") < 30000 ? 1 : 0) +
		         (best($bests, "GreatDivideRevisited") < 30000 ? 1 : 0) +
		         (best($bests, "TowerMaze")            < 20000 ? 1 : 0) +
		         (best($bests, "Battlements")          < 15000 ? 1 : 0) +
		         (best($bests, "NaturalSelection")     < 20000 ? 1 : 0);

		if ($count >= 3) { grant($user, 25); }
	}

	//Top on any level
	if (!in_array(26, $current)) {
		$query = $db->prepare("
			SELECT COUNT(*) FROM ex82r_user_scores
			JOIN (
			    SELECT mission_id, MIN(sort) AS min FROM ex82r_user_scores
			    GROUP BY mission_id
			    ORDER BY sort ASC
		    ) AS tops
			ON tops.mission_id = ex82r_user_scores.mission_id
			AND tops.min = ex82r_user_scores.sort
        	JOIN ex82r_missions
        	  ON ex82r_user_scores.mission_id = ex82r_missions.id
			JOIN `ex82r_mission_games`
		  	  ON ex82r_missions.`game_id` = `ex82r_mission_games`.`id`
			WHERE `game_type` = 'Single Player'
			AND user_id = :user_id
			AND is_custom = 0
		");
		$query->bindValue(":user_id", $user->id);
		$query->execute();
		$count = $query->fetchColumn(0);
		if ($count > 0) {
			grant($user, 26);
		}

		/*
		// Top 10 on any level--- very slow

		$query = $db->prepare("
			SELECT ex82r_missions.id FROM `ex82r_missions`
			JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
			WHERE game_type = 'Single Player'
		");
		$query->execute();
		$ids = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		//VERY shitty-- this takes ~4 seconds to run.
		foreach ($ids as $id) {
			//See if we're in top 10 on each level
			$bests = Mission::getById($id)->getTopScores(0, 10);
			foreach ($bests as $best) {
				$userId = $best["user_id"];
				if ($userId === $user->id) {
					grant($user, 26);
					break 2;
				}
			}
		}
		*/
	}

	//Beating a whole bunch of levels, just at all
	if (!in_array(34, $current) &&
	    best($bests, "pitfall_ultra")           != NO_TIME &&
	    best($bests, "gravity_ultra")           != NO_TIME &&
	    best($bests, "platformparty_ultra")     != NO_TIME &&
	    best($bests, "earlyfrost_ultra")        != NO_TIME &&
	    best($bests, "windingroad_ultra")       != NO_TIME &&
	    best($bests, "rampmatrix_ultra")        != NO_TIME &&
	    best($bests, "jumpjumpjump_ultra")      != NO_TIME &&
	    best($bests, "upward_ultra")            != NO_TIME &&
	    best($bests, "gauntlet_ultra")          != NO_TIME &&
	    best($bests, "aroundtheworld_ultra")    != NO_TIME &&
	    best($bests, "duality_ultra")           != NO_TIME &&
	    best($bests, "mudslide_ultra")          != NO_TIME &&
	    best($bests, "aimhigh_ultra")           != NO_TIME &&
	    best($bests, "compasspoints_ultra")     != NO_TIME &&
	    best($bests, "obstacle_ultra")          != NO_TIME &&
	    best($bests, "sporkintheroad_ultra")    != NO_TIME &&
	    best($bests, "greatdivide_ultra")       != NO_TIME &&
	    best($bests, "plumbing_ultra")          != NO_TIME &&
	    best($bests, "whirl_ultra")             != NO_TIME &&
	    best($bests, "hopskipjump_ultra")       != NO_TIME &&
	    best($bests, "slickslide_ultra")        != NO_TIME &&
	    best($bests, "ordeal_ultra")            != NO_TIME &&
	    best($bests, "survival_ultra")          != NO_TIME &&
	    best($bests, "reloaded_ultra")          != NO_TIME &&
	    best($bests, "scaffold_ultra")          != NO_TIME &&
	    best($bests, "acrobat_ultra")           != NO_TIME &&
	    best($bests, "battlements_ultra")       != NO_TIME &&
	    best($bests, "threefoldmaze_ultra")     != NO_TIME &&
	    best($bests, "construction_ultra")      != NO_TIME &&
	    best($bests, "ski_ultra")               != NO_TIME &&
	    best($bests, "threefoldrace_ultra")     != NO_TIME &&
	    best($bests, "kingofthemountain_ultra") != NO_TIME &&
	    best($bests, "selection_ultra")         != NO_TIME &&
	    best($bests, "schadenfreude_ultra")     != NO_TIME) {
		grant($user, 34);
	}
	if (!in_array(35, $current) &&
	    best($bests, "mountaintop_ultra") != NO_TIME &&
	    best($bests, "ascend_ultra")      != NO_TIME &&
	    best($bests, "divergence_ultra")  != NO_TIME &&
	    best($bests, "urban_ultra")       != NO_TIME &&
	    best($bests, "lesstravel_ultra")  != NO_TIME &&
	    best($bests, "treehouse_ultra")   != NO_TIME &&
	    best($bests, "skate_ultra")       != NO_TIME &&
	    best($bests, "cube_ultra")        != NO_TIME &&
	    best($bests, "endurance_ultra")   != NO_TIME) {
		grant($user, 35);
	}

	//Beating levels with special modifiers
	if (!in_array(33, $current) &&
	    bestModifiers($user, "Hypercube_ultra", Modifiers::NoJumping) < 30000)
		{ grant($user, 33); }

	if (!in_array(38, $current) &&
	    bestModifiers($user, "blackdiamond_ultra", Modifiers::DoubleDiamond | Modifiers::NoTimeTravels) < 75000)
		{ grant($user, 38); }

	if (!in_array(39, $current) &&
		bestModifiers($user, "whirl_ultra", Modifiers::GotEasterEgg) < 25000)
		{ grant($user, 39); }

	//Rating-based ones are dumb
	if ($user->ratings["rating_mbg"] > 7000000) { grant($user, 27); }
	if ($user->ratings["rating_mbp"] > 12000000) { grant($user, 28); }
	if ($user->ratings["rating_general"] > 30000000) { grant($user, 29); }
	if ($user->ratings["rating_general"] > 60000000) { grant($user, 30); }
	if ($user->ratings["rating_mbu"] > 4000000) { grant($user, 40); }

	//-------------------------------------------------------------------------
	// MP Achievements
	//-------------------------------------------------------------------------

	//MP Achievement Win a match (non teams mode) on a rated level.
    //MP Achievement Win 500 cumulative matches in FFA.
    $query = $db->prepare("
         SELECT COUNT(*) FROM ex82r_match_scores
          JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
          JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
          JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
          JOIN ex82r_matches ON ex82r_match_scores.match_id = ex82r_matches.id
        WHERE
          ex82r_mission_games.rating_column = 'rating_mp'
          AND ex82r_missions.is_custom = 0
          AND ex82r_matches.player_count > 1
          AND ex82r_match_scores.placement = 1
          AND ex82r_match_scores.team_id IS NULL
          AND ex82r_match_scores.user_id = :user_id
    ");
    $query->bindValue(":user_id", $user->id);
    $query->execute();
    $count = $query->fetchColumn(0);
    if ($count > 0) {
        grant($user, 46);
    }
    if ($count >= 500) {
        grant($user, 57);
    }

    //MP Achievement Win a teams mode match on a rated level.
    //MP Achievement Win 100 cumulative teams mode matches.
    $query = $db->prepare("
         SELECT COUNT(*) FROM ex82r_match_scores
          JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
          JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
          JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
          JOIN ex82r_matches ON ex82r_match_scores.match_id = ex82r_matches.id
        WHERE
          ex82r_mission_games.rating_column = 'rating_mp'
          AND ex82r_missions.is_custom = 0
          AND ex82r_matches.player_count > 1
          AND ex82r_match_scores.placement = 1
          AND ex82r_match_scores.team_id IS NOT NULL
          AND ex82r_match_scores.user_id = :user_id
    ");
    $query->bindValue(":user_id", $user->id);
    $query->execute();
    $count = $query->fetchColumn(0);
    if ($count > 0) {
        grant($user, 47);
    }
    if ($count >= 100) {
        grant($user, 56);
    }

    //MPAchievement for 5000 red gems, 2000 yellow gems, 400 blue gems
    //and 15,000 total gems
    $query = $db->prepare("
      SELECT SUM(`gems_1_point`), SUM(`gems_2_point`), SUM(`gems_5_point`) 
        FROM ex82r_user_scores
        JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
        JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
      WHERE
        user_id = :user_id
        AND ex82r_mission_games.game_type = 'Multiplayer'
    ");
    $query->bindValue(":user_id", $user->id);
    $query->execute();
    $data = $query->fetch(PDO::FETCH_NUM);
    $gem1 = $data[0];
    $gem2 = $data[1];
    $gem5 = $data[2];
    $gemCount = $gem1 + $gem2 + $gem5;
    $points = $gem1 + ($gem2 * 2) + ($gem5 * 5);
    if ($gem1 >= 5000) {
        // 5000 red gems
        grant($user, 61);
    }
    if ($gem2 >= 2000) {
        grant($user, 62);
    }
    if ($gem5 >= 400) {
        grant($user, 63);
    }
    if ($gemCount >= 15000) {
        grant($user, 64);
    }
    if ($points >= 30000) {
        grant($user, 65);
    }

    //Win a FFA match on Spires with (at least [ don't tell Matan ]) 3 other people.
    $query = $db->prepare("
        SELECT COUNT(*)
          FROM ex82r_match_scores
          JOIN ex82r_matches ON ex82r_match_scores.match_id = ex82r_matches.id
          JOIN ex82r_user_scores ON ex82r_matches.mission_id = ex82r_user_scores.mission_id
          JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
        WHERE
          ex82r_missions.basename= 'Spires_Hunt'
          AND ex82r_matches.player_count >= 4
          AND ex82r_match_scores.placement = 1
          AND ex82r_match_scores.user_id = :user_id    
    ");
    $query->bindValue(":user_id", $user->id);
    $query->execute();
    $count = $query->fetchColumn(0);
    if ($count > 0) {
        grant($user, 67);
    }

    //Have more than 12 players on king of the marble.
    $query = $db->prepare("
        SELECT COUNT(*)
          FROM ex82r_matches
          JOIN ex82r_match_scores ON ex82r_matches.id = ex82r_match_scores.match_id
          JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
        WHERE
          ex82r_missions.basename = 'KingOfTheMarble_Hunt'
          AND ex82r_matches.player_count > 12
          AND ex82r_match_scores.user_id = :user_id    
    ");
    $query->bindValue(":user_id", $user->id);
    $query->execute();
    $count = $query->fetchColumn(0);
    if ($count > 0) {
        grant($user, 76);
    }

    //Beat hunt level with <= 2 points from opponent
    $query = $db->prepare("
        SELECT COUNT(*)
		FROM ex82r_match_scores AS score
		  JOIN ex82r_matches On score.match_id = ex82r_matches.id
		  JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
		  JOIN ex82r_user_scores ON score.score_id = ex82r_user_scores.id
		WHERE
		  ex82r_matches.player_count > 1
		  AND score.placement = 1
		  AND score.user_id = :user_id
		  AND (ex82r_user_scores.score - (
		    SELECT ex82r_user_scores.score FROM ex82r_match_scores
		    JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		    WHERE
		      ex82r_match_scores.placement = 2
		      AND ex82r_match_scores.match_id = score.match_id
		    LIMIT 1
		  )) <= 2
    ");
    $query->bindValue(":user_id", $user->id);
    $query->execute();
    $count = $query->fetchColumn(0);
    if ($count > 0) {
        grant($user, 49);
    }

    //Beat hunt level with >= 50 points from opponent
    $query = $db->prepare("
        SELECT COUNT(*)
          FROM ex82r_match_scores AS score
          JOIN ex82r_matches On score.match_id = ex82r_matches.id
          JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
          JOIN ex82r_user_scores ON score.score_id = ex82r_user_scores.id
        WHERE
          ex82r_matches.player_count > 1
          AND score.placement = 1
          AND score.user_id = :user_id
          AND (ex82r_user_scores.score - (
		    SELECT ex82r_user_scores.score FROM ex82r_match_scores
		    JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		    WHERE
		      ex82r_match_scores.placement = 2
		      AND ex82r_match_scores.match_id = score.match_id
            LIMIT 1
          )) >= 50
    ");
    $query->bindValue(":user_id", $user->id);
    $query->execute();
    $count = $query->fetchColumn(0);
    if ($count > 0) {
        grant($user, 50);
    }

	//Win a Multiplayer match on every official Hunt level.
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_missions
		JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
		WHERE ex82r_missions.id NOT IN (
		  SELECT ex82r_missions.`id`
		  FROM ex82r_matches
		    JOIN ex82r_match_scores ON ex82r_matches.id = ex82r_match_scores.match_id
		    JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
		    JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
		  WHERE rating_column = 'rating_mp'
		        AND is_custom = 0
		        AND user_id = :user_id
		        AND player_count > 1
		        AND placement = 1
		) AND rating_column = 'rating_mp'
		AND is_custom = 0
	");
    $query->bindValue(":user_id", $user->id);
    $query->execute();
    $count = $query->fetchColumn(0);
    if ($count == 0) {
    	grant($user, 51);
    }

    //Win a FFA match against 7 other people.
    $query = $db->prepare("
        SELECT COUNT(*)
          FROM ex82r_matches
          JOIN ex82r_match_scores ON ex82r_matches.id = ex82r_match_scores.match_id
          JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
          JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
        WHERE
          ex82r_mission_games.rating_column = 'rating_mp'
          AND ex82r_missions.is_custom = 0
          AND ex82r_matches.player_count >= 8
          AND ex82r_match_scores.placement = 1
          AND ex82r_match_scores.user_id = :user_id    
    ");
    $query->bindValue(":user_id", $user->id);
    $query->execute();
    $count = $query->fetchColumn(0);
    if ($count > 0) {
        grant($user, 53);
    }

    //54 and 55 are based on win streaks, so use the user field
    if ($user->streaks["mp_games"] >= 10) {
    	grant($user, 54);
    }
    if ($user->streaks["mp_games"] >= 25) {
    	grant($user, 55);
    }

    $query = $db->prepare("
        SELECT COUNT(*) FROM ex82r_missions
		  JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
		WHERE modification = 'Gold'
		  AND game_id = 6
		-- Levels that are in MBG where you do *not* have the platinum score
		-- If this is zero then you get the achievement 
		  AND (SELECT MAX(score) FROM ex82r_user_scores
		      WHERE user_id = :user_id
		      AND ex82r_user_scores.mission_id = mission_id
		  ) < ex82r_mission_rating_info.platinum_score
    ");
    $query->bindValue(":user_id", $user->id);
    $query->execute();
    if ($query->fetchColumn(0) === 0) {
    	grant($user, 69);
    }

	$query = $db->prepare("
        SELECT COUNT(*) FROM ex82r_missions
		  JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
		WHERE modification = 'Ultra'
		  AND game_id = 6
		-- Levels that are in MBU where you do *not* have the platinum score
		-- If this is zero then you get the achievement 
		  AND (SELECT MAX(score) FROM ex82r_user_scores
		      WHERE user_id = :user_id
		      AND ex82r_user_scores.mission_id = mission_id
		  ) < ex82r_mission_rating_info.platinum_score
    ");
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	if ($query->fetchColumn(0) === 0) {
		grant($user, 70);
	}

    //5 MP wins on Core, Concentric, Battlecube, Battlecube Revisited,
    //Vortex Effect, and Zenith
    if (getNumberOfWinsOnLevel($user, "Core_Hunt") >= 5 &&
        getNumberOfWinsOnLevel($user, "Concentric_Hunt") >= 5 &&
        getNumberOfWinsOnLevel($user, "Battlecube_Hunt") >= 5 &&
        getNumberOfWinsOnLevel($user, "BattlecubeRevisited_Hunt") >= 5 &&
        getNumberOfWinsOnLevel($user, "VortexEffect_Hunt") >= 5 &&
        getNumberOfWinsOnLevel($user, "Zenith_Hunt") >= 5) {
        // wow, that's a lot of queries...
        grant($user, 68);
    }

    //Win a 4v4 Multiplayer match.
    $query = $db->prepare("
	    SELECT COUNT(*) FROM ex82r_matches
		  JOIN ex82r_match_scores ON ex82r_matches.id = ex82r_match_scores.match_id
		  JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
		  JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
		  JOIN ex82r_match_teams ON ex82r_match_teams.id = ex82r_match_scores.team_id
		WHERE rating_column = 'rating_mp'
		  AND is_custom = 0
		  AND user_id = :user_id
		  AND ex82r_matches.player_count = 8
		  AND ex82r_match_teams.player_count = 4
		  AND placement = 1
		  AND team_id IS NOT NULL
    ");
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	if ($query->fetchColumn(0) > 0) {
		grant($user, 72);
	}


	//Win on both Sprawl and Horizon in a Team Mode Multiplayer match against two other teams.
	$query = $db->prepare("
	    SELECT COUNT(*) FROM ex82r_matches
		  JOIN ex82r_match_scores ON ex82r_matches.id = ex82r_match_scores.match_id
		  JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
		  JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
		  JOIN ex82r_match_teams ON ex82r_match_teams.id = ex82r_match_scores.team_id
		WHERE rating_column = 'rating_mp'
	      AND is_custom = 0
	      AND user_id = :user_id
	      AND ex82r_matches.team_count > 2
	      AND placement = 1
	      AND team_id IS NOT NULL
	      AND basename = 'Sprawl_Hunt'
    ");
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	$sprawlWins = $query->fetchColumn(0);
	$query = $db->prepare("
	    SELECT COUNT(*) FROM ex82r_matches
		  JOIN ex82r_match_scores ON ex82r_matches.id = ex82r_match_scores.match_id
		  JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
		  JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
		  JOIN ex82r_match_teams ON ex82r_match_teams.id = ex82r_match_scores.team_id
		WHERE rating_column = 'rating_mp'
	      AND is_custom = 0
	      AND user_id = :user_id
	      AND ex82r_matches.team_count > 2
	      AND placement = 1
	      AND team_id IS NOT NULL
	      AND basename = 'Horizon_Hunt'
    ");
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	$horizonWins = $query->fetchColumn(0);

	if ($sprawlWins > 0 && $horizonWins > 0) {
		grant($user, 73);
	}

	//Get the lowest score on your team by at least half the points than the next person after you.
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_match_scores AS score
		  JOIN ex82r_matches ON score.match_id = ex82r_matches.id
		  JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
		  JOIN ex82r_match_teams ON score.team_id = ex82r_match_teams.id
		  JOIN ex82r_user_scores ON score.score_id = ex82r_user_scores.id
		-- All scores where:
		WHERE score.user_id = :user_id
		-- Your team exists and has at least 2 players
		  AND team_id IS NOT NULL
		  AND ex82r_match_teams.player_count > 1
		  AND is_custom = 0
		-- The lowest non-you score on the team 
		  AND (
		    SELECT MIN(ex82r_user_scores.score) FROM ex82r_match_scores
		      JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		      WHERE ex82r_match_scores.match_id = score.match_id
		      AND ex82r_match_scores.user_id != :user_id2 -- Because you can't use the same param twice
		      AND team_id = score.team_id
		-- Is greater than twice your score
		) > ex82r_user_scores.score * 2
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":user_id2", $user->id);
	$query->execute();
	if ($query->fetchColumn(0) > 0) {
		grant($user, 74);
	}

	//Get more points than the rest of your team-mates combined.
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_match_scores AS score
		  JOIN ex82r_matches ON score.match_id = ex82r_matches.id
		  JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
		  JOIN ex82r_match_teams ON score.team_id = ex82r_match_teams.id
		  JOIN ex82r_user_scores ON score.score_id = ex82r_user_scores.id
		-- All scores where:
		WHERE score.user_id = :user_id
		-- Your team exists and has at least 3 players
		  AND team_id IS NOT NULL
		  AND ex82r_match_teams.player_count > 2
		  AND is_custom = 0
		-- Everyone else's scores summed up 
		  AND (
		    SELECT SUM(ex82r_user_scores.score) FROM ex82r_match_scores
		      JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		      WHERE ex82r_match_scores.match_id = score.match_id
		      AND ex82r_match_scores.user_id != :user_id2 -- Because you can't use the same param twice
		      AND team_id = score.team_id
		-- Is less than your score
		) < ex82r_user_scores.score
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":user_id2", $user->id);
	$query->execute();
	if ($query->fetchColumn(0) > 0) {
		grant($user, 75);
	}

	//TODO 77 Lose to more than one guest in a Multiplayer match.

	//Get more points than everyone else combined.
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_match_scores AS score
		  JOIN ex82r_matches ON score.match_id = ex82r_matches.id
		  JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
		  JOIN ex82r_user_scores ON score.score_id = ex82r_user_scores.id
		-- All scores where:
		WHERE score.user_id = :user_id
		  AND is_custom = 0
		  AND player_count > 2
		-- Everyone else's scores summed up 
		  AND (
		    SELECT SUM(ex82r_user_scores.score) FROM ex82r_match_scores
		      JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		      WHERE ex82r_match_scores.match_id = score.match_id
		      AND ex82r_match_scores.user_id != :user_id2 -- Because you can't use the same param twice
		-- Is less than your score
		) < ex82r_user_scores.score
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":user_id2", $user->id);
	$query->execute();
	if ($query->fetchColumn(0) > 0) {
		grant($user, 78);
	}

	//Beat another person by over 225 points.
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_match_scores AS score
		  JOIN ex82r_matches ON score.match_id = ex82r_matches.id
		  JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
		  JOIN ex82r_user_scores ON score.score_id = ex82r_user_scores.id
		-- All scores where:
		WHERE score.user_id = :user_id
		  AND is_custom = 0
		  AND player_count > 2
		-- Everyone else's scores summed up 
		  AND (
		    SELECT SUM(ex82r_user_scores.score) FROM ex82r_match_scores
		      JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		      WHERE ex82r_match_scores.match_id = score.match_id
		      AND ex82r_match_scores.user_id != :user_id2 -- Because you can't use the same param twice
		-- Is less than your score sub 225
		) < (ex82r_user_scores.score - 225)
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":user_id2", $user->id);
	$query->execute();
	if ($query->fetchColumn(0) > 0) {
		grant($user, 79);
	}

	//Defeat IsraeliRD in a match (FFA)
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_match_scores AS score
		JOIN ex82r_matches ON score.match_id = ex82r_matches.id
		-- So we can only get official levels
		JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
		JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
		-- Get opponent (who we'll call Matan) scores
		JOIN ex82r_match_scores AS matan_score ON matan_score.match_id = score.match_id
		-- Where it was you and Matan
		WHERE score.user_id = :user_id
		  AND matan_score.user_id = :matan_id
		  -- And you won
		  AND score.placement = 1
		  AND matan_score.placement > 1
		  -- And it wasn't custom
		  AND is_custom = 0
		  AND rating_column = 'rating_mp'
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":matan_id", 263);
	$query->execute();
	if ($query->fetchColumn(0) > 0) {
		grant($user, 81);
	}

	//Get a negative score
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_match_scores AS score
		JOIN ex82r_matches ON score.match_id = ex82r_matches.id
		JOIN ex82r_user_scores ON score.score_id = ex82r_user_scores.id
		-- So we can only get official levels
		JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
		JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
		WHERE score.user_id = :user_id
		  AND score < 0
		  -- And it wasn't custom
		  AND is_custom = 0
		  AND rating_column = 'rating_mp'
	");
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	if ($query->fetchColumn(0) > 0) {
		grant($user, 82);
	}

	//These will update only when their events are active
	updateHalloweenAchievements($user);
	updateWinterAchievements($user);

	updateEarnedFlair($user);

	$user->update();
	$updated = $user->achievements;
	return array_diff($current, $updated);
}

function updateEarnedFlair(User $user) {
	global $db;
	$bests = getBestScores($user);

	//Double diamond of < 1:10.000 gives the DD flair
	if (bestModifiers($user, "blackdiamond_ultra", Modifiers::DoubleDiamond | Modifiers::NoTimeTravels) < 70000) {
		$user->awardTitle(DOUBLE_DIAMOND_FLAIR);
	}
	//Speed boostair flair: sub 2 minutes on Speed Attack
	if (best($bests, "SpeedAttack") < 120000) {
		$user->awardTitle(SPEED_BOOSTAIR_FLAIR);
	}
	//"The Asleep" flair: 10 or more times of 99:59.999
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_user_scores WHERE `user_id` = :user_id AND `score` >= 5998999 AND `score_type` = 'time'
	");
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	if ($query->fetchColumn(0) >= 10) {
		$user->awardTitle(TITLE_THE_ASLEEP);
	}
	//Spaceship flair, sub 6 minutes on Space Station
	if (best($bests, "SpaceStation") <= 360000) {
		$user->awardTitle(SPACESHIP_FLAIR);
	}
	//Traplaunch flair: sub-18.5 on Great Divide
	if (best($bests, "GreatDivide") <= 18500) {
		$user->awardTitle(TRAPLAUNCH_FLAIR);
	}

	//PQ Titles (via Threefolder):
	// PQBRONZE_FLAIR    Unlock Quest Complete (125)
	// PQSILVER_FLAIR    Unlock Platinum Player (127)
	// PQGOLD_FLAIR      Unlock Ultimate Player (128)
	// PQPLATINUM_FLAIR  Unlock So awesome we couldn't think of a title (130)
	// NESTEGG_FLAIR     Unlock Bird Watcher (136) and Extra Chirpy (137)
	// GEMBLUE_FLAIR     TBD
	// GEMPLATINUM_FLAIR TBD
	// GEMPURPLE_FLAIR   TBD
	// GEMRED_FLAIR      TBD
	// GEMTEAL_FLAIR     TBD
	// GEMYELLOW_FLAIR   TBD
	// RBYGEM_FLAIR      Unlock Absolute Madness (133)
	// BUBBLE_FLAIR      Unlock Ultimate Breather! (140)
	// ANVIL_FLAIR       Unlock Unintended Workarounds (146)
	// FIREBALL_FLAIR    Unlock Rapid Fire (142) and Pants on Fire (150)

	// TITLE_THE_SPEEDSTER     Unlock They've Gone to Plaid! (138)
	// TITLE_100_AWESOME       Unlock So awesome we couldn't think of a title (130)
	// TITLE_THE_OVERACHIEVER  Unlock every PQ achievement (except Awesome Player,
	//                         So awesome we couldn't think of a title,
	//                         Basically a Gem Collection Level,
	//                         and The Real Awesome Time)
	// TITLE_THE_COMPLETIONIST Beat every single level's Ultimate Time,
	//                         unlock all achievements (except for those listed
	//                         in the previous title),
	//                         and find all Nest Eggs

	$achievements = $user->achievements;

	if (in_array(125, $achievements)) {
		$user->awardTitle(PQBRONZE_FLAIR);
	}
	if (in_array(127, $achievements)) {
		$user->awardTitle(PQSILVER_FLAIR);
	}
	if (in_array(128, $achievements)) {
		$user->awardTitle(PQGOLD_FLAIR);
	}
	if (in_array(130, $achievements)) {
		$user->awardTitle(PQPLATINUM_FLAIR);
	}
	if (in_array(136, $achievements) && in_array(137, $achievements)) {
		$user->awardTitle(NESTEGG_FLAIR);
	}
	if (in_array(133, $achievements)) {
		$user->awardTitle(RBYGEM_FLAIR);
	}
	if (in_array(140, $achievements)) {
		$user->awardTitle(BUBBLE_FLAIR);
	}
	if (in_array(146, $achievements)) {
		$user->awardTitle(ANVIL_FLAIR);
	}
	if (in_array(142, $achievements) && in_array(150, $achievements)) {
		$user->awardTitle(FIREBALL_FLAIR);
	}
	if (in_array(138, $achievements)) {
		$user->awardTitle(TITLE_THE_SPEEDSTER);
	}
	if (in_array(130, $achievements)) {
		$user->awardTitle(TITLE_100_AWESOME);
	}

	$allpq = [125, 126, 127, 128, 131, 132, 133, 134, 135, 136, 137, 138, 140, 141, 142, 144, 145, 146, 147, 148, 149, 150, 151, 152, 153, 154, 155]; //excluding hidden and awesome
	//Easy compare
	if (count(array_intersect($achievements, $allpq)) === count($allpq)) {
		$user->awardTitle(TITLE_THE_OVERACHIEVER);

		//Egg achievements
		$query = $db->prepare("
			SELECT COUNT(*) FROM (
				SELECT ex82r_user_eggs.mission_id FROM ex82r_user_eggs
				JOIN ex82r_missions ON ex82r_user_eggs.mission_id = ex82r_missions.id
				JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
				WHERE `user_id` = :user_id
				  AND `has_egg`
				  AND `ex82r_missions`.`game_id` = 4
				  AND disabled = 0
                  AND normally_hidden = 0
				GROUP BY ex82r_user_eggs.mission_id
			) AS egg_missions
		");
		$query->bindValue(":user_id", $user->id);
		$query->execute();
		$gameEggs = $query->fetchColumn(0);

		if ($gameEggs == 53) {
			$query = $db->prepare("
				SELECT
					-- Count of all challenge scores/times except PHP knows what the bit flags are
				    SUM(CASE WHEN `modifiers` & :mod_ultimate != 0 THEN 1 ELSE 0 END) AS `ultimate_count`
				FROM (
					-- Need to get first score id with this score as otherwise this will return
					-- 2 rows if someone gets the same time twice.
				    SELECT
				        `bests`.`mission_id`, MIN(ex82r_user_scores.`id`) AS `first`
				    FROM (
				        -- Select all scores
				        SELECT ex82r_user_scores.`mission_id`, MIN(`sort`) AS `minSort`
				        FROM ex82r_user_scores
				        JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
				        JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
				        WHERE `user_id` = :user_id
				        AND `game_id` = 4
						AND ex82r_mission_rating_info.disabled = 0
                        AND ex82r_mission_rating_info.normally_hidden = 0
				        GROUP BY `mission_id`
				    ) AS `bests`
				    -- Join the scores table so we can get the id of the score
				    JOIN ex82r_user_scores
				      ON ex82r_user_scores.`mission_id` = `bests`.`mission_id`
				     AND ex82r_user_scores.`sort` = `bests`.`minSort`
				    GROUP BY `mission_id`
				) AS `uniques`
				-- Join the scores table again so we can get score info
				JOIN ex82r_user_scores ON ex82r_user_scores.`id` = `first`
				JOIN `ex82r_missions` ON `uniques`.`mission_id` = `ex82r_missions`.`id`
		        JOIN `ex82r_mission_rating_info` ON `ex82r_missions`.`id` = `ex82r_mission_rating_info`.`mission_id`
			");
			$query->bindValue(":mod_ultimate", Modifiers::BeatUltimateTime | Modifiers::BeatUltimateScore);
			$query->bindValue(":user_id", $user->id);
			$query->execute();
			$ults = $query->fetchColumn(0);

			if ($ults == 138) {
				$user->awardTitle(TITLE_THE_COMPLETIONIST);
			}
		}
	}

	//Colored name: beating every platinum score/time (have fun yall)
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_missions
		  JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
		WHERE
		  game_id != 5 -- Custom levels
		  AND difficulty_id != 16 -- PQ Bonus
		  AND game_type = 'Single Player' -- Ignore MP
		  AND ex82r_missions.id NOT IN (
		    SELECT DISTINCT ex82r_missions.id FROM ex82r_missions
		      JOIN ex82r_user_scores ON ex82r_missions.id = ex82r_user_scores.mission_id
		      JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
		      JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
		    WHERE user_id = :user_id
		          AND game_type = 'Single Player' -- Ignore MP
		          AND is_custom = 0 -- Duh
		          AND game_id != 5 -- Custom levels
		          AND difficulty_id != 16 -- PQ Bonus
		          AND ((
		            score_type = 'time' AND (score < platinum_time -- Beat platinum time
		            OR platinum_time = 0 OR platinum_time IS NULL)
		          ) OR (
		            score_type = 'score' AND (score >= platinum_score -- Reached platinum score
		            OR platinum_score = 0 OR platinum_score IS NULL)
		          ))
		  )
	");
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	if ($query->fetchColumn(0) == 0 && !$user->getHasColor()) {
		$user->awardColor();
	}
}

function updateHalloweenAchievements(User $user) {
	global $db;
	if ($db->getSetting("halloween_event") !== "true" && !Login::isPrivilege("pq.test.frightfest")) {
		return;
	}
	//2000: Participate in the Frightfest Event.
//	grant($user, 2000);

	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_matches
		  JOIN ex82r_match_scores ON ex82r_matches.id = ex82r_match_scores.match_id
		  JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		  JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
		WHERE ex82r_match_scores.user_id = :user_id
		      AND ex82r_missions.game_id = :spooky_game_id
		      AND ex82r_match_scores.placement = 1
			  AND player_count > 1
			  AND extra_modes LIKE '%ghosts%'
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":spooky_game_id", 18);
	$query->execute();
	$ghostWins = $query->fetchColumn(0);
	//2001: Win a round while Ghost Hunt and Ratings are enabled - 2 or more players
	if ($ghostWins > 0) {
		grant($user, 2001);
	}
	//2002: Win 10 rounds while Ghost Hunt and Ratings are enabled - 2 or more players
	if ($ghostWins >= 10) {
		grant($user, 2002);
	}
	//2018: Win 100 rounds while Ghost Hunt and Ratings are enabled - 2 or more players
	if ($ghostWins >= 100) {
		grant($user, 2018);
	}

	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_matches
		  JOIN ex82r_match_scores ON ex82r_matches.id = ex82r_match_scores.match_id
		  JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		  JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
		WHERE ex82r_match_scores.user_id = :user_id
		      AND ex82r_missions.game_id = :spooky_game_id
			  AND player_count > 1
			  AND extra_modes LIKE '%ghosts%'
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":spooky_game_id", 18);
	$query->execute();
	//2019: Play 250 rounds while Ghost Hunt and Ratings are enabled - 2 or more players - Doesn't have to win, just score > 0 points.
	if ($query->fetchColumn(0) >= 250) {
		grant($user, 2019);
	}

	//2003: Play and finish one game on each of the halloween multiplayer levels with ratings enabled and 2 or more players.
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_missions
		JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
		WHERE ex82r_missions.id NOT IN (
		  SELECT ex82r_missions.`id`
		  FROM ex82r_matches
		    JOIN ex82r_match_scores ON ex82r_matches.id = ex82r_match_scores.match_id
		    JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
		    JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
		  WHERE rating_column = 'rating_mp'
		        AND is_custom = 0
		        AND game_id = :spooky_game_id
		        AND user_id = :user_id
		        AND player_count > 1
		        AND placement = 1
		) AND rating_column = 'rating_mp'
		AND is_custom = 0
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":spooky_game_id", 18);
	$query->execute();
	$count = $query->fetchColumn(0);
	if ($count == 0) {
		grant($user, 2003);
	}

	//2004: Play and beat the platinum score on at least 5 halloween multiplayer levels with ratings enabled. DO NOT NEED 2 OR MORE PLAYERS!
	$query = $db->prepare("
		SELECT * FROM ex82r_user_scores
		  JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
		  JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
		WHERE game_id = :spooky_game_id
		  AND ex82r_user_scores.score_type = 'score'
		  AND ex82r_user_scores.score >= ex82r_mission_rating_info.platinum_score
		  AND user_id = :user_id
		GROUP BY ex82r_user_scores.mission_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":spooky_game_id", 18);
	$query->execute();
	$count = $query->rowCount();
	if ($count >= 5) {
		grant($user, 2004);
	}

	//2005: Play and beat the ultimate score on at least 5 halloween multiplayer levels with ratings enabled. DO NOT NEED 2 OR MORE PLAYERS!
	$query = $db->prepare("
		SELECT * FROM ex82r_user_scores
		  JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
		  JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
		WHERE game_id = :spooky_game_id
		  AND ex82r_user_scores.score_type = 'score'
		  AND ex82r_user_scores.score >= ex82r_mission_rating_info.ultimate_score
		  AND user_id = :user_id
		GROUP BY ex82r_user_scores.mission_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":spooky_game_id", 18);
	$query->execute();
	$count = $query->rowCount();
	if ($count >= 5) {
		grant($user, 2005);
	}

	//2008: Candycorns... need a total 16 easter eggs in the halloween levels
	$query = $db->prepare("
		SELECT COUNT(*) FROM (
			SELECT mission_id FROM ex82r_user_eggs
			JOIN ex82r_missions ON ex82r_user_eggs.`mission_id` = ex82r_missions.`id`
			JOIN `ex82r_mission_games` ON ex82r_missions.`game_id` = `ex82r_mission_games`.`id`
			WHERE `user_id` = :user_id
			AND `game_type` = 'Multiplayer' AND `is_custom` = 0
			AND game_id = :spooky_game_id
		    GROUP BY mission_id
		) AS egg_missions
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":spooky_game_id", 18);
	$query->execute();
	if ($query->fetchColumn(0) == 16) {
		grant($user, 2008);
	}

	//2009: Total gem counts for spooky rated matches should be > 2000
	$query = $db->prepare("
		SELECT SUM(gem_count) FROM ex82r_matches
		JOIN ex82r_match_scores ON ex82r_matches.id = ex82r_match_scores.match_id
		JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
		
		WHERE game_id = :spooky_game_id
		AND ex82r_user_scores.user_id = :user_id
		AND player_count > 1
		AND is_custom = 0
	");
	$query->bindValue(":spooky_game_id", 18);
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	$spookyGemTotal = $query->fetchColumn(0);
	if ($spookyGemTotal >= 2000) {
		grant($user, 2009);
	}

	//2006: Check if player has Trigger ID 9001
	if (getHasEventTrigger($user, 9001)) { grant($user, 2006); }
	//2015: Check if player has Trigger ID 1750
	if (getHasEventTrigger($user, 1750)) { grant($user, 2015); }
	//2020: Check if player has Trigger ID 9002
	if (getHasEventTrigger($user, 9002)) { grant($user, 2020); }
	//2007: Check if player has Trigger ID 8080 - 8092 INCLUSIVE
	if (getHasEventTriggerRange($user, 8080, 8092)) { grant($user, 2007); }
	//2011: Check if player has Trigger ID 1500 - 1515 INCLUSIVE
	if (getHasEventTriggerRange($user, 1500, 1515)) { grant($user, 2011); }
	//2012: Check if player has Trigger ID 1700 - 1730 INCLUSIVE
	if (getHasEventTriggerRange($user, 1700, 1730)) { grant($user, 2012); }
	//2013: Check if player has Trigger ID 1650 - 1660 INCLUSIVE
	if (getHasEventTriggerRange($user, 1650, 1660)) { grant($user, 2013); }
	//2014: Check if player has Trigger ID 1600 - 1611 INCLUSIVE
	if (getHasEventTriggerRange($user, 1600, 1611)) { grant($user, 2014); }
	//2017: Check if player has Trigger ID 1800 - 1802 INCLUSIVE
	if (getHasEventTriggerRange($user, 1800, 1802)) { grant($user, 2017); }

	//For the meta achievements
	$user->update();

	//2010: Complete 2001 - 2009
	if (in_array(2001, $user->achievements) &&
		in_array(2002, $user->achievements) &&
		in_array(2003, $user->achievements) &&
		in_array(2004, $user->achievements) &&
		in_array(2005, $user->achievements) &&
		in_array(2006, $user->achievements) &&
		in_array(2007, $user->achievements) &&
		in_array(2008, $user->achievements) &&
		in_array(2009, $user->achievements)) {
		grant($user, 2010);
	}
	//2016: Complete 2011 - 2015
	if (in_array(2011, $user->achievements) &&
		in_array(2012, $user->achievements) &&
		in_array(2013, $user->achievements) &&
		in_array(2014, $user->achievements) &&
		in_array(2015, $user->achievements)) {
		grant($user, 2016);
	}
	//2021: Complete 2010, 2016, 2017, 2018, 2019, 2020
	if (in_array(2010, $user->achievements) &&
		in_array(2016, $user->achievements) &&
		in_array(2017, $user->achievements) &&
		in_array(2018, $user->achievements) &&
		in_array(2019, $user->achievements) &&
		in_array(2020, $user->achievements)) {
		grant($user, 2021);
	}
}

function updateWinterAchievements(User $user) {
	global $db;
	if ($db->getSetting("winter_event") !== "true" && !Login::isPrivilege("pq.test.winterfest")) {
		return;
	}

	//-------------------------------------------------------------------------
	// Misc
	//-------------------------------------------------------------------------

	//3004: Easter eggs... need a total 35 easter eggs in the halloween levels
	$query = $db->prepare("
		SELECT COUNT(*) FROM (
			SELECT mission_id FROM ex82r_user_eggs
			JOIN ex82r_missions ON ex82r_user_eggs.`mission_id` = ex82r_missions.`id`
			JOIN `ex82r_mission_games` ON ex82r_missions.`game_id` = `ex82r_mission_games`.`id`
			WHERE `user_id` = :user_id
			AND `game_type` = 'Multiplayer' AND `is_custom` = 0
			AND game_id = :winter_game_id
		    GROUP BY mission_id
		) AS egg_missions
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":winter_game_id", 19);
	$query->execute();
	if ($query->fetchColumn(0) == 35) {
		grant($user, 3004);
	}

	//3000: Participate in the Frightfest Event.
//	grant($user, 3000);

	//-------------------------------------------------------------------------
	// Mission-specific
	//-------------------------------------------------------------------------

	//3002: Win a match against at least three other players on the frozen pipes of Skate Battle Royale.
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_match_scores
		  JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		  JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
		  JOIN ex82r_matches ON ex82r_match_scores.match_id = ex82r_matches.id
		WHERE game_id = :winter_game_id
		  AND ex82r_match_scores.user_id = :user_id
		  AND player_count > 3
		  AND placement = 1
		  AND ex82r_matches.mission_id = :mission_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":winter_game_id", 19);
	$query->bindValue(":mission_id", 6030);
	$query->execute();
	if ($query->fetchColumn(0) > 0) {
		grant($user, 3002);
	}

	//3003: Win a Teams match on the icy plains of Spires
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_match_scores
		  JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		  JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
		  JOIN ex82r_matches ON ex82r_match_scores.match_id = ex82r_matches.id
		WHERE game_id = :winter_game_id
		  AND ex82r_match_scores.user_id = :user_id
		  AND player_count > 1
		  AND team_id IS NOT NULL
		  AND placement = 1
		  AND ex82r_matches.mission_id = :mission_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":winter_game_id", 19);
	$query->bindValue(":mission_id", 6053);
	$query->execute();
	if ($query->fetchColumn(0) > 0) {
		grant($user, 3003);
	}

	//3015: Win a Snowball-Only Teams match on Snow Brawl where there are at least 2 teams
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_match_scores
		  JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		  JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
		  JOIN ex82r_matches ON ex82r_match_scores.match_id = ex82r_matches.id
		WHERE game_id = :winter_game_id
		  AND ex82r_match_scores.user_id = :user_id
		  AND player_count > 1
		  AND team_id IS NOT NULL
		  AND team_count >= 2
		  AND extra_modes LIKE '%snowballsOnly%'
		  AND placement = 1
		  AND ex82r_matches.mission_id = :mission_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":winter_game_id", 19);
	$query->bindValue(":mission_id", 6041);
	$query->execute();
	if ($query->fetchColumn(0) > 0) {
		grant($user, 3015);
	}

	//3020: Won a Snowball-Only round of Wintry Village
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_match_scores
		  JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		  JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
		  JOIN ex82r_matches ON ex82r_match_scores.match_id = ex82r_matches.id
		WHERE game_id = :winter_game_id
		  AND ex82r_match_scores.user_id = :user_id
		  AND player_count > 1
		  AND extra_modes LIKE '%snowballsOnly%'
		  AND placement = 1
		  AND ex82r_matches.mission_id = :mission_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":winter_game_id", 19);
	$query->bindValue(":mission_id", 6051);
	$query->execute();
	if ($query->fetchColumn(0) > 0) {
		grant($user, 3020);
	}

	//-------------------------------------------------------------------------
	// Beat X Y-scores
	//-------------------------------------------------------------------------

	//3005: Beat any 5 Frozen Scores on the frozen Multiplayer levels, in versus mode.
	$query = $db->prepare("
		SELECT * FROM ex82r_match_scores
		  JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		  JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
		  JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
		  JOIN ex82r_matches ON ex82r_match_scores.match_id = ex82r_matches.id
		WHERE game_id = :winter_game_id
		  AND ex82r_user_scores.score_type = 'score'
		  AND ex82r_user_scores.score >= ex82r_mission_rating_info.ultimate_score
		  AND ex82r_match_scores.user_id = :user_id
		  AND player_count > 1
		GROUP BY ex82r_user_scores.mission_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":winter_game_id", 19);
	$query->execute();
	$count = $query->rowCount();
	if ($count >= 5) {
		grant($user, 3005);
	}

	//3013: Beat at least 20 Chilly Scores on the Frozen Multiplayer levels
	$query = $db->prepare("
		SELECT * FROM ex82r_user_scores
		  JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
		  JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
		WHERE game_id = :winter_game_id
		  AND ex82r_user_scores.score_type = 'score'
		  AND ex82r_user_scores.score >= ex82r_mission_rating_info.platinum_score
		  AND user_id = :user_id
		GROUP BY ex82r_user_scores.mission_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":winter_game_id", 19);
	$query->execute();
	$count = $query->rowCount();
	if ($count >= 20) {
		grant($user, 3013);
	}

	//3014: Beat at least 20 Frozen Scores on the Frozen Multiplayer levels
	$query = $db->prepare("
		SELECT * FROM ex82r_user_scores
		  JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
		  JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
		WHERE game_id = :winter_game_id
		  AND ex82r_user_scores.score_type = 'score'
		  AND ex82r_user_scores.score >= ex82r_mission_rating_info.ultimate_score
		  AND user_id = :user_id
		GROUP BY ex82r_user_scores.mission_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":winter_game_id", 19);
	$query->execute();
	$count = $query->rowCount();
	if ($count >= 20) {
		grant($user, 3014);
	}

	//Win a Winterfest FFA Match by at least 100 points
	$query = $db->prepare("
		SELECT COUNT(*) FROM ex82r_match_scores AS score
		  JOIN ex82r_matches ON score.match_id = ex82r_matches.id
		  JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
		  JOIN ex82r_user_scores ON score.score_id = ex82r_user_scores.id
		-- All scores where:
		WHERE score.user_id = :user_id
		  AND is_custom = 0
		  AND game_id = :winter_game_id
		  AND player_count > 1
		-- The next highest person's score 
		  AND (
		    SELECT MAX(ex82r_user_scores.score) FROM ex82r_match_scores
		      JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		      WHERE ex82r_match_scores.match_id = score.match_id
		      AND ex82r_match_scores.user_id != :user_id2 -- Because you can't use the same param twice
		-- Is less than your score sub 100
		) < (ex82r_user_scores.score - 100)
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":user_id2", $user->id);
	$query->bindValue(":winter_game_id", 19);
	$query->execute();
	if ($query->fetchColumn(0) > 0) {
		grant($user, 79);
	}
	//-------------------------------------------------------------------------
	// Snowballs related
	//-------------------------------------------------------------------------

	//3007: Launch 3,000 Snowballs
	$query = $db->prepare("
		SELECT SUM(snowballs) FROM ex82r_user_event_snowballs
		JOIN ex82r_user_scores ON ex82r_user_event_snowballs.score_id = ex82r_user_scores.id
		WHERE user_id = :user_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	if ($query->fetchColumn(0) >= 3000) {
		grant($user, 3007);
	}

	//3008: Hit other players a total of 500 times with Snowballs
	$query = $db->prepare("
		SELECT SUM(snowballs) FROM ex82r_user_event_snowballs
		JOIN ex82r_user_scores ON ex82r_user_event_snowballs.score_id = ex82r_user_scores.id
		WHERE user_id = :user_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	if ($query->fetchColumn(0) >= 500) {
		grant($user, 3008);
	}


	//3030: Win 10 rounds of Snowball-Only mode on any Winterfest level.
	//3031: Win 25 rounds of Snowball-Only mode on any Winterfest level.
	//3032: Win 100 rounds of Snowball-Only mode on any Winterfest level.
	$query = $db->prepare("
		SELECT * FROM ex82r_match_scores
		  JOIN ex82r_user_scores ON ex82r_match_scores.score_id = ex82r_user_scores.id
		  JOIN ex82r_missions ON ex82r_user_scores.mission_id = ex82r_missions.id
		  JOIN ex82r_matches ON ex82r_match_scores.match_id = ex82r_matches.id
		WHERE game_id = :winter_game_id
		  AND ex82r_match_scores.user_id = :user_id
		  AND player_count > 1
		  AND extra_modes LIKE '%snowballsOnly%'
		  AND placement = 1
		GROUP BY ex82r_matches.mission_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":winter_game_id", 19);
	$query->execute();
	$scores = $query->rowCount();
	if ($scores > 10) {
		grant($user, 3030);
	}
	if ($scores > 25) {
		grant($user, 3031);
	}
	if ($scores > 100) {
		grant($user, 3032);
	}

	//-------------------------------------------------------------------------
	// Trigger based (easy)
	//-------------------------------------------------------------------------

	//3025: Check if player has Trigger ID 1105
	if (getHasEventTrigger($user, 1105)) { grant($user, 3025); }
	//3029: Check if player has Trigger ID 1112
	if (getHasEventTrigger($user, 1112)) { grant($user, 3029); }
	//3009: Check if player has at least one Trigger between 8101 - 8129
	if (eventTriggerRangeCount($user, 8101, 8129) >= 1) { grant($user, 3009); }
	//3010: Check if player has at least seven Triggers between 8101 - 8129
	if (eventTriggerRangeCount($user, 8101, 8129) >= 7) { grant($user, 3010); }
	//3011: Check if player has at least fourteen Triggers between 8101 - 8129
	if (eventTriggerRangeCount($user, 8101, 8129) >= 14) { grant($user, 3011); }
	//3012: Check if player has all of the Santa Triggers (between 8101 - 8129)
	if (getHasEventTriggerRange($user, 8101, 8129)) { grant($user, 3012); }
	//3016: Check if player has triggers 1020 through 1042 INCLUSIVE
	if (getHasEventTriggerRange($user, 1020, 1042)) { grant($user, 3016); }
	//3017: Check if player has triggers 1000 through 1019 INCLUSIVE
	if (getHasEventTriggerRange($user, 1000, 1019)) { grant($user, 3017); }
	//3018: Check if player has triggers 1043 through 1074 INCLUSIVE
	if (getHasEventTriggerRange($user, 1043, 1074)) { grant($user, 3018); }
	//3019: Check if player has triggers 1075 through 1084 INCLUSIVE
	if (getHasEventTriggerRange($user, 1075, 1084)) { grant($user, 3019); }
	//3024: Check if player has triggers 1102, 1103, and 1104.
	if (getHasEventTriggerRange($user, 1102, 1104)) { grant($user, 3024); }
	//3026: Check if player has trigger 1106, 1107, 1108, 1109, 1110, and 1111.
	if (getHasEventTriggerRange($user, 1106, 1111)) { grant($user, 3026); }
	//3027: Check if player has triggers 1113, 1114, 1115, 1116, 1117, 1118
	if (getHasEventTriggerRange($user, 1113, 1118)) { grant($user, 3027); }
	//3028: Check if player has triggers 1119 through 1148 INCLUSIVE
	if (getHasEventTriggerRange($user, 1119, 1148)) { grant($user, 3028); }

	//-------------------------------------------------------------------------
	// Meta
	//-------------------------------------------------------------------------
	$user->update();

	//3021: Check if player has achievements 3001, 3004, 3011, 3016, 3017, 3018, 3019
	if (in_array(3001, $user->achievements) &&
		in_array(3004, $user->achievements) &&
		in_array(3011, $user->achievements) &&
		in_array(3016, $user->achievements) &&
		in_array(3017, $user->achievements) &&
		in_array(3018, $user->achievements) &&
		in_array(3019, $user->achievements)) {
		grant($user, 3021);
	}
	//3022: Check if player has achievements 3002, 3003, 3005, 3006, 3007, 3008, 3013, 3014, 3015, 3015
	if (in_array(3002, $user->achievements) &&
		in_array(3003, $user->achievements) &&
		in_array(3005, $user->achievements) &&
		in_array(3006, $user->achievements) &&
		in_array(3007, $user->achievements) &&
		in_array(3008, $user->achievements) &&
		in_array(3013, $user->achievements) &&
		in_array(3014, $user->achievements) &&
		in_array(3015, $user->achievements) &&
		in_array(3015, $user->achievements)) {
		grant($user, 3022);
	}
	//3023: Check if player has achievements 3021, 3022
	if (in_array(3021, $user->achievements) &&
		in_array(3022, $user->achievements)) {
		grant($user, 3023);
	}
}

/**
 * Get's the count of how many times a user won a
 * multiplayer match on a specific level.
 * @param User   $user
 * @param string $level Mission basename
 * @return int The number of wins on $level.
 */
function getNumberOfWinsOnLevel(User $user, $level) {
    global $db;
    $query = $db->prepare("
        SELECT COUNT(*)
          FROM ex82r_match_scores
          JOIN ex82r_matches ON ex82r_match_scores.match_id = ex82r_matches.id
          JOIN ex82r_missions ON ex82r_matches.mission_id = ex82r_missions.id
        WHERE
          ex82r_match_scores.user_id = :user_id
          AND ex82r_matches.player_count > 1
          AND ex82r_match_scores.placement = 1
          AND ex82r_missions.basename = :base_name
    ");
    $query->bindValue(":user_id", $user->id);
    $query->bindValue(":base_name", $level);
    $query->execute();
    return $query->fetchColumn(0);
}

/**
 * Get the user's best score with the given modifiers on a mission
 * @param User   $user
 * @param string $level     Mission basename
 * @param int    $modifiers Modifier flags to check
 * @return int Best score or NO_TIME if none exists
 */
function bestModifiers(User $user, $level, $modifiers = 0) {
	$best = $user->getBestScores(Mission::getByBasename($level), 1, $modifiers);
	if (count($best) == 0) {
		return NO_TIME;
	}
	return $best[0]["score"];
}

/**
 * Extract best score from a best scores array returned from getBestScores()
 * @param array  $bests Result of getBestScores()
 * @param string $level Level basename
 * @return int Score, or NO_TIME if not found
 */
function best($bests, $level) {
	if (array_key_exists($level, $bests)) {
		return $bests[$level]["score"];
	}
	return NO_TIME;
}

/**
 * Get an associative array of a user's best scores (modifiers irrelevant)
 * @param User $user
 * @return array Best scores as basename => [score => score, score_type => type]
 */
function getBestScores(User $user) {
	global $db;
	$query = $db->prepare("
		SELECT DISTINCT ex82r_missions.`basename`, `score`, `score_type` FROM
		-- Select all time scores
		(
			SELECT `mission_id`, MIN(`sort`) AS `minSort`
			FROM ex82r_user_scores
			WHERE `user_id` = :user_id
			GROUP BY `mission_id`
		) AS `bests`
		-- Join the scores table so we can get other info
		JOIN ex82r_user_scores
		  ON ex82r_user_scores.`mission_id` = `bests`.`mission_id`
		 AND ex82r_user_scores.`sort` = `bests`.`minSort`
		JOIN ex82r_missions
		  ON ex82r_missions.`id` = `bests`.`mission_id`
		JOIN `ex82r_mission_games`
		  ON ex82r_missions.`game_id` = `ex82r_mission_games`.`id`
		WHERE `game_type` = 'Single Player'
		AND `is_custom` = 0
	");
	$query->bindValue(":user_id", $user->id);
	$query->execute();
	$scores = $query->fetchAll(PDO::FETCH_ASSOC);

	//Make it associative
	$result = [];
	foreach ($scores as $score) {
		$result[$score["basename"]] = $score;
	}
	return $result;
}


function getHasEventTrigger(User $user, $id) {
	global $db;
	$query = $db->prepare("
		SELECT COUNT(*) FROM `ex82r_user_event_triggers` WHERE `trigger` = :id AND `user_id` = :user_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":id", $id);
	$query->execute();

	return ($query->fetchColumn(0) > 0);
}

function getHasEventTriggerRange(User $user, $idStart, $idEnd) {
	global $db;
	$query = $db->prepare("
		SELECT COUNT(*) FROM `ex82r_user_event_triggers`
		WHERE `trigger` >= :start AND `trigger` <= :end AND `user_id` = :user_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":start", $idStart);
	$query->bindValue(":end", $idEnd);
	$query->execute();

	//Inclusive
	$all = ($idEnd - $idStart + 1);
	return $query->fetchColumn(0) >= $all;
}

function eventTriggerRangeCount(User $user, $idStart, $idEnd) {
	global $db;
	$query = $db->prepare("
		SELECT COUNT(*) FROM `ex82r_user_event_triggers`
		WHERE `trigger` >= :start AND `trigger` <= :end AND `user_id` = :user_id
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":start", $idStart);
	$query->bindValue(":end", $idEnd);
	$query->execute();

	return $query->fetchColumn(0);
}


/**
 * Grant a user an achievement. Note: does nothing if the user already has that achievement.
 * @param User $user        User to grant
 * @param int  $achievement Achievement id
 */
function grant(User $user, $achievement) {
	global $db;

	//Check the achievement for any flair award
	$query = $db->prepare("SELECT `reward_flair` FROM `ex82r_achievement_names` WHERE `id` = :id");
	$query->bindValue(":id", $achievement);
	requireExecute($query);
	$rewardFlair = $query->fetchColumn(0);
	if ($rewardFlair != null) {
		$user->awardTitle($rewardFlair);
	}

	//Don't give achievements twice
	if (in_array($achievement, $user->achievements)) {
		return;
	}
	//But do grant ones that we've earned
	$query = $db->prepare("
		INSERT INTO `ex82r_user_achievements` (user_id, achievement_id)
		VALUES (:user_id, :achievement_id)
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":achievement_id", $achievement);
	requireExecute($query);
	$user->achievements[] = $achievement;

	//Find its value
	$query = $db->prepare("SELECT `rating` FROM `ex82r_achievement_names` WHERE `id` = :id");
	$query->bindValue(":id", $achievement);
	requireExecute($query);
	$rating = $query->fetchColumn(0);

	//And give us that much
	$query = $db->prepare("
		UPDATE `ex82r_user_ratings` SET
			`rating_achievement` = `rating_achievement` + :rating,
			`rating_general` = `rating_general` + :rating2
		WHERE `user_id` = :user_id
	");
	$query->bindValue(":rating", $rating);
	$query->bindValue(":rating2", $rating);
	$query->bindValue(":user_id", $user->id);
	requireExecute($query);

	$user->update();
}
