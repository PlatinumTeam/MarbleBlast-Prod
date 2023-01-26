<?php
define("PQ_RUN", true);
require_once("../Framework.php");

if (!Login::isPrivilege("pq.mod.editRatings")) {
	header("HTTP/1.1 401 Unauthorized");
	die();
}

function updateRatings($ratingInfo) {
	global $db;

	$oldInfo = Mission::getById($ratingInfo["mission_id"]);

	$create = $db->prepare("UPDATE `ex82r_mission_rating_info` SET
		`par_time` = :par_time,
		`platinum_time` = :platinum_time,
		`ultimate_time` = :ultimate_time,
		`awesome_time` = :awesome_time,
		`par_score` = :par_score,
		`platinum_score` = :platinum_score,
		`ultimate_score` = :ultimate_score,
		`awesome_score` = :awesome_score,
		`completion_bonus` = :completion_bonus,
		`set_base_score` = :set_base_score,
		`multiplier_set_base` = :multiplier_set_base,
		`platinum_bonus` = :platinum_bonus,
		`ultimate_bonus` = :ultimate_bonus,
		`awesome_bonus` = :awesome_bonus,
		`standardiser` = :standardiser,
		`time_offset` = :time_offset,
		`difficulty` = :difficulty,
		`platinum_difficulty` = :platinum_difficulty,
		`ultimate_difficulty` = :ultimate_difficulty,
		`awesome_difficulty` = :awesome_difficulty,
		`hunt_multiplier` = :hunt_multiplier,
		`hunt_divisor` = :hunt_divisor,
		`hunt_completion_bonus` = :hunt_completion_bonus,
		`hunt_par_bonus` = :hunt_par_bonus,
		`hunt_platinum_bonus` = :hunt_platinum_bonus,
		`hunt_ultimate_bonus` = :hunt_ultimate_bonus,
		`hunt_awesome_bonus` = :hunt_awesome_bonus,
		`quota_100_bonus` = :quota_100_bonus,
		`notes` = :notes
		WHERE
		`mission_id` = :mission_id
	");

	$create->bindValue(":par_time",              $ratingInfo["par_time"]);
	$create->bindValue(":platinum_time",         $ratingInfo["platinum_time"]);
	$create->bindValue(":ultimate_time",         $ratingInfo["ultimate_time"]);
	$create->bindValue(":awesome_time",          $ratingInfo["awesome_time"]);
	$create->bindValue(":par_score",             $ratingInfo["par_score"]);
	$create->bindValue(":platinum_score",        $ratingInfo["platinum_score"]);
	$create->bindValue(":ultimate_score",        $ratingInfo["ultimate_score"]);
	$create->bindValue(":awesome_score",         $ratingInfo["awesome_score"]);
	$create->bindValue(":completion_bonus",      $ratingInfo["completion_bonus"]);
	$create->bindValue(":set_base_score",        $ratingInfo["set_base_score"]);
	$create->bindValue(":multiplier_set_base",   $ratingInfo["multiplier_set_base"]);
	$create->bindValue(":platinum_bonus",        $ratingInfo["platinum_bonus"]);
	$create->bindValue(":ultimate_bonus",        $ratingInfo["ultimate_bonus"]);
	$create->bindValue(":awesome_bonus",         $ratingInfo["awesome_bonus"]);
	$create->bindValue(":standardiser",          $ratingInfo["standardiser"]);
	$create->bindValue(":time_offset",           $ratingInfo["time_offset"]);
	$create->bindValue(":difficulty",            $ratingInfo["difficulty"]);
	$create->bindValue(":platinum_difficulty",   $ratingInfo["platinum_difficulty"]);
	$create->bindValue(":ultimate_difficulty",   $ratingInfo["ultimate_difficulty"]);
	$create->bindValue(":awesome_difficulty",    $ratingInfo["awesome_difficulty"]);
	$create->bindValue(":hunt_multiplier",       $ratingInfo["hunt_multiplier"]);
	$create->bindValue(":hunt_divisor",          $ratingInfo["hunt_divisor"]);
	$create->bindValue(":hunt_completion_bonus", $ratingInfo["hunt_completion_bonus"]);
	$create->bindValue(":hunt_par_bonus",        $ratingInfo["hunt_par_bonus"]);
	$create->bindValue(":hunt_platinum_bonus",   $ratingInfo["hunt_platinum_bonus"]);
	$create->bindValue(":hunt_ultimate_bonus",   $ratingInfo["hunt_ultimate_bonus"]);
	$create->bindValue(":hunt_awesome_bonus",    $ratingInfo["hunt_awesome_bonus"]);
	$create->bindValue(":quota_100_bonus",       $ratingInfo["quota_100_bonus"]);
	$create->bindValue(":mission_id",            $ratingInfo["mission_id"]);
	$create->bindValue(":notes",                 $ratingInfo["notes"]);

	$create->execute();

	$changes = [];
	foreach ($ratingInfo as $key => $newValue) {
		if ($oldInfo->ratingInfo[$key] != $newValue) {
			$changes[$key] = [
				"old" => $oldInfo->ratingInfo[$key],
			    "new" => $newValue
			];
		}
	}

	//If they changed anything make a log of it
	if (count($changes) >= 0) {
		$changelog = $db->prepare("INSERT INTO `ex82r_mission_change_log` SET
			`mission_id` = :mission_id,
			`changes` = :changes
		");
		$changelog->bindValue(":mission_id", $ratingInfo["mission_id"]);
		$changelog->bindValue(":changes", json_encode($changes));
		$changelog->execute();
	}
}

updateRatings($_POST["info"]["ratingInfo"]);
