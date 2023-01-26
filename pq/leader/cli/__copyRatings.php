<?php
define("PQ_RUN", true);
require_once("../Framework.php");

function copyRatings($oldInfo) {
	global $db;

	//Try to find it in the new database
	$newInfo = Mission::getByBasename($oldInfo["file"]);

	$type = $oldInfo["type"];
	$game = $oldInfo["game"];
	$completion = 0;

	//PQ:
	//Tutorial: 1000
	//Beginner: 1000
	//Intermediate: 2000
	//Advanced: 3000
	//Expert: 5000
	//Bonus: 4000

	if (strtolower($type) == "beginner")
		$completion += ($game == "Ultra" ? 1500 : 1000);
	if (strtolower($type) == "intermediate")
		$completion += ($game == "Ultra" ? 2500 : 2000);
	if (strtolower($type) == "advanced")
		$completion += ($game == "Platinum" ? 3000 : 4000);
	if (strtolower($type) == "expert")
		$completion += 5000;
	if (strtolower($type) == "lbcustom")
		$completion += 4000;

	$create = $db->prepare("INSERT INTO `ex82r_mission_rating_info` SET
		`mission_id` = :mission_id,
		`par_time` = :par_time,
		`platinum_time` = :platinum_time,
		`ultimate_time` = :ultimate_time,
		`awesome_time` = 0,
		`par_score` = 0,
		`platinum_score` = 0,
		`ultimate_score` = 0,
		`awesome_score` = 0,
		`completion_bonus` = :completion_bonus,
		`set_base_score` = :set_base_score,
		`multiplier_set_base` = :multiplier_set_base,
		`platinum_bonus` = :platinum_bonus,
		`ultimate_bonus` = :ultimate_bonus,
		`awesome_bonus` = 0,
		`standardiser` = :standardiser,
		`difficulty` = :difficulty,
		`platinum_difficulty` = :platinum_difficulty,
		`ultimate_difficulty` = :ultimate_difficulty,
		`awesome_difficulty` = 0
	");

	$create->bindValue(":mission_id", $newInfo->id);
	$create->bindValue(":par_time", $oldInfo["qualify"]);
	$create->bindValue(":platinum_time", $oldInfo["gold"]);
	$create->bindValue(":ultimate_time", $oldInfo["ultimate"]);
	$create->bindValue(":completion_bonus", $completion);
	$create->bindValue(":set_base_score", $oldInfo["basescore"]);
	$create->bindValue(":multiplier_set_base", $oldInfo["basemultiplier"]);
	$create->bindValue(":platinum_bonus", $oldInfo["platinumbonus"]);
	$create->bindValue(":ultimate_bonus", $oldInfo["ultimatebonus"]);
	$create->bindValue(":standardiser", $oldInfo["standardiser"]);
	$create->bindValue(":difficulty", $oldInfo["difficulty"]);
	$create->bindValue(":platinum_difficulty", $oldInfo["golddifficulty"]);
	$create->bindValue(":ultimate_difficulty", $oldInfo["ultimatedifficulty"]);

	$create->execute();
}

//Clear it out
$db->query("DELETE FROM `ex82r_mission_rating_info`");

$query = $pdb->prepare("SELECT * FROM `officiallevels`");
$query->execute();
while (($oldInfo = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	copyRatings($oldInfo);
}

$query = $pdb->prepare("SELECT * FROM `levels`");
$query->execute();
while (($oldInfo = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	copyRatings($oldInfo);
}
