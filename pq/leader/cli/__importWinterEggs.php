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

//Get all the missions
$query = $db->prepare("
	SELECT `ex82r_missions`.`id`, `basename` FROM `ex82r_missions`
	  JOIN ex82r_mission_games ON ex82r_missions.game_id = ex82r_mission_games.id
	WHERE game_id = 19
");
$query->execute();

$missionLookup = [];
while (($row = $query->fetch()) !== false) {
    $id = $row[0];
    $basename = $row[1];
    $mission = Mission::getById($id);
    $missionLookup[strtolower($basename)] = $mission;
}

echo("Got all missions\n");

$query = $pdb->prepare("SELECT *, LOWER(`username`) AS `user` FROM snowglobes");
$query->execute();

$db->beginTransaction();

while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	$missionName = strtolower($row["mission"]);
	$username = $row["user"];
	if (!array_key_exists($missionName, $missionLookup)) {
	    echo("Can't find mission {$missionName}\n");
		continue;
	}
	if (!array_key_exists($username, $userLookup)) {
        echo("Can't find user {$username}\n");
		continue;
	}
	$user = $userLookup[$username];
	$mission = $missionLookup[$missionName];
	$time = $row["timestamp"];

	echo("Import snowglobe on {$mission->name} for {$row["username"]}\n");

	//See if they have it already
    $has = $db->prepare("SELECT COUNT(*) FROM ex82r_user_eggs WHERE user_id = :user_id AND mission_id = :mission_id");
    $has->bindValue(":user_id", $user->id);
    $has->bindValue(":mission_id", $mission->id);
    $has->execute();
    if ($has->fetchColumn(0) > 0) {
        echo("User {$username} already has globe on {$mission->name}\n");
        continue;
    }

    $insert = $db->prepare("
        INSERT INTO ex82r_user_eggs SET 
            user_id = :user_id,
            mission_id = :mission_id,
            best_time = 5999999,
            timestamp = :time
    ");
    $insert->bindValue(":user_id", $user->id);
    $insert->bindValue(":mission_id", $mission->id);
    $insert->bindValue(":time", $time);
    $insert->execute();
}

$db->commit();
