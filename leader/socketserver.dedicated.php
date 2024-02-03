<?php
defined("SOCKET_SERVER") or die("Invalid Access");

$ded_connection = null;

function dedicatedGetInfo($id) {
	//First off, connect to the db
	try {
		dConnect();
	} catch (Exception $e) {
		return $e->getMessage();
	}

	if (!dExists($id)) {
		dDisconnect();
		return "Server $id does not exist!";
	}

	$query = dprepare("SELECT * FROM `servers` WHERE `id` = :id");
	$query->bind(":id", $id);

	$result = $query->execute();
	$array = $result->fetch();

	$platform = $array["os"] == "x86UNIX" ? "Linux (Mac Compatible)" : "Windows";

	$info = "Dedicated server $id info:\n" .
			  "Server Name: {$array["name"]}\n" .
			  "Server Running: " . (dIsRunning($id) ? "Yes" : "No") . "\n" . 
			  "Server Port: {$array["port"]}\n" .
			  "Server Platform: $platform\n" .
			  "Max Players: {$array["maxplayers"]}\n" .
			  "Calculates Rating: " . ($array["calculate"] ? "Yes" : "No") . "\n";

	return $info;
}

function dedicatedStartServer($id) {
	//First off, connect to the db
	try {
		dConnect();
	} catch (Exception $e) {
		return $e->getMessage();
	}

	if (!dExists($id)) {
		dDisconnect();
		return "Server $id does not exist!";
	}

	if (dIsRunning($id)) {
		dDisconnect();
		return "Server is already running!";
	}

	$query = dprepare("SELECT * FROM `servers` WHERE `id` = :id");
	$query->bind(":id", $id);

	$result = $query->execute();
	$array = $result->fetch();

	//Start the server!
	$location = $array["gamelocation"];
	$script = $array["startscript"];
	$result = exec(escapeshellcmd("{$location}{$script}"));

	//$Result should be the PID
	if (!posix_kill($result, 0)) {
		//No it's not
		dDisconnect();
		return "Could not find dedicated server process.";
	}

	//Yes it is

	//Add it to the database
	$query = dprepare("UPDATE `servers` SET `pid` = :pid WHERE `id` = :id");
	$query->bind(":pid", $result);
	$query->bind(":id", $id);
	$query->execute();

	//Update the settings to the database
	$conts = "";
	$inputfile = $array["inputfile"];

	$vresult = dprepare("SELECT * FROM `variables`")->execute();
	while (($varray = $vresult->fetch()) !== false) {
		$var = $varray["name"];
		$gamevar = $varray["gamevar"];
		$after = $varray["extra"];

		$query = dprepare("SELECT `$var` FROM `servers` WHERE `id` = :id");
		$query->bind(":id", $id);
		$value = $query->execute()->fetchIdx(0);

		$value = str_replace(array("\n", "\r\n"), "\\n", addslashes($value));

		$command = "$gamevar = \"$value\"; $after ";
		$conts .= $command;
	}

	$conts .= $array["startcmd"];

	file_put_contents("{$location}{$inputfile}", $conts);

	//And don't forget to disconnect
	dDisconnect();
	return true;
}

function dedicatedStopServer($id) {
	//First off, connect to the db
	try {
		dConnect();
	} catch (Exception $e) {
		return $e->getMessage();
	}

	if (!dExists($id)) {
		dDisconnect();
		return "Server $id does not exist!";
	}

	if (!dIsRunning($id)) {
		dDisconnect();
		return "Server is not running!";
	}

	$query = dprepare("SELECT * FROM `servers` WHERE `id` = :id");
	$query->bind(":id", $id);

	$result = $query->execute();
	$array = $result->fetch();

	//Stop the server!
	$location = $array["gamelocation"];
	$inputfile = $array["inputfile"];

	//Try to stop it nicely first
	file_put_contents("{$location}{$inputfile}", "quit();");

	//Wait and check if it has closed
	$pid = $array["pid"];
	$loop = 0;
	do {
		usleep(300000);
		if ($loop > 5) {
			//Give up waiting
			posix_kill($pid, 9); //SIGKILL
		}
		$loop ++;
	} while (posix_kill($pid, 0));

	//And don't forget to disconnect
	dDisconnect();
	return true;
}

function dedicatedSetVar($id, $var, $value) {
	//First off, connect to the db
	try {
		dConnect();
	} catch (Exception $e) {
		return $e->getMessage();
	}

	if (!dExists($id)) {
		dDisconnect();
		return "Server $id does not exist!";
	}

	$query = dprepare("SELECT * FROM `servers` WHERE `id` = :id");
	$query->bind(":id", $id);

	$result = $query->execute();
	$array = $result->fetch();

	//Tell the server something
	$location = $array["gamelocation"];
	$inputfile = $array["inputfile"];

	//Nice try
	preg_replace('/[^a-z0-9]/s', '', $var);

	//Get the variable from the database
	$query = dprepare("SELECT * FROM `variables` WHERE `name` = :name");
	$query->bind(":name", $var);
	$result = $query->execute();

	//Make sure the variable exists
	if (!$result->rowCount()) {
		dDisconnect();
		return "Invalid variable name $var";
	}

	//Ok update the variable
	$query = dprepare("UPDATE `servers` SET `$var` = :value WHERE `id` = :id");
	$query->bind(":value", $value);
	$query->bind(":id", $id);
	$query->execute();

	$array = $result->fetch();
	$gamevar = $array["gamevar"];
	$after = $array["extra"];

	$value = str_replace(array("\n", "\r\n"), "\\n", addslashes($value));
	$command = "$gamevar = \"$value\"; $after ";
	$result = file_put_contents("{$location}{$inputfile}", $command);

	//And don't forget to disconnect
	dDisconnect();
	return true;
}

function dedicatedEvaluate($cmd) {
	//TODO
}

function dedicatedKickPlayer($player) {
	//TODO
}

function dExists($id) {
	$query = dprepare("SELECT COUNT(*) FROM `servers` WHERE `id` = :id");
	$query->bind(":id", $id);
	return $query->execute()->fetchIdx(0) > 0;
}

function dIsRunning($id) {
	$query = dprepare("SELECT `pid` FROM `servers` WHERE `id` = :id");
	$query->bind(":id", $id);
	if (($pid = $query->execute()->fetchIdx(0)) < 0)
		return false;

	//Now make sure it's alive
	return posix_kill($pid, 0);
}

function dConnect() {
	$dedhost = MBDB::getDatabaseHost("dedicated");
	$deduser = MBDB::getDatabaseUser("dedicated");
	$dedpass = MBDB::getDatabasePass("dedicated");
	$deddata = MBDB::getDatabaseName("dedicated");

	$dsn = "mysql:dbname=" . $deddata . ";host=" . $dedhost;
	//HiGuy: Connect + select
	try {
		global $ded_connection;
	   $ded_connection = new SpDatabaseConnection($dsn, $deduser, $dedpass);
	} catch (SpDatabaseLoginException $e) {
		throw $e;
		
	}
	if ($ded_connection == null) {
		throw new Exception("Could not connect to the database!", 1);
	}
}

function dDisconnect() {
	global $ded_connection;

	$ded_connection = null;
}

function dprepare($query) {
	global $ded_connection;

	return $ded_connection->prepare($query);
}

?>