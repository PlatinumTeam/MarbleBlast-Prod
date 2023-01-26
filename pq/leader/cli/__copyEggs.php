<?php
define("PQ_RUN", true);
require_once("../Framework.php");

$jdb = new Database("joomla");

function unphiltime($time) {
	return $time + 1344917000;
}

//Literally RecordEgg.php
function giveEgg(User $user, Mission $mission, $time) {
	global $db;

	//Check if they already have an egg in the database
	$query = $db->prepare(
		"SELECT `best_time` FROM ex82r_user_eggs
		WHERE `user_id` = :user_id
	 	AND `mission_id` = :mission_id");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":mission_id", $mission->id);
	$query->execute();

	if ($query->rowCount() == 0) {
		//No, we don't have one. Insert it new
		$query = $db->prepare(
			"INSERT INTO ex82r_user_eggs
			SET `user_id` = :user_id,
		        `mission_id` = :mission_id,
		        `best_time` = :time");
		$query->bindValue(":user_id", $user->id);
		$query->bindValue(":mission_id", $mission->id);
		$query->bindValue(":time", $time);

		$query->execute();
	} else {
		//Find if they've beaten their best time
		$best = $query->fetchColumn(0);

		if ($time < $best) {
			//Yes, update their time to reflect that
			$query = $db->prepare(
				"UPDATE ex82r_user_eggs
				SET `best_time` = :time
			WHERE `user_id` = :user_id
				AND `mission_id` = :mission_id");
			$query->bindValue(":time", $time);
			$query->bindValue(":user_id", $user->id);
			$query->bindValue(":mission_id", $mission->id);

			$query->execute();
		}
	}
}

//Get all the user ids
$query = $jdb->prepare("SELECT `id`, LOWER(`username`) FROM `bv2xj_users`");
$query->execute();

$userLookup = [];
while (($row = $query->fetch()) !== false) {
	$user = trim($row[1]);
	$userLookup[$user] = User::get($row[0]);
}

//Get all the missions
$query = $db->prepare("
	SELECT `ex82r_missions`.`id`, `basename` FROM `ex82r_missions`
	JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
	WHERE game_type = 'Single Player'
");
$query->execute();

$missionLookup = [];
while (($row = $query->fetch()) !== false) {
	$id = $row[0];
	$basename = $row[1];
	$mission = Mission::getById($id);
	$missionLookup[stripLevel($basename)] = $mission;
}

//Clear eggs
$query = $db->prepare("
	DELETE FROM ex82r_user_eggs
	WHERE best_time = 5999999
");

$query = $pdb->prepare("SELECT *, LOWER(`username`) AS `user` FROM easteregg");
$query->execute();

while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	$username = trim($row["user"]);
	$basename = stripLevel($row["level"]);
	$time = date("c", unphiltime($row["time"]));

	if (!array_key_exists($username, $userLookup)) {
		echo("Invalid user    at {$time} on {$basename} egg for {$username}\n");
		continue;
	}
	if (!array_key_exists($basename, $missionLookup)) {
		echo("Invalid mission at {$time} on {$basename} egg for {$username} \n");
		continue;
	}

	$user = $userLookup[$username];
	$mission = $missionLookup[$basename];

	//Give them the egg
	giveEgg($user, $mission, 5999999);
}
