<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 1) {
	$dedhost = MBDB::getDatabaseHost("dedicated");
	$deduser = MBDB::getDatabaseUser("dedicated");
	$dedpass = MBDB::getDatabasePass("dedicated");
	$deddata = MBDB::getDatabaseName("dedicated");

	$dsn = "mysql:dbname=" . $deddata . ";host=" . $dedhost;
	// Connect + select
	try {
		global $ded_connection;
	   $ded_connection = new SpDatabaseConnection($dsn, $deduser, $dedpass);
	} catch (SpDatabaseLoginException $e) {
		die("Could not open database connection.");
	}
	if ($ded_connection == null) {
		die("Could not connect to database.");
	}

	function dprepare($query) {
		global $ded_connection;

		return $ded_connection->prepare($query);
	}

	//Do something to the server

	$action = $_POST["action"];
	$server = $_POST["server"];

	$row = dprepare("SELECT * FROM `servers` WHERE `id` = :id");
	$row->bind(":id", $server);

	$result = $row->execute();
	if ($result->rowCount()) {
		//Server info time
		$array = $result->fetch();
		$location = $array["gamelocation"];

		if ($action == "start") {
			//Start the server!
			$script = $array["startscript"];
			$result = exec(escapeshellcmd("{$location}{$script}"));

			//$Result should be the PID
			if (posix_kill($result, 0)) {
				//Yes it is

				//Add it to the database
				$query = dprepare("UPDATE `servers` SET `pid` = :pid WHERE `id` = :id");
				$query->bind(":pid", $result);
				$query->bind(":id", $server);
				$query->execute();

				//These things, they take time
				sleep(3);

				//Update the settings to the database
				$conts = "";
				$inputfile = $array["inputfile"];

				$vresult = dprepare("SELECT * FROM `variables`")->execute();
				while (($varray = $vresult->fetch()) !== false) {
					$var = $varray["name"];
					$gamevar = $varray["gamevar"];
					$after = $varray["extra"];

					$query = dprepare("SELECT `$var` FROM `servers` WHERE `id` = :id");
					$query->bind(":id", $server);
					$value = $query->execute()->fetchIdx(0);

					$value = str_replace(array("\n", "\r\n"), "\\n", addslashes($value));

					$command = "$gamevar = \"$value\"; $after ";
					$conts .= $command;
				}

				$conts .= $array["startcmd"];

				file_put_contents("{$location}{$inputfile}", $conts);

				//Tell them
				echo(json_encode(array("Result" => $result, "Error" => false, "Command" => escapeshellcmd("{$location}{$script}"))));
			} else {
				//Probably an error
				echo(json_encode(array("Result" => "unknown", "Error" => true, "Command" => escapeshellcmd("{$location}{$script}"))));
			}
		} else if ($action == "stop") {
			//Stop the server!
			$inputfile = $array["inputfile"];

			//Try to stop it nicely first
			file_put_contents("{$location}{$inputfile}", "quit();");

			//Wait and check if it has closed
			$pid = $array["pid"];
			$loop = 0;
			do {
				sleep(1);
				if ($loop > 5) {
					//Give up waiting
					posix_kill($pid, 9); //SIGKILL
				}
				$loop ++;
			} while (posix_kill($pid, 0));

			echo(json_encode(array("Result" => true, "Error" => false)));
		} else if ($action == "send") {
			//Tell the server something
			$inputfile = $array["inputfile"];
			$entry = $_POST["value"];

			$result = file_put_contents("{$location}{$inputfile}", $entry);

			//Give it time to actually exec the damn command!
			sleep(1);

			echo(json_encode(array("Result" => $result, "Error" => !$result)));
		} else if ($action == "set") {
			//Tell the server something
			$inputfile = $array["inputfile"];
			$var = $_POST["variable"];
			$value = $_POST["value"];

			//Nice try
			preg_replace('/[^a-z0-9]/s', '', $var);

			//Get the variable from the database
			$query = dprepare("SELECT * FROM `variables` WHERE `name` = :name");
			$query->bind(":name", $var);
			$result = $query->execute();

			//Make sure the variable exists
			if ($result->rowCount()) {
				//Ok update the variable
				$query = dprepare("UPDATE `servers` SET `$var` = :value WHERE `id` = :id");
				$query->bind(":value", $value);
				$query->bind(":id", $server);
				$query->execute();

				$array = $result->fetch();
				$gamevar = $array["gamevar"];
				$after = $array["extra"];

				$value = str_replace(array("\n", "\r\n"), "\\n", addslashes($value));

				$command = "$gamevar = \"$value\"; $after ";

				$result = file_put_contents("{$location}{$inputfile}", $command);

				//Give it time to actually exec the damn command!
				sleep(1);

				echo(json_encode(array("Result" => $result, "Error" => !$result)));
			} else {
				//Yeah right
				echo(json_encode(array("Error" => true)));
			}
		}
	} else
		echo(json_encode(array("Error" => "The server does not exist!")));
}

?>
