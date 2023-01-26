<?php
define("PQ_RUN", true);
require_once("../Framework.php");

$jdb = new Database("joomla");
$pdb = new Database("platinum");

$query = $pdb->prepare("SELECT * FROM scores WHERE origin = 0 ORDER BY `time` ASC");
$query->execute();
$phil_rows = $query->fetchAll(PDO::FETCH_ASSOC);

// convert mission names
$missionmap = [];
$query = $db->prepare("SELECT * FROM prod_pq.ex82r_missions WHERE game_id = 1 or game_id = 2 or game_id = 5");
$query->execute();
while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	$missionmap[stripLevel($row["basename"])] = $row["id"];
}

// convert usernames
$usermap = [];
$query = $jdb->prepare("SELECT * FROM bv2xj_users");
$query->execute();
while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	$usermap[strtolower($row["username"])] = $row["id"];
}

$origins = ["PhilsEmpire", "MarbleBlast.com"];

foreach ($phil_rows as $row) {
	$username = $row["username"];
	$score = $row["score"];
	$level = $row["level"];
	$origin = $origins[$row["origin"]];

	if (!array_key_exists($level, $missionmap)) {
		echo("nomis $username $score $level\n");
		break;
	}
	$id = $missionmap[$level];

	if (!array_key_exists($username, $usermap)) {
		echo("nouser $username $score $level\n");
		break;
	}
	$uid = $usermap[$username];

	// Find equivalent time in new db
	$query = $db->prepare("
		SELECT id FROM prod_pq.ex82r_user_scores
		WHERE user_id = :uid AND mission_id = :id
		AND score = :score AND origin = 'MarbleBlastPlatinum'
		ORDER BY timestamp ASC
	    LIMIT 1
	");
	$query->bindValue(":uid", $uid);
	$query->bindValue(":id", $id);
	$query->bindValue(":score", $score);
	$query->execute();

	$id = $query->fetchColumn(0);
	// Now update
	$query = $db->prepare("
		UPDATE prod_pq.ex82r_user_scores
		SET origin = 'PhilsEmpire'
		WHERE id = :id
	");
	$query->bindValue(":id", $id);
	$query->execute();
}
