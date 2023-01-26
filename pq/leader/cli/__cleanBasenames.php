<?php
define("PQ_RUN", true);
require_once("../Framework.php");

//Get all the missions
$query = $db->prepare("SELECT `id`, `basename`,`name` FROM `ex82r_missions`");
$query->execute();

$missionLookup = [];
while (($row = $query->fetch()) !== false) {
	$id = $row[0];
	$oldbase = $row[1];
	$newbase = str_replace("\\'", "'", $oldbase);
	$newbase = mb_convert_encoding($newbase, "ASCII");

	if ($oldbase !== $newbase) {
		echo("From $oldbase to $newbase\n");

		$update = $db->prepare("UPDATE `ex82r_missions` SET `basename` = :newbase WHERE `basename` = :oldbase");
		$update->bindValue(":newbase", $newbase);
		$update->bindValue(":oldbase", $oldbase);
		//	$update->execute();
	}

	$oldname = $row[2];
	$newname = str_replace("\\'", "'", $oldname);
	$newname = mb_convert_encoding($newname, "ASCII");

	if ($oldname !== $newname) {
		echo("From $oldname to $newname\n");

		$update = $db->prepare("UPDATE `ex82r_missions` SET `name` = :newname WHERE `name` = :oldname");
		$update->bindValue(":newname", $newname);
		$update->bindValue(":oldname", $oldname);
		//	$update->execute();
	}
}
