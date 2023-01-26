<?php

class SPRatings {
	/**
	 * Get how many rating points a score will earn you for a given mission
	 * @param array   $scoreInfo The score
	 * @param Mission $mission   A mission's info, result of calling getMissionInfoBy*
	 * @return int The rating for that time
	 */
	static function getScoreRating(&$scoreInfo, Mission $mission) {
		//Customs (or stuff without a rating column) give you nothing
		if ($mission->gameInfo["rating_column"] === null) {
			$scoreInfo["rating"] = 0;
			return 0;
		}
		//Custom missions give you nothing
		if ($mission->custom) {
			$scoreInfo["rating"] = 0;
			return 0;
		}

		//Combine the mission info with rating info
		$scoreInfo["rating_column"] = $mission->gameInfo["rating_column"];

		$gameMode = $mission->gamemode;
		//Base mode is the first one
		$baseMode = strtolower(getWord($gameMode, 0));
		switch ($baseMode) {
		case "null":
			$rating = self::getNullScoreRating($scoreInfo, $mission);
			break;
		case "hunt":
			$rating = self::getHuntScoreRating($scoreInfo, $mission);
			break;
		case "gemmadness":
			$rating = self::getGemMadnessScoreRating($scoreInfo, $mission);
			break;
		case "quota":
			$rating = self::getQuotaScoreRating($scoreInfo, $mission);
			break;
		default:
			//Unknown game mode, just use null mode
			$rating = self::getNullScoreRating($scoreInfo, $mission);
			break;
		}

		$scoreInfo["rating"] = $rating;

		return $rating;
	}

	static function getNullScoreRating(&$scoreInfo, Mission $mission) {
		//Score is the "score" field from the query
		$score = $scoreInfo["score"];

		$ratingInfo = $mission->ratingInfo;

		if ($ratingInfo["disabled"] == 1) {
			return 0;
		}
		//Some quick bounds checking
		if ($score < $ratingInfo["time_offset"]) {
			return -2; //Bad Score
		}

		// I just copied this all from 1.14
		// No comments, just the guts <auto-generated>
		$parTime         = $ratingInfo["par_time"];
		$platinumTime    = $ratingInfo["platinum_time"];
		$ultimateTime    = $ratingInfo["ultimate_time"];
		$awesomeTime     = $ratingInfo["awesome_time"];
		$completionBonus = $ratingInfo["completion_bonus"];

		//Levels with a difficulty automatically change their bonus
		$completionBonus *= $ratingInfo["difficulty"];

		//This is the time used for calculating your score. If you got under par (and a par exists)
		// then your score will just be the score at par time, because the if-statement below will
		// reduce it linearly.
		if ($parTime > 0) {
			$scoreTime = min($score, $parTime) / 1000;
		} else {
			$scoreTime = $score / 1000;
		}
		$scoreTime -= $ratingInfo["time_offset"] / 1000;
		$scoreTime += 0.1;

		//You instantly get bonus points if you beat a challenge time
		$bonus = 0;
		if ($platinumTime && $score < $platinumTime) {
			$bonus += $ratingInfo["platinum_bonus"] * $ratingInfo["platinum_difficulty"];
			$scoreInfo["modifiers"] |= Modifiers::BeatPlatinumTime;
		}
		if ($ultimateTime && $score < $ultimateTime) {
			$bonus += $ratingInfo["ultimate_bonus"] * $ratingInfo["ultimate_difficulty"];
			$scoreInfo["modifiers"] |= Modifiers::BeatUltimateTime;
		}
		if ($awesomeTime && $score < $awesomeTime) {
			$bonus += $ratingInfo["awesome_bonus"] * $ratingInfo["awesome_difficulty"];
			$scoreInfo["modifiers"] |= Modifiers::BeatAwesomeTime;
		}

		$standardiser      = $ratingInfo["standardiser"];
		$setBaseScore      = $ratingInfo["set_base_score"];
		$multiplierSetBase = $ratingInfo["multiplier_set_base"];

		//(completion base score+(Platinum×platinum bonus)+(On Ult×platinum bonus)+(Ultimate×platinum bonus)+(Ultimate×ultimate bonus)+((LOG(Time,10)×Standardiser)−base score)×−1)×multiplier

		// Spy47 : Awesome formula (not made by me).
		$rating = ($completionBonus + $bonus + (((log10($scoreTime) * $standardiser) - $setBaseScore) * - 1)) *
		          $multiplierSetBase;

		//If they get over the par time, linearly decrease the number of points they'll get until you hit 0
		if ($score > $parTime && ($parTime > 0)) {
			//Number of points you will lose per second over par. It just divides the score at par
			// by the seconds after par until 99:59.999 (which gives a score of 0).
			$lostPerSec = ($rating - 1) / (5999.999 - ($parTime / 1000));

			//How many seconds over par you are
			$overPar = max($score - $parTime, 0) / 1000;

			//Just multiply them and that's how many points you lose
			$rating -= $overPar * $lostPerSec;
		} else {
			//Under par, flag it
			$scoreInfo["modifiers"] |= Modifiers::BeatParTime;
		}

		// Spy47 : They'll probably commit suicide if they see a negative rating.
		$rating = floor($rating < 1 ? 1 : $rating);

		return $rating;
	}

