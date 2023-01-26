<?php

class Modifiers {
	//Various little challenges that people try
	const GotEasterEgg      = (1 <<  0);
	const NoJumping         = (1 <<  1);
	const DoubleDiamond     = (1 <<  2);
	const NoTimeTravels     = (1 <<  3);
	//Game mode-specific
	const QuotaHundred      = (1 <<  4);
	const GemMadnessAll     = (1 <<  5);
	//Challenge time-based
	const BeatParTime       = (1 <<  6);
	const BeatPlatinumTime  = (1 <<  7);
	const BeatUltimateTime  = (1 <<  8);
	const BeatAwesomeTime   = (1 <<  9);
	//Challenge score-based
	const BeatParScore      = (1 << 10);
	const BeatPlatinumScore = (1 << 11);
	const BeatUltimateScore = (1 << 12);
	const BeatAwesomeScore  = (1 << 13);
	//If the score was the world record when it was achieved (could still be WR too)
	const WasWorldRecord    = (1 << 14);
	const IsBestScore       = (1 << 15);
	const Controller        = (1 << 16);
}

/**
 * Get the "sorting value" for a score. You can order these by ASC to sort times easily.
 * @param array $scoreInfo A score info array containing at least "type" and "score"
 * @return int The sort value
 */
function getScoreSorting($scoreInfo) {
	return $scoreInfo["type"] === "time" ? $scoreInfo["score"] : 10000000 - $scoreInfo["score"];
}
