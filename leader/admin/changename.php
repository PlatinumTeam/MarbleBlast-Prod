<?php

$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();
if ($access > (MINIMUM_ACCESS - 1)) {

	$from = $_REQUEST["from"];
	$to   = $_REQUEST["to"];
	$run  = (bool)$_REQUEST["run"];

	$access = getUserPrivilege($from);
	if ($access > getUserPrivilege(getUsername())) {
		return;
	}

	echo("Changing user name from {$from} to {$to}<br>");

	//All fields to change
	$dbs = array(
		"platinum" => array(
			"`achievements`"      => array("`username`"),
			"`addresses`"         => array("`username`"),
			"`blocks`"            => array("`username`"),
			"`cachievements`"     => array("`username`"),
			"`challengedata`"     => array("`username`", "`opponent`", "`winner`"),
			"`challenges`"        => array("`player0`", "`player1`", "`winner`"),
			"`chat`"              => array("`username`", "`destination`"),
			"`clevelscores`"      => array("`username`"),
			"`easteregg`"         => array("`username`"),
			"`eventcandy`"        => array("`username`"),
			"`eventtriggers`"     => array("`username`"),
			"`expires`"           => array("`username`"),
			"`friends`"           => array("`username`"),
			"`guitracking`"       => array("`username`"),
			"`imports`"           => array("`username`"),
			"`jloggedin`"         => array("`username`"),
			"`loggedin`"          => array("`username`"),
			"`motd`"              => array("`submitter`"),
			"`mpachievements`"    => array("`username`"),
			"`notify`"            => array("`username`"),
			"`packselects`"       => array("`username`"),
			"`pqapril`"           => array("`username`"),
			"`qotd`"              => array("`username`", "`submitter`"),
			"`ratings`"           => array("`username`"),
			"`report`"            => array("`username`", "`person`"),
			"`savedmessages`"     => array("`sender`", "`recipient`"),
			"`sclevelscores`"     => array("`username`"),
			"`scores`"            => array("`username`"),
			"`scores2`"           => array("`username`"),
			"`scpractice`"        => array("`username`"),
			"`scscores`"          => array("`username`"),
			"`serverplayers`"     => array("`username`"),
			"`serverscores`"      => array("`username`"),
			"`serverteams`"       => array("`username`"),
			"`snowballs`"         => array("`username`"),
			"`snowglobes`"        => array("`username`"),
			"`superchallenges`"   => array("`player0`", "`player1`", "`player2`", "`player3`"),
			"`topscores`"         => array("`username`"),
			"`tracking`"          => array("`username`"),
			"`ultraachievements`" => array("`username`"),
			"`usedkeys`"          => array("`username`"),
			"`userchallengedata`" => array("`username`"),
			"`usermarbles`"       => array("`username`"),
			"`users`"             => array("`username`"),
		),
		"joomla"   => array(
			"`bv2xj_users`" => array("`username`")
		),
		"fubar"    => array(
			"`xhj3d_fubar_users`" => array("`username`")
		)
	);

	foreach ($dbs as $db => $tables) {
		try {
			//Open the database connection and try to change stuff
			$dsn        = "mysql:dbname=" . MBDB::getDatabaseName($db) . ";host=" . MBDB::getDatabaseHost($db);
			$connection = new PDO($dsn, MBDB::getDatabaseUser($db), MBDB::getDatabasePass($db));

			echo("Connected to database {$db}<br>");

			foreach ($tables as $table => $fields) {
				foreach ($fields as $field) {
					//Check how many fields we will change
					$query = $connection->prepare("SELECT COUNT(*) FROM $table WHERE $field = :old");
					$query->bindParam(":old", $from);
					$query->execute();
					$count = $query->fetchColumn(0);

					if ($count) {
						if ($run) {
							echo("Updating {$count} rows on {$table}.{$field}<br>");
							//Change them
							$query = $connection->prepare("UPDATE $table SET $field = :new WHERE $field = :old");
							$query->bindParam(":new", $to);
							$query->bindParam(":old", $from);
							$query->execute();
						} else {
							echo("Would update {$count} rows on {$table}.{$field}<br>");
						}
					}
				}
			}
		} catch (Exception $e) {
			echo("Error connecting to DB: $db<br>");
		}
	}
}
