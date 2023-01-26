<?php
define("PQ_RUN", true);
require_once("../Framework.php");

//Get all the missions
$query = $db->prepare("
	SELECT `ex82r_missions`.`id`, `ex82r_missions`.`basename` FROM `ex82r_missions`
	JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
	WHERE game_type = 'Single Player'
");
$query->execute();

$missionLookup = [];
while (($row = $query->fetch()) !== false) {
	$id = $row[0];
	$base = $row[1];

	$ptq = $pdb->prepare("SELECT `qualify` FROM `officiallevels` WHERE `file` = :basen
ame");
	$ptq->bindValue(":basename", $base);
	$ptq->execute();

	if ($ptq->rowCount() === 0) {
		//Maybe it's custom?
		$ptq = $pdb->prepare("SELECT `qualify` FROM `levels` WHERE `file` = :basename");
		$ptq->bindValue(":basename", $base);
		$ptq->execute();
	}
	if ($ptq->rowCount() === 0) {
		continue;
	}

	$mission = Mission::getById($id);
	$newPar = $mission->ratingInfo["par_time"];
	$oldPar = $ptq->fetchColumn(0);

	if ($newPar != $oldPar) {
		if ($newPar == 0) {
			//Update old par
			$upq = $db->prepare("UPDATE `ex82r_mission_rating_info` SET `par_time` = :par WHERE `mission_id` = :id");
			$upq->bindValue(":par", $oldPar);
			$upq->bindValue(":id", $id);
			$upq->execute();
			echo("Update to match internal par: {$mission->name} to $oldPar\n");
		} else {
			echo("Old level internal par mismatch: {$mission->name} $oldPar -> $newPar\n");
		}
	}
}
