<?php
define("PQ_RUN", true);
require_once("../Framework.php");

if (!Login::isPrivilege("pq.mod.editRatings")) {
	header("HTTP/1.1 401 Unauthorized");
	die();
}

$changes = requireParam("changes");

foreach ($changes as $missionId => $changeInfo) {
	if (array_key_exists("newDifficultyId", $changeInfo)) {
		$query = $db->prepare(
			"UPDATE `ex82r_missions`
			SET `difficulty_id` = :newDifficulty,
			    `sort_index` = :newSortIndex
			WHERE `id` = :id"
		);
		$query->bindValue(":newDifficulty", $changeInfo["newDifficultyId"]);
		$query->bindValue(":newSortIndex", $changeInfo["newSortIndex"]);
		$query->bindValue(":id", $missionId);
		$query->execute();
	} else {
		$query = $db->prepare(
			"UPDATE `ex82r_missions`
			SET `sort_index` = :newSortIndex
			WHERE `id` = :id"
		);
		$query->bindValue(":newSortIndex", $changeInfo["newSortIndex"]);
		$query->bindValue(":id", $missionId);
		$query->execute();
	}
}
