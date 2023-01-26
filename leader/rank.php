<?php
$allow_nonwebchat = false;

// get the total rank in lbs
function getTotalRank($username) {
	$query = pdo_prepare("SELECT COUNT(*) FROM `users` WHERE `rating` > (SELECT `rating` FROM `users` WHERE `username` = :username) AND `showscores` = 1 AND `banned` = 0");
	$query->bind(":username", $username);
	return $query->execute()->fetchIdx(0) + 1;
}

// get the total rank of mbp in lbs
function getMBPRank($username) {
	$query = pdo_prepare("SELECT COUNT(*) FROM `users` WHERE `rating_mbp` > (SELECT `rating_mbp` FROM `users` WHERE `username` = :username) AND `showscores` = 1 AND `banned` = 0");
	$query->bind(":username", $username);
	return $query->execute()->fetchIdx(0) + 1;
}

// get the total rank of mbg in lbs
function getMBGRank($username) {
	$query = pdo_prepare("SELECT COUNT(*) FROM `users` WHERE `rating_mbg` > (SELECT `rating_mbg` FROM `users` WHERE `username` = :username) AND `showscores` = 1 AND `banned` = 0");
	$query->bind(":username", $username);
	return $query->execute()->fetchIdx(0) + 1;
}

// get the total rank of mbu in lbs
function getMBURank($username) {
	$query = pdo_prepare("SELECT COUNT(*) FROM `users` WHERE `rating_mbu` > (SELECT `rating_mbu` FROM `users` WHERE `username` = :username) AND `showscores` = 1 AND `banned` = 0");
	$query->bind(":username", $username);
	return $query->execute()->fetchIdx(0) + 1;
}

// get the total rank of custom in lbs
function getCustomRank($username) {
	$query = pdo_prepare("SELECT COUNT(*) FROM `users` WHERE `rating_custom` > (SELECT `rating_custom` FROM `users` WHERE `username` = :username) AND `showscores` = 1 AND `banned` = 0");
	$query->bind(":username", $username);
	return $query->execute()->fetchIdx(0) + 1;
}

// get the total rank of multiplayer in lbs
function getMultiplayerRank($username) {
	$games = userField($username, "rating_mpgames");
	if ($games < getServerPref("provisgames"))
		return "Pvs";

	$query = pdo_prepare("SELECT COUNT(*) FROM `users` WHERE `rating_mp` > (SELECT `rating_mp` FROM `users` WHERE `username` = :username) AND `showscores` = 1 AND `banned` = 0");
	$query->bind(":username", $username);
	return $query->execute()->fetchIdx(0) + 1;
}

// get the total rank of challenge points in lbs
function getChallengeRank($username) {
	$query = pdo_prepare("SELECT COUNT(*) FROM `users` WHERE `challengepoints` > (SELECT `challengepoints` FROM `users` WHERE `username` = :username) AND `showscores` = 1 AND `banned` = 0");
	$query->bind(":username", $username);
	return $query->execute()->fetchIdx(0) + 1;
}
?>
