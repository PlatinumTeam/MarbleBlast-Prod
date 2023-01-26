<?php
$allow_nonwebchat = true;
$dont_update_ping = true;
$kick_myself      = true;

// Open the database connection
require("opendb.php");

// Only add if we are from torque or submitting
if (isTorque() || isSubmitting()) {
	// Check login (see opendb.php)

	$login = checkPostLogin();
	if ($login == 7) { // 7 is success
		if ($_POST["submitting"] == 2) { // Admin panel
			$username = getUsername();
			$password = getPostValue("password");
			$query = pdo_prepare("SELECT `salt`,`joomla` FROM `users` WHERE `username` = :user");
			$query->bind(":user", $username, PDO::PARAM_STR);
			$result = $query->execute();
			$row = $result->fetch();
			$salt = $row["salt"];
			$joomla = $row["joomla"];

			if ($joomla) {
				require_once("jsupport.php");
				setCookie("username", $username, time() + 60*60*24*7);
				setCookie("password", $password, time() + 60*60*24*7);
			} else {
				setCookie("username", $username, time() + 60*60*24*7);
				setCookie("password", salt($password, $salt), time() + 60*60*24*7);
			}

			if (getPostValue("continue") != "")
				headerDie("Location: " . getPostValue("continue"));
			headerDie("Location: admin/admin.php");
		}
		if (getUserLoggedIn(getUsername())) {
			if (isTorque()) {
				// Kick them off and log them in again!
				$query = pdo_prepare("DELETE FROM `loggedin` WHERE `username` = :user");
				$query->bind(":user", getUsername());
				$query->execute();
				echo("AUTOKICKED\n");
			} else {
				$username = getUsername();
				$query = pdo_prepare("SELECT `loginsess` FROM `loggedin` WHERE `username` = :user");
				$query->bind(":user", $username, PDO::PARAM_STR);
				$result = $query->execute();
				$row = $result->fetchIdx();
				sig(8, false, $row[0]); //Already logged in
			}
		}
		if (isTorque()) {
			// This buffer better damn well flush!

			//HiGuy from the future: It doesn't. :(
			echo("HEY, LISTEN!\n");
			flush();
		}

		// Notify the server of our login
		$location = -2;
		if (array_key_exists("location", $_POST))
			$location = intval($_POST["location"]);

		$sess = str_rand(64);
		if (array_key_exists("sess", $_POST))
			$sess = $_POST["sess"];

		$game = "Platinum";
		if (array_key_exists("game", $_POST))
			$game = $_POST["game"];

		$username = getUsername();
		$display = getDisplayName();
		$time = getServerTime();
		$access = getUserAccess(getUsername());

		$resolutions = getPostValue("res");

		deleteTrackDataType("windowres", $username);
		deleteTrackDataType("screenres", $username);
		trackData("windowres", $username, $resolutions[0]);
		trackData("screenres", $username, $resolutions[1]);
		trackData("logins", $username);

		$platform = getPostValue("platform");
		if ($platform != "")
			trackData("platform", $username, $platform);

		postNotify("login", $username, -1, "$location $game $access");
		trackData("lastlogin", $username, getServerTime());

		$table = (isJoomla() ? "jloggedin" : "loggedin");

		$query = pdo_prepare("INSERT INTO `$table` (`username`, `display`, `access`, `location`, `game`, `time`, `logintime`, `loginsess`) VALUES (:user, :display, :access, :location, :game, :time, :logintime, :session)");
		$query->bind(":user", $username, PDO::PARAM_STR);
		$query->bind(":display", $display, PDO::PARAM_STR);
		$query->bind(":access", $access, PDO::PARAM_INT);
		$query->bind(":location", $location, PDO::PARAM_INT);
		$query->bind(":game", $game, PDO::PARAM_STR);
		$query->bind(":time", $time, PDO::PARAM_INT);
		$query->bind(":logintime", $time, PDO::PARAM_INT);
		$query->bind(":session", $sess, PDO::PARAM_STR);
		$query->execute();

		// Send a success if we're in Torque, otherwise redirect to login
		if (isTorque()) {
			sig(7); //Login Successful
			require("info.php");

		   $query = pdo_prepare("SELECT * FROM `scdata` WHERE `disabled` = 0 ORDER BY `id` ASC");
		   $result = $query->execute();
		   echo("SUPERCHALLENGE LIST LENGTH " . $result->rowCount() . "\n");
		   while (($row = $result->fetch()) !== false) {
		   	// We have a bunch of points info, but the client doesn't need that ;)
		   	$row["display"] = urlencode($row["display"]);
		      echo("SUPERCHALLENGE LIST {$row['id']} {$row['name']} {$row['display']} {$row['platinumPercent']} {$row['ultimatePercent']} {$row['minTimeout']} {$row['maxTimeout']} {$row['bitmap']} {$row['missions']}\n");
		   }

			echo("GET A RUN JOB\n");
		}
	} else { // Die
		if (isTorque())
			sig($login);
	}
}
?>
