<?php
define("PQ_RUN", true);
require_once("../Framework.php");

$jdb = new Database("joomla", []);

//Get all the user ids
$query = $jdb->prepare("SELECT `id`, LOWER(`username`) FROM `bv2xj_users`");
$query->execute();

$userLookup = [];
while (($row = $query->fetch()) !== false) {
	$user = trim($row[1]);
	$userLookup[$user] = User::get($row[0]);
}
echo("Got users\n");

$db->prepare("DELETE FROM ex82r_user_event_triggers")->execute();

$query = $pdb->prepare("SELECT *, LOWER(`username`) AS `user` FROM `eventtriggers`");
$query->execute();

$inserted = 0;

while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	$username = trim($row["user"]);
	$trigger = $row["triggerID"];
	$time = $row["timestamp"];

	if (!array_key_exists($username, $userLookup)) {
		echo("Invalid user    at {$time} trigger id {$trigger} for {$username}\n");
		continue;
	}

	$user = $userLookup[$username];
	echo("Inserted {$username} {$trigger} at {$time}\n");

	$insert = $db->prepare("INSERT INTO ex82r_user_event_triggers SET `user_id` = :user, `trigger` = :trigger, `timestamp` = :time");
	$insert->bindValue(":user", $user->id);
	$insert->bindValue(":trigger", $trigger);
	$insert->bindValue(":time", $time);
	$insert->execute();
}
