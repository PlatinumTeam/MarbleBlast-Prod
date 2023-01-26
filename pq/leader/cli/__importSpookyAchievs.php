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
	44 => 2000,
	45 => 2001,
	46 => 2002,
	47 => 2003,
	48 => 2004,
	49 => 2005,
	50 => 2006,
	51 => 2007,
	52 => 2008,
	53 => 2009,
	54 => 2010,
	55 => 2011,
	56 => 2012,
	57 => 2013,
	58 => 2014,
	59 => 2015,
	60 => 2016,
	61 => 2021
];

$db->prepare("DELETE FROM ex82r_user_achievements WHERE achievement_id >= 2000 AND achievement_id < 3000")->execute();
$query = $pdb->prepare("SELECT *, LOWER(`username`) AS `user` FROM mpachievements");
$query->execute();

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