	static function getHuntScoreRating(&$scoreInfo, Mission $mission) {
		//Easier access
		$score      = $scoreInfo["score"];
		$ratingInfo = $mission->ratingInfo;

		//Tons of bonuses
		$bonus = $ratingInfo["hunt_completion_bonus"];
		if ($ratingInfo["par_score"] && $score >= $ratingInfo["par_score"]) {
			$bonus += $ratingInfo["hunt_par_bonus"];
			$scoreInfo["modifiers"] |= Modifiers::BeatParScore;
		}
		if ($ratingInfo["platinum_score"] && $score >= $ratingInfo["platinum_score"]) {
			$bonus += $ratingInfo["hunt_platinum_bonus"];
			$scoreInfo["modifiers"] |= Modifiers::BeatPlatinumScore;
		}
		if ($ratingInfo["ultimate_score"] && $score >= $ratingInfo["ultimate_score"]) {
			$bonus += $ratingInfo["hunt_ultimate_bonus"];
			$scoreInfo["modifiers"] |= Modifiers::BeatUltimateScore;
		}
		if ($ratingInfo["awesome_score"] && $score >= $ratingInfo["awesome_score"]) {
			$bonus += $ratingInfo["hunt_awesome_bonus"];
			$scoreInfo["modifiers"] |= Modifiers::BeatAwesomeScore;
		}

		//Rating = HuntBaseScore (ℯ^(x / HuntStandardiser) - 1) + If[x ≥ Par, ParBonus, 0] + If[x ≥ Platinum, PlatinumBonus, 0] + If[x ≥ Ultimate, UltimateBonus, 0] + If[x ≥ Awesome, AwesomeBonus, 0] + CompletionBonus
		//Or more succinctly:
		//Rating = HuntBaseScore (ℯ^(x / HuntStandardiser) - 1) + Bonuses
		$rating = floor($ratingInfo["hunt_multiplier"] * (exp($score / $ratingInfo["hunt_divisor"]) - 1) + $bonus);

		return $rating;
	}

	static function getGemMadnessScoreRating(&$scoreInfo, Mission $mission) {
		//If we have a time we got all the gems
		if ($scoreInfo["type"] === 'time') {
			$scoreInfo["modifiers"] |= Modifiers::GemMadnessAll;
		}

		//Check for not all gems, (because it's cleaner)
		if (($scoreInfo["modifiers"] & Modifiers::GemMadnessAll) == 0) {
			return self::getHuntScoreRating($scoreInfo, $mission);
		}

		//They have gotten all the hunt gems, so we need to combine the hunt rating for all gems
		// with a null rating of their time

		//Hunt rating for their points, which is just the max
		$huntInfo          = $scoreInfo;
		$huntInfo["score"] = $mission->ratingInfo["hunt_max_score"];

		$huntRating = self::getHuntScoreRating($huntInfo, $mission);
		//Copy any assigned modifiers from the hunt function
		$scoreInfo["modifiers"] |= $huntInfo["modifiers"];

		//Null rating of their time
		$nullRating = self::getNullScoreRating($scoreInfo, $mission);

		return $huntRating + $nullRating;
	}

	static function getQuotaScoreRating(&$scoreInfo, Mission $mission) {
		//Don't believe what they say about getting 100%
		$scoreInfo["modifiers"] &= ~Modifiers::QuotaHundred;
		if ($scoreInfo["gemCount"] == $mission->ratingInfo["gem_count"]) {
			//They did get 100%
			$scoreInfo["modifiers"] |= Modifiers::QuotaHundred;
			$scoreInfo["quota_bonus"] = $mission->ratingInfo["quota_100_bonus"];
		}

		//Just null
		return self::getNullScoreRating($scoreInfo, $mission);
	}
}
