<?php

$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$mission = $_GET["mission"];
$cutoff  = $_GET["cutoff"];

$run = $_GET["run"];

// Connection to old database
$olddbhost = MBDB::getDatabaseHost("platinum_old");
$olddbuser = MBDB::getDatabaseUser("platinum_old");
$olddbpass = MBDB::getDatabasePass("platinum_old");
$olddbdata = MBDB::getDatabaseName("platinum_old");

$dsn = "mysql:dbname=" . $olddbdata . ";host=" . $olddbhost;
// Connect + select
try {
	global $oldConnection;
	$oldConnection = new SpDatabaseConnection($dsn, $olddbuser, $olddbpass);
} catch (SpDatabaseLoginException $e) {
	die("Could not open database connection.");
}
if ($oldConnection == null) {
	die("Could not connect to database.");
}

function old_prepare($query) {
	global $oldConnection;
	return $oldConnection->prepare($query);
}

if (getAccess() > 0) {
	$times = array(

		//put stuff in here

		"platformtraining" => 5680,
		"marbleplayground" => 28950,
		"motomarblecross" => 2060,
		"spaceslide" => 5500,
		"freewaycrossing" => 4860,
		"kingofthemountain" => 5600,
		"obstaclecourse" => 3980,
		"pathways" => 19850,
		"towermaze" => 5450,
		"moneytree" => 36900,
		"diamondroundup" => 11850,
		"groundzero" => 1600,
		"learnthewallhit" => 11270,
		"teleporttraining" => 12700,
		"daedalhelix" => 48970,
		"thetimemodifierrace" => 40495,
		"tunnelvision" => 14360,
		"kingofthemountainultra" => 4690,
		"schadenfreudeultra" => 19460,
		"02unpredictableride" => 9015,
		"catwalkcapers" => 7354,
		"solomaze" => 91160,
		"tubetower" => 4900,
		"vortex" => 12900,
		"egueefirstchallenge" => 43220,
		"oversimplified" => 20290,
		"vertigo" => 7095,
		"autonomous" => 9739,
		"shiftingcorridors" => 15150,
		"simonsays" => 17610,
		"03marblesmithworkshop" => 4500,
		"readysetroll" => 10900,
		"mariocircuit1" => 12930
	);

//	foreach ($times as $mission => $cutoff) {
//		deleteCutoff($mission, $cutoff);
//	}

	deleteCutoff($mission, $cutoff);
}

function deleteCutoff($mission, $cutoff) {
	$query = pdo_prepare("SELECT * FROM `scores` WHERE `score` < :cutoff AND `level` = :mission ORDER BY `score` ASC");
	$query->bind(":cutoff", $cutoff);
	$query->bind(":mission", $mission);
	$result = $query->execute();

	echo("<hr><br>Deleting {$result->rowCount()} rows for {$mission}<br>");

	$rows = $result->fetchAll();

	foreach ($rows as $index => $row) {
		deleteTime($row["id"]);
	}

	$query = pdo_prepare("SELECT * FROM `scores2` WHERE `score` < :cutoff AND `level` = :mission ORDER BY `time` ASC");
	$query->bind(":cutoff", $cutoff);
	$query->bind(":mission", $mission);
	$result = $query->execute();

	$rows = $result->fetchAll();
	echo("<br>Deleting {$result->rowCount()} old rows for {$mission}<br>");

	foreach ($rows as $index => $row) {
		deleteScores2Time($row["id"]);
	}

	$query = old_prepare("SELECT * FROM `times` WHERE `time` < :cutoff AND `mission` = :mission ORDER BY `time` ASC");
	$query->bind(":cutoff", $cutoff);
	$query->bind(":mission", $mission);
	$result = $query->execute();

	$rows = $result->fetchAll();
	echo("<br>Deleting {$result->rowCount()} old rows for {$mission}<br>");

	foreach ($rows as $index => $row) {
		deleteOldTime($row["id"]);
	}
}


function deleteScores2Time($id) {
	//Update their rating accordingly
	$query = pdo_prepare("SELECT `username`, `score` FROM `scores2` WHERE `id` = :id");
	$query->bind(":id", $id);
	$result = $query->execute();

	if ($result->rowCount()) {
		list($user, $score) = $result->fetchIdx();

		echo("$user just got old time deleted: {$score}<br>");

		global $run;
		if ($run) {
			$query = pdo_prepare("DELETE FROM `scores2` WHERE `id` = :id");
			$query->bind(":id", $id);
			$query->execute();
			echo("Deleted score $id<br>");
		}
	}
}

function deleteOldTime($id) {
	//Update their rating accordingly
	$query = old_prepare("SELECT `username`, `time` FROM `times` WHERE `id` = :id");
	$query->bind(":id", $id);
	$result = $query->execute();

	if ($result->rowCount()) {
		list($user, $score) = $result->fetchIdx();

		echo("$user just got old time deleted: {$score}<br>");

		global $run;
		if ($run) {
			$query = old_prepare("DELETE FROM `times` WHERE `id` = :id");
			$query->bind(":id", $id);
			$query->execute();
			echo("Deleted score $id<br>");
		}
	}
}

function deleteTime($id) {
	//Update their rating accordingly
	$query = pdo_prepare("SELECT `username`, `rating`, `level`, `score`, `gametype` FROM `scores` WHERE `id` = :id");
	$query->bind(":id", $id);
	$result = $query->execute();

	if ($result->rowCount()) {
		list($user, $rating, $level, $score, $gameType) = $result->fetchIdx();

		$query = pdo_prepare("SELECT `rating` FROM `scores` WHERE `level` = :level AND `score` >= :score AND `gametype` = :gametype AND `username` = :username AND `id` != :id LIMIT 1");
		$query->bind(":level", $level);
		$query->bind(":score", $score);
		$query->bind(":gametype", $gameType);
		$query->bind(":username", $user);
		$query->bind(":id", $id);
		$result = $query->execute();

		$nextbest = 0;
		if ($result->rowCount()) {
			$nextbest = $result->fetchIdx(0);
		}

		echo("$user just got a rating loss of " . ($rating - $nextbest) . " time: {$score}<br>");

		$ratingField = "rating_";

		if ($gameType == "Gold")
			$ratingField .= "mbg";
		else if ($gameType == "Platinum")
			$ratingField .= "mbp";
		else if ($gameType == "Ultra")
			$ratingField .= "mbu";
		else if ($gameType == "LBCustom")
			$ratingField .= "custom";

		global $run;
		if ($run) {
			$change = $rating - $nextbest;
			$query  =
				pdo_prepare("UPDATE `users` SET `rating` = `rating` - :change, `$ratingField` = `$ratingField` - :change WHERE `username` = :username");
			$query->bind(":change", $change);
			$query->bind(":username", $user);
			$query->execute();

			$query = pdo_prepare("DELETE FROM `scores` WHERE `id` = :id");
			$query->bind(":id", $id);
			$query->execute();
			echo("Deleted score $id<br>");
		}
	}
}
