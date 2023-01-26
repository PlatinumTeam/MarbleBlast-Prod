<?php

class MPRatings {
	static function getScoreRatings(&$scores, Mission $mission) {
		$players = count($scores);

		//Find the best score
		$bestScore = 0;
		foreach ($scores as $player) {
			$bestScore = max($bestScore, $player["score"]);
		}
		//So we don't divide by zero
		if ($bestScore <= 0) {
			$bestScore = 1;
		}

		//Give each player ratings
		for ($i = 0; $i < $players; $i ++) {
			$player = $scores[$i];

			if ($players > 1 && $mission->gameInfo["rating_column"] === "rating_mp") {
				//You get points based on how much time you played and how many people there were
				//More playing, more time = more points
				$change = 20 * $players * $player["timePercent"];

				//1st place - 1.0
				//8th place - 0.5
				//Translates into:
				//(1 - ((place - 1) / (players - 1)) * 0.5)
				//Multiply by 10 and that's how many bonus points you get for placing
				$change += (1 - (($player["place"] - 1) / ($players - 1))) * 10;

				//Multiply your score based on how many points you got compared to the best player
				// This is a 75% factor so you'll still get some even if you scored 0
				$change *= 0.25 + (($player["score"] / $bestScore) * 0.75);

				//If you'd get under 5 points we'll take pity and give you some anyway
				if ($change < 5) {
					$change = 5;
				}

				if ($mission->custom) {
					// 1/10th points for custom levels
					$change *= 0.1;

					//Pity points
					if ($change < 1) {
						$change = 1;
					}
				}

				$scores[$i]["rating"] = round($change);
			} else {
				//No points for playing by yourself
				$scores[$i]["rating"] = 0;
			}

			//Get modifiers too
			$scores[$i]["modifiers"] = 0;
			if ($mission->ratingInfo["par_score"] &&
				$player["score"] >= $mission->ratingInfo["par_score"])
				$scores[$i]["modifiers"] |= Modifiers::BeatParScore;
			if ($mission->ratingInfo["platinum_score"] &&
				$player["score"] >= $mission->ratingInfo["platinum_score"])
				$scores[$i]["modifiers"] |= Modifiers::BeatPlatinumScore;
			if ($mission->ratingInfo["ultimate_score"] &&
				$player["score"] >= $mission->ratingInfo["ultimate_score"])
				$scores[$i]["modifiers"] |= Modifiers::BeatUltimateScore;
			if ($mission->ratingInfo["awesome_score"] &&
				$player["score"] >= $mission->ratingInfo["awesome_score"])
				$scores[$i]["modifiers"] |= Modifiers::BeatAwesomeScore;
		}
	}
}
