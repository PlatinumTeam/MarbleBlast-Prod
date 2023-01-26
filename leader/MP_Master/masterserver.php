<?php
//-----------------------------------------------------------------------------
// MasterServer.php
//
// Copyright (c) 2014 HiGuy Smith
// Portions Copyright (c) GarageGames.com, Inc.
//
// References:
// serverQuery.cc
// c3masterserver.pl
// http://perldoc.perl.org/functions/pack.html
// http://www.binarytides.com/udp-socket-programming-in-php/
//-----------------------------------------------------------------------------

//Port to listen on
$port    = 29000;
//Maximum length of a data buffer
$maxlen  = 1024;
//Time before servers are removed from the list
$timeout = 180;
//Time before servers are sent a second info request
$refresh = 30;

//Ignore all of opendb's checking
$master_server = true;

chdir(dirname($_SERVER["PHP_SELF"]));
file_put_contents("/var/run/marbleblast/masterserver.pid", posix_getpid());

//Connect to the database
require("config.php");

//Create a TCP/IP socket
if (!($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
	socketError();
}

echo("Created socket\n");

if (!socket_bind($socket, "0.0.0.0", $port)) {
	socketError();
}

echo("Socket bound\n");

$serverlist = array();

while (true) {
	echo("Waiting for a connection\n");

	$buffer = "";
	$address = "";
	$port = "";
	$r = socket_recvfrom($socket, $buffer, $maxlen, 0, $address, $port);

	echo("Got " . mb_strlen($buffer) . " bytes from $address:$port\n");

	for ($i = 0; $i < mb_strlen($buffer); $i ++) {
		printf("%02X", ord($buffer[$i]));
	}
	printf("\n");

	$cmd = ord($buffer[0]);

	echo("Command: $cmd\n");

/*
   enum PacketTypes
   {
      MasterServerGameTypesRequest  = 2,
      MasterServerGameTypesResponse = 4,
      MasterServerListRequest       = 6,
      MasterServerListResponse      = 8,
      GameMasterInfoRequest         = 10,
      GameMasterInfoResponse        = 12,
      GamePingRequest               = 14,
      GamePingResponse              = 16,
      GameInfoRequest               = 18,
      GameInfoResponse              = 20,
      GameHeartbeat                 = 22,

      ConnectChallengeRequest       = 26,
      ConnectChallengeReject        = 28,
      ConnectChallengeResponse      = 30,
      ConnectRequest                = 32,
      ConnectReject                 = 34,
      ConnectAccept                 = 36,
      Disconnect                    = 38,
   };
*/
   try {
		switch ($cmd) {
		case 6: //MasterServerListRequest
			print_r($serverlist);

			//Figure out the server list and send it back
			readChar ($buffer, $cmd_type);
			readChar ($buffer, $query_flags);
			readInt  ($buffer, $query_key);
			readChar ($buffer, $dummy);
			readStr  ($buffer, $game_type);
			readStr  ($buffer, $mission_type);
			readChar ($buffer, $min_players);
			readChar ($buffer, $max_players);
			readInt  ($buffer, $region_mask);
			readInt  ($buffer, $version);
			readChar ($buffer, $filter_flag);
			readChar ($buffer, $max_bots);
			readShort($buffer, $min_cpu);
			readChar ($buffer, $buddy_count);

			//At the moment, we just get their request and shit it out to the console.
			//We completely ignore it, because I haven't written the bit that sorts the list yet.
			echo("Got request:\n");
			echo("   Query Flags: $query_flags\n");
			echo("   Key: $query_key\n");
			echo("   Dummy: $dummy\n");
			echo("   Game Type: $game_type\n");
			echo("   Mission Type: $mission_type\n");
			echo("   Min Players: $min_players\n");
			echo("   Max Players: $max_players\n");
			echo("   Region Mask: $region_mask\n");
			echo("   Version: $version\n");
			echo("   Filter Flag: $filter_flag\n");
			echo("   Max Bots: $max_bots\n");
			echo("   Min CPU: $min_cpu\n");
			echo("   Buddy Count: $buddy_count\n");

			$packettype = 8; //MasterServerListResponse

			$query = safe_prepare("SELECT * FROM `mpservers` ORDER BY (`filterFlag` & 1) DESC, `name` ASC");
			$result = $query->execute();
			$servercount = $result->rowCount();

			if ($servercount) {
				//Check for old servers
				$packetindex = 0;
				$array = $result->fetchAll();

				//Prune first, then update
				foreach ($array as $row) {
					$key = $row["key"];

					//Check for timeout
					if (gettimeofday(true) > date("U", strtotime($row["timestamp"])) + $timeout) {

						//Can the server, it's gone
						$query = safe_prepare("DELETE FROM `mpservers` WHERE `key` = :key");
						$query->bind(":key", $key);
						$query->execute();

						echo("Removed {$row["address"]}:{$row["port"]} for inactivity\n");
						$servercount --;

						//And go to the next one
						continue;
					}
				}

				//Create the response
				$outbuffer = "";
				writeChar ($outbuffer, $packettype);
				writeChar ($outbuffer, 0);
				writeInt  ($outbuffer, $query_key);
				writeChar ($outbuffer, $packetindex);
				writeChar ($outbuffer, 1); //packet count = ceil($servercount / 65536), this is basically always one
				writeShort($outbuffer, $servercount);
				foreach ($array as $row) {
					$key = $row["key"];
					$serveraddress = $row["address"];
					$serverport = $row["port"];

					//Check for timeout, we don't remove this one above, so we need to skip over it
					if (gettimeofday(true) > date("U", strtotime($row["timestamp"])) + $timeout) {
						continue;
					}

					if ($serveraddress == "127.0.0.1") {
						preg_match("/inet (addr:)?(?!127)(\d+(\.\d+){3})/", `ip addr`, $m);
						$serveraddress = $m[2];
					}

					$ipbits = explode(".", $serveraddress);

					writeChar ($outbuffer, $ipbits[0]);
					writeChar ($outbuffer, $ipbits[1]);
					writeChar ($outbuffer, $ipbits[2]);
					writeChar ($outbuffer, $ipbits[3]);
					writeShort($outbuffer, $serverport);

					echo("Sending $address:$port server #$packetindex: $serveraddress:$serverport ({$row["name"]})\n");

//					//Send a packet to the host to ping the client as well, attempting UDP hole punching
//					$packettype = 2; //MasterServerGameTypesRequest... we can use it though
//					$ipbits = explode(".", $address);
//
//					$punchbuffer = "";
//					writeChar ($punchbuffer, $packettype);
//					writeChar ($punchbuffer, 0);
//					writeInt  ($punchbuffer, $key);
//					writeChar ($punchbuffer, $ipbits[0]);
//					writeChar ($punchbuffer, $ipbits[1]);
//					writeChar ($punchbuffer, $ipbits[2]);
//					writeChar ($punchbuffer, $ipbits[3]);
//					writeShort($punchbuffer, $port);
//					socket_sendto($socket, $punchbuffer, strlen($punchbuffer), 0, $serveraddress, $serverport);
//
//					//Send a packet to the host to ping the client as well, attempting UDP hole punching
//					$packettype = 2; //MasterServerGameTypesRequest... we can use it though
//					$ipbits = explode(".", $serveraddress);
//
//					$punchbuffer = "";
//					writeChar ($punchbuffer, $packettype);
//					writeChar ($punchbuffer, 0);
//					writeInt  ($punchbuffer, $key);
//					writeChar ($punchbuffer, $ipbits[0]);
//					writeChar ($punchbuffer, $ipbits[1]);
//					writeChar ($punchbuffer, $ipbits[2]);
//					writeChar ($punchbuffer, $ipbits[3]);
//					writeShort($punchbuffer, $serverport);
//					socket_sendto($socket, $punchbuffer, strlen($punchbuffer), 0, $address, $port);
				}
				//Now send it
				socket_sendto($socket, $outbuffer, strlen($outbuffer), 0, $address, $port);
			} else {
				$packettotal = 1;
				$outbuffer = "";
				writeChar ($outbuffer, $packettype);
				writeChar ($outbuffer, 0);
				writeInt  ($outbuffer, $query_key);
				writeChar ($outbuffer, 0);
				writeChar ($outbuffer, $packettotal);
				writeShort($outbuffer, 0);
				writeChar ($outbuffer, 0);
				writeChar ($outbuffer, 0);
				writeChar ($outbuffer, 0);
				writeChar ($outbuffer, 0);
				writeShort($outbuffer, 0);

				echo("No servers to send to $address:$port");

				//Now send it
				socket_sendto($socket, $outbuffer, strlen($outbuffer), 0, $address, $port);
			}

			break;
		case 12: //GameMasterInfoResponse (0x0C)
			//GameMasterInfoResponse has a shitton of data!
			readChar ($buffer, $cmd_type);
			readChar ($buffer, $flags);
			readInt  ($buffer, $key);
			readStr  ($buffer, $game_type);
			readStr  ($buffer, $mission_type);
			readChar ($buffer, $max_players);
			readInt  ($buffer, $region_mask);
			readInt  ($buffer, $version);
			readChar ($buffer, $filter_flag);
			readChar ($buffer, $bot_count);
			readInt  ($buffer, $cpu_speed);
			readChar ($buffer, $player_count);
			readGuids($buffer, $guid_list, $player_count);

			//I packed these vars full of information
			/*
				$Server::GameType =
					"Platinum" TAB
					($MP::TeamMode ? "Teams" : "FFA") TAB
					serverGetHandicaps() TAB
					($Server::Dedicated ? "No Host" : $LB::Username) TAB
					(!!$MPPref::CalculateScores) TAB
					($MPPref::Server::Password !$= "");


				$Server::GameType =
				    "Platinum" TAB
				    ($MP::TeamMode ? "Teams" : "FFA") TAB
				    0 TAB
				    ($Server::Dedicated ? "No Host" : $LB::Username) TAB
				    ($MPPref::Server::Password !$= "");


				$Server::MissionType =
					$MPPref::Server::Name TAB
					MissionInfo.name;
			*/
			$split      = explode("\t", $game_type);
			$mod        = $split[0];
			$mode       = $split[1];
			$handicap   = $split[2];
			$host       = $split[3];
			$submitting = true; // $split[4];
			$password   = $split[4];
			$min_rating = 0; // $split[5];

			$split      = explode("\t", $mission_type);
			$_port      = $split[0];
			$name       = $split[1];
			$level      = $split[2];

			echo("Got server info:\n");
			echo("   Query Flags: $flags\n");
			echo("   Key: $key\n");
			echo("   Game Type: $game_type\n");
			echo("   Mission Type: $mission_type\n");
			echo("   Max Players: $max_players\n");
			echo("   Region Mask: $region_mask\n");
			echo("   Version: $version\n");
			echo("   Filter Flag: $filter_flag\n");
			echo("   Bot Count: $bot_count\n");
			echo("   CPU Speed: $cpu_speed\n");
			echo("   Player Count: $player_count\n");
			echo("\n");
			echo("   Interpreted:\n");
			echo("      Mod: $mod\n");
			echo("      Mode: $mode\n");
			echo("      Handicap: $handicap\n");
			echo("      Host: $host\n");
			echo("      Submitting: $submitting\n");
			echo("      Password: $password\n");
			echo("      Min Rating: $min_rating\n");
			echo("      Port: $_port\n");
			echo("      Name: $name\n");
			echo("      Level: $level\n");
			echo("\n");

			//If their port doesn't seem to be right, there's not much we can do.
			if ($port != $_port && (int)$_port) {
				echo("Port discrepancy! Connected port ($port) is not the same as the info port ($_port)!\n");

				//If their old port is wrong, we need to remove the old server from the DB
				$query = safe_prepare("SELECT `key` FROM `mpservers` WHERE `address` = :address AND `port` = :port LIMIT 1");
				$query->bind(":address", $address);
				$query->bind(":port", $port);
				$result = $query->execute();

				//Make sure we delete the right one
				if ($result->rowCount()) {
					$key = $result->fetchIdx(0);
					$query = safe_prepare("DELETE FROM `mpservers` WHERE `key` = :key LIMIT 1");
					$query->bind(":key", $key);
					$query->execute();
				}

				//Can't really tell them, so just use the new port
				$port = $_port;
			}

			$query = safe_prepare("SELECT `key` FROM `mpservers` WHERE `address` = :address AND `port` = :port LIMIT 1");
			$query->bind(":address", $address);
			$query->bind(":port", $port);
			$result = $query->execute();

			if ($result->rowCount()) {
				$key = $result->fetchIdx(0);
				//Found it
				echo("Server info from $address:$port\n");

				//Now insert their info
				$query = safe_prepare(
					"UPDATE `mpservers` SET
						`name` = :name,
						`level` = :level,
						`mod` = :mod,
						`mode` = :mode,
						`handicap` = :handicap,
						`host` = :host,
						`submitting` = :submitting,
						`password` = :password,
						`minRating` = :minRating,
						`maxPlayers` = :maxPlayers,
						`regionMask` = :regionMask,
						`version` = :version,
						`filterFlag` = :filterFlag,
						`botCount` = :botCount,
						`CPUSpeed` = :CPUSpeed,
						`players` = :players,
						`receivedinfo` = 1,
						`timestamp` = CURRENT_TIMESTAMP
					WHERE `key` = :key");
				$query->bind(":name",       $name);
				$query->bind(":level",      $level);
				$query->bind(":mod",        $mod);
				$query->bind(":mode",       $mode);
				$query->bind(":handicap",   $handicap);
				$query->bind(":host",       $host);
				$query->bind(":submitting", $submitting);
				$query->bind(":password",   $password);
				$query->bind(":minRating",  $min_rating);
				$query->bind(":maxPlayers", $max_players);
				$query->bind(":regionMask", $region_mask);
				$query->bind(":version",    $version);
				$query->bind(":filterFlag", $filter_flag);
				$query->bind(":botCount",   $bot_count);
				$query->bind(":CPUSpeed",   $cpu_speed);
				$query->bind(":players",    $player_count);
				$query->bind(":key",        $key);
				$query->execute();
			} else {
				//Now insert their info
				$query = safe_prepare(
					"INSERT INTO `mpservers` SET
						`address` = :address,
						`port` = :port,
						`name` = :name,
						`level` = :level,
						`mod` = :mod,
						`mode` = :mode,
						`handicap` = :handicap,
						`host` = :host,
						`submitting` = :submitting,
						`password` = :password,
						`minRating` = :minRating,
						`maxPlayers` = :maxPlayers,
						`regionMask` = :regionMask,
						`version` = :version,
						`filterFlag` = :filterFlag,
						`botCount` = :botCount,
						`CPUSpeed` = :CPUSpeed,
						`players` = :players,
						`key` = :key,
						`receivedinfo` = 1,
						`timestamp` = CURRENT_TIMESTAMP");
				$query->bind(":address",    $address);
				$query->bind(":port",       $port);
				$query->bind(":name",       $name);
				$query->bind(":level",      $level);
				$query->bind(":mod",        $mod);
				$query->bind(":mode",       $mode);
				$query->bind(":handicap",   $handicap);
				$query->bind(":host",       $host);
				$query->bind(":submitting", $submitting);
				$query->bind(":password",   $password);
				$query->bind(":minRating",  $min_rating);
				$query->bind(":maxPlayers", $max_players);
				$query->bind(":regionMask", $region_mask);
				$query->bind(":version",    $version);
				$query->bind(":filterFlag", $filter_flag);
				$query->bind(":botCount",   $bot_count);
				$query->bind(":CPUSpeed",   $cpu_speed);
				$query->bind(":players",    $player_count);
				$query->bind(":key",        generateKey(64));
				$query->execute();

				echo("Inserted $address:$port server into server list.\n");
			}

			break;
		case 22: //GameHeartbeat
			//Heartbeat is just going to be an 0x16 (22)

			//Add their server to the list if we haven't yet!
			$query = safe_prepare("SELECT `key` FROM `mpservers` WHERE `address` = :address AND `port` = :port LIMIT 1");
			$query->bind(":address", $address);
			$query->bind(":port", $port);
			$result = $query->execute();

			if ($result->rowCount()) {
				$key = $result->fetchIdx(0);
				echo("Heartbeat from existing server $address:$port\n");

				//Update timestamp
				$query = safe_prepare("UPDATE `mpservers` SET `timestamp` = CURRENT_TIMESTAMP WHERE `key` = :key LIMIT 1");
				$query->bind(":key", $key);
				$query->execute();

				//Ask for their info
				socket_sendto($socket, chr(10), 1, 0, $address, $port); //GameMasterInfoRequest
				echo("Sending an info request to $address:$port\n");
			} else {
				//If we haven't found their server, then they are a new server!
				$query = safe_prepare(
					"INSERT INTO `mpservers` SET
						`address` = :address,
						`port` = :port,
						`key` = :key,
						`receivedinfo` = 0");
				$query->bind(":address", $address);
				$query->bind(":port",    $port);
				$query->bind(":key",     generateKey(64));
				$query->execute();

				echo("Inserted $address:$port server into server list.\n");

				//Ask for their info
				socket_sendto($socket, chr(10), 1, 0, $address, $port); //GameMasterInfoRequest
				echo("Sending an info request to $address:$port\n");
			}
			break;
		case  2: echo("Cannot handle MasterServerGameTypesRequest (2) requests!\n"); break;
		case  4: echo("Cannot handle MasterServerGameTypesResponse (4) requests!\n"); break;
		case  8: echo("Cannot handle MasterServerListResponse (8) requests!\n"); break;
		case 10: echo("Cannot handle GameMasterInfoRequest (10) requests!\n"); break;
		case 14: echo("Cannot handle GamePingRequest (14) requests!\n"); break;
		case 16: echo("Cannot handle GamePingResponse (16) requests!\n"); break;
		case 18: echo("Cannot handle GameInfoRequest (18) requests!\n"); break;
		case 20: echo("Cannot handle GameInfoResponse (20) requests!\n"); break;
		case 26: echo("Cannot handle ConnectChallengeRequest (26) requests!\n"); break;
		case 28: echo("Cannot handle ConnectChallengeReject (28) requests!\n"); break;
		case 30: echo("Cannot handle ConnectChallengeResponse (30) requests!\n"); break;
		case 32: echo("Cannot handle ConnectRequest (32) requests!\n"); break;
		case 34: echo("Cannot handle ConnectReject (34) requests!\n"); break;
		case 36: echo("Cannot handle ConnectAccept (36) requests!\n"); break;
		case 38: echo("Cannot handle Disconnect (38) requests!\n"); break;
		case 28785: echo("Cannot handle PQ (0x7071) requests!\n"); break;
		default:
			echo("Unknown command ($cmd) sent!");
			break;
		}
	} catch (Exception $e) {
		if ($lb_connection == null || get_class($e) == "SpDatabaseException") {
			if ($lb_connection != null)
				$lb_connection = null;
			echo("Database is not working, restarting connection\n");

			echo($e->getMessage() . '\n');
			echo($e->getTraceAsString() . '\n');

			$dsn = "mysql:dbname=" . $mysql_data . ";host=" . $mysql_host;
			// Connect + select
			try {
				global $lb_connection;
			   $lb_connection = new SpDatabaseConnection($dsn, $mysql_user, $mysql_pass);
			} catch (SpDatabaseLoginException $e) {
				echo("Could not open database connection.");
			}
			if ($lb_connection == null) {
				echo("Could not connect to database.");
			}
		}
	}
}

function readChar(&$buffer, &$store) {
	$store = ord($buffer[0]);
	$buffer = substr($buffer, 1);
}

function readShort(&$buffer, &$store) {
	$data = unpack("S1result", $buffer);
	$store = $data["result"];
	$buffer = substr($buffer, 2);
}

function readInt(&$buffer, &$store) {
	$data = unpack("I1result", $buffer);
	$store = $data["result"];
	$buffer = substr($buffer, 4);
}

function readStr(&$buffer, &$store) {
	$len = ord($buffer[0]);
	$store = substr($buffer, 1, $len);
	$buffer = substr($buffer, 1 + $len);
}

function readGuids(&$buffer, &$store, $player_count) {
	$store = array();
	while ($player_count --) {
		readInt($buffer, $guid);
		$store[] = $guid;
	}
}

function writeChar(&$buffer, $data) {
	$buffer .= pack("C1", $data);
}

function writeShort(&$buffer, $data) {
	$buffer .= pack("S1", $data);
}

function writeInt(&$buffer, $data) {
	$buffer .= pack("I1", $data);
}

function writeStr(&$buffer, $data) {
	$buffer .= pack("C1", strlen($data));
	$buffer .= $data;
}

function generateKey($length = 64) {
	$chars = "abcdefghijklmnopqrstuvwqyz0123456789";

	//Get random seed from microtime
	list($usec, $sec) = explode(" ", microtime());
	//Do some cool maths
	$seed = (float) $sec + ((float) $usec * 100000);
	//And set the seed
	mt_srand($seed);

	//Generate
	$str = "";
	$charc = strlen($chars);

	for ($i = 0; $length > $i; $i ++) {
		$str .= $chars{mt_rand(0, $charc - 1)};
	}

	return $str;
}


//Makes sure the SQL server is connected and happy
function checkServer() {
    //Try/catch for an SQL error
    try {
        //Just a basic query, if it doesn't work, then the server has disconnected
        global $lb_connection;
        if ($lb_connection)
            $lb_connection->prepare("SELECT * FROM `loggedin`")->execute();
    } catch (Exception $e) {
        echo("Error in checkserver!\n");
        //Server has disconnected or something?

        global $lb_connection, $mysql_data, $mysql_host, $mysql_user, $mysql_pass, $lb_connectionOpen;

        $dsn = "mysql:dbname=" . $mysql_data . ";host=" . $mysql_host;
        // Connect + select
        try {
            $lb_connection = new SpDatabaseConnection($dsn, $mysql_user, $mysql_pass);
        } catch (SpDatabaseLoginException $e) {
            die("Could not open database connection.\n");
        }
        if ($lb_connection == null) {
            die("Could not connect to database.\n");
        }
        $lb_connectionOpen = true;

        echo("Lost connection to database, but reconnected!\n");
    }
}

function safe_prepare($query) {
    //Make sure the SQL server is alive
    checkServer();
    return pdo_prepare($query);
}

function socketError() {
	die("Socket error: [" . socket_last_error() . "] " . socket_strerror(socket_last_error()) . "\n");
}

function on_shutdown() {
	global $socket;

	//Close the listening socket
	socket_close($socket);
}

?>