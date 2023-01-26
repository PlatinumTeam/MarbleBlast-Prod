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

$mapping = [
	38 => 3000,
	39 => 3001,
	40 => 3002,
	41 => 3003,
	42 => 3004,
	43 => 3005,
	62 => 3006,
	63 => 3007,
	64 => 3008,
	65 => 3009,
	66 => 3010,
	67 => 3011,
	68 => 3012,
	69 => 3013,
	70 => 3014,
	71 => 3015,
	72 => 3016,
	73 => 3017,
	74 => 3018,
	75 => 3019,
	76 => 3020,
	77 => 3021,
	78 => 3022,
	79 => 3023,
	80 => 3024,
	81 => 3025,
	82 => 3026
];

$db->prepare("DELETE FROM ex82r_user_achievements WHERE achievement_id >= 3000 AND achievement_id < 4000")->execute();
$query = $pdb->prepare("SELECT *, LOWER(`username`) AS `user` FROM mpachievements");
$query->execute();

$db->beginTransaction();

while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	$ach = $row["achievement"];
	$username = $row["user"];
	if (!array_key_exists($ach, $mapping)) {
		continue;
	}
	if (!array_key_exists($username, $userLookup)) {
		continue;
	}
	$user = $userLookup[$username];
	echo("Import ach {$ach} => {$mapping[$ach]} for {$row["username"]}\n");

	$insert = $db->prepare("
		INSERT INTO ex82r_user_achievements SET achievement_id = :ach_id, user_id = :user_id, timestamp = :time
	");
	$insert->bindValue(":ach_id", $mapping[$ach]);
	$insert->bindValue(":user_id", $user->id);
	$insert->bindValue(":time", $row["timestamp"]);
	$insert->execute();
}

$db->commit();
