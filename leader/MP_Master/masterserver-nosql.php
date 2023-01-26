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
$port    = 80;
//Maximum length of a data buffer
$maxlen  = 1024;
//Time before servers are removed from the list
$timeout = 120;
//Time before servers are sent a second info request
$refresh = 90;

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
		printf("%02X ", ord($buffer[$i]));
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
	switch ($cmd) {
	case 6: //MasterServerListRequest
		print_r($serverlist);

		//Figure out the server list and send it back
		readChar ($buffer, $cmd_type);
		readChar ($buffer, $query_flags);
		readInt  ($buffer, $key);
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
		echo("   Key: $key\n");
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

		if (count($serverlist)) {
			//Check for old servers
			foreach ($serverlist as $serverinfo) {
				$serveraddress = $serverinfo["Address"];
				$serverport = $serverinfo["Port"];

				//Check for timeout
				if (gettimeofday(true) > $serverinfo["Timestamp"] + $timeout) {

					//Can the server, it's gone
					$idx = array_search($serverinfo, $serverlist);
					unset($serverlist[$idx]);
					array_splice($serverlist, $idx, 1);

					echo("Removed $serveraddress:$serverport for inactivity\n");

					//And go to the next one
					continue;
				}
			}

			//And spit out the list
			$packettotal = count($serverlist);
			$packetindex = 0;
			foreach ($serverlist as $serverinfo) {
				$serveraddress = $serverinfo["Address"];
				$serverport = $serverinfo["Port"];

				//Check for refresh
				if (gettimeofday(true) > $serverinfo["Timestamp"] + $refresh) {

					//Make sure it's still alive
					socket_sendto($socket, chr(10), 1, 0, $serveraddress, $serverport); //GameMasterInfoRequest
					echo("Sending an info request to $serveraddress:$serverport\n");
				}

				if ($serveraddress == "127.0.0.1") {
					preg_match("/inet (addr:)?(?!127)(\d+(\.\d+){3})/", `ip addr`, $m);
					$serveraddress = $m[2];
				}

				$ipbits = explode(".", $serveraddress);

				//Create the response
				$outbuffer = "";
				writeChar ($outbuffer, $packettype);
				writeChar ($outbuffer, 0);
				writeInt  ($outbuffer, $key);
				writeChar ($outbuffer, $packetindex);
				writeChar ($outbuffer, $packettotal);
				writeShort($outbuffer, $packettotal);
				writeChar ($outbuffer, $ipbits[0]);
				writeChar ($outbuffer, $ipbits[1]);
				writeChar ($outbuffer, $ipbits[2]);
				writeChar ($outbuffer, $ipbits[3]);
				writeShort($outbuffer, $serverport);

				$packetindex ++;

				//Now send it
				socket_sendto($socket, $outbuffer, strlen($outbuffer), 0, $address, $port);
			}
		} else {
			$packettotal = 1;
			$outbuffer = "";
			writeChar ($outbuffer, $packettype);
			writeChar ($outbuffer, 0);
			writeInt  ($outbuffer, $key);
			writeChar ($outbuffer, 0);
			writeChar ($outbuffer, $packettotal);
			writeShort($outbuffer, 0);
			writeChar ($outbuffer, 0);
			writeChar ($outbuffer, 0);
			writeChar ($outbuffer, 0);
			writeChar ($outbuffer, 0);
			writeShort($outbuffer, 0);

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

		$info = array(
			"GameType" => $game_type,
			"MissionType" => $mission_type,
			"MaxPlayers" => $max_players,
			"RegionMask" => $region_mask,
			"Version" => $version,
			"FilterFlag" => $filter_flag,
			"BotCount" => $bot_count,
			"CPUSpeed" => $cpu_speed,
			"PlayerCount" => $player_count,
			"GuidList" => $guid_list
		);

		$found = false;
		//Find their server
		for ($i = 0; $i < count($serverlist); $i ++) {
			if ($serverlist[$i]["Address"] == $address &&
				 $serverlist[$i]["Port"] == $port) {
				//Found it
				echo("Server info from $address:$port\n");

				//Now insert their info
				$serverlist[$i]["Info"] = $info;

				//Update timestamp
				$serverlist[$i]["Timestamp"] = gettimeofday(true);

				$found = true;
				break;
			}
		}

		//If we didn't find them, add them to the list
		if (!$found) {
			$serverlist[] = array("Address" => $address, "Port" => $port, "Timestamp" => gettimeofday(true), "Info" => $info);
			echo("Inserted $address:$port server into server list. List is now " . count($serverlist) . " servers long!\n");
		}

		break;
	case 22: //GameHeartbeat
		//Heartbeat is just going to be an 0x16 (22)

		//Add their server to the list if we haven't yet!
		$found = false;
		//Find their server
		for ($i = 0; $i < count($serverlist); $i ++) {
			if ($serverlist[$i]["Address"] == $address &&
				 $serverlist[$i]["Port"] == $port) {
				//Found it
				echo("Heartbeat from existing server $address:$port\n");
				$found = true;

				//Update timestamp
				$serverlist[$i]["Timestamp"] = gettimeofday(true);

				//Ask for their info
				socket_sendto($socket, chr(10), 1, 0, $address, $port); //GameMasterInfoRequest
				echo("Sending an info request to $address:$port\n");
				break;
			}
		}

		//If we haven't found their server, then they are a new server!
		if (!$found) {
			$serverlist[] = array("Address" => $address, "Port" => $port, "Timestamp" => gettimeofday(true), "Info" => array());
			echo("Inserted $address:$port server into server list. List is now " . count($serverlist) . " servers long!\n");

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

function socketError() {
	die("Socket error: [" . socket_last_error() . "] " . socket_strerror(socket_last_error()) . "\n");
}

function on_shutdown() {
	global $socket;

	//Close the listening socket
	socket_close($socket);
}

?>